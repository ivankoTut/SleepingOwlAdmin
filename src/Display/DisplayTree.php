<?php

namespace SleepingOwl\Admin\Display;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Router;
use Illuminate\Support\Str;
use Request;
use SleepingOwl\Admin\Contracts\ModelConfigurationInterface;
use SleepingOwl\Admin\Contracts\TreeRepositoryInterface;
use SleepingOwl\Admin\Contracts\WithRoutesInterface;
use SleepingOwl\Admin\Display\Extension\Columns;
use SleepingOwl\Admin\Display\Extension\Tree;
use SleepingOwl\Admin\Repository\TreeRepository;

/**
 * @method TreeRepositoryInterface getRepository()
 * @property TreeRepositoryInterface $repository
 */
class DisplayTree extends Display implements WithRoutesInterface
{
    /**
     * @param Router $router
     */
    public static function registerRoutes(Router $router)
    {

        if (! $router->has($routeName = 'admin.display.tree.reorder')) {
            $router->get('{adminModel}/tree', ['as' => $routeName, function (ModelConfigurationInterface $model) {
                $display = $model->fireDisplay();

                if ($display instanceof DisplayTabbed) {
                    foreach ($display->getTabs() as $tab) {
                        $content = $tab->getContent();
                        if ($content instanceof self) {
                            $display = $content;
                            break;
                        }
                    }
                }

                return new JsonResponse([
                    'data' => [
                        'tree' => $display->getRepository()->getTree($display->getCollection()),
                    ]
                ]);
            }]);
        }

        if (! $router->has($routeName = 'admin.display.tree.reorder')) {
            $router->post('{adminModel}/reorder', ['as' => $routeName, function (ModelConfigurationInterface $model) {
                $display = $model->fireDisplay();

                if ($display instanceof DisplayTabbed) {
                    $display->getTabs()->each(function ($tab) {
                        $content = $tab->getContent();
                        if ($content instanceof self) {
                            $content->getRepository()->reorder(
                                Request::input('data')
                            );
                        }
                    });
                } else {
                    $display->getRepository()->reorder(
                        Request::input('data')
                    );
                }
            }]);
        }
    }

    /**
     * @var string
     */
    protected $view = 'display.tree';

    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * @var bool
     */
    protected $reorderable = true;

    /**
     * @var string|callable
     */
    protected $value = 'title';

    /**
     * @var string
     */
    protected $parentField = 'parent_id';

    /**
     * @var string
     */
    protected $orderField = 'order';

    /**
     * @var string|null
     */
    protected $rootParentId = null;

    /**
     * @var string
     */
    protected $repositoryClass = TreeRepository::class;

    /**
     * @var Column\TreeControl
     */
    protected $controlColumn;

    /**
     * @var Collection
     */
    protected $collection;

    public function __construct()
    {
        parent::__construct();

        $this->extend('columns', $columns = new Columns());

        $columns->setView('display.extensions.tree_columns');
    }

    public function initialize()
    {
        parent::initialize();

        $this->getRepository()
             ->setParentField($this->getParentField())
             ->setOrderField($this->getOrderField())
             ->setRootParentId($this->getRootParentId());

        $this->getColumns()->setControlColumn(
            app('sleeping_owl.table.column')->treeControl()
        );
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string|callable $value
     * @param string|null $title
     *
     * @return $this
     */
    public function setValue($value, $title = null)
    {
        $this->value = $value;

        if (is_null($title)) {
            $title = Str::title($value);
        }

        $this->setColumns([
            app('sleeping_owl.table.column')->link($value, $title)
        ]);

        return $this;
    }

    /**
     * @return string
     */
    public function getParentField()
    {
        return $this->parentField;
    }

    /**
     * @param string $parentField
     *
     * @return $this
     */
    public function setParentField($parentField)
    {
        $this->parentField = $parentField;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrderField()
    {
        return $this->orderField;
    }

    /**
     * @param string $orderField
     *
     * @return $this
     */
    public function setOrderField($orderField)
    {
        $this->orderField = $orderField;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getRootParentId()
    {
        return $this->rootParentId;
    }

    /**
     * @param null|string $rootParentId
     *
     * @return $this
     */
    public function setRootParentId($rootParentId)
    {
        $this->rootParentId = $rootParentId;

        return $this;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     *
     * @return $this
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function setParameter($key, $value)
    {
        $this->parameters[$key] = $value;

        return $this;
    }

    /**
     * @return bool
     */
    public function isReorderable()
    {
        return $this->reorderable;
    }

    /**
     * @param bool $reorderable
     *
     * @return $this
     */
    public function setReorderable($reorderable)
    {
        $this->reorderable = (bool) $reorderable;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $model = $this->getModelConfiguration();

        return parent::toArray() + [
            'items' => $this->getRepository()->getTree($this->getCollection()),
            'reorderable' => $this->isReorderable(),
            'creatable' => $model->isCreatable(),
            'createUrl' => $model->getCreateUrl($this->getParameters() + Request::all()),
            'columns' => $this->getColumns()->toArray()['columns']
        ];
    }

    /**
     * @return ModelConfigurationInterface
     */
    protected function getModelConfiguration()
    {
        return app('sleeping_owl')->getModel($this->modelClass);
    }

    /**
     * @return Collection
     * @throws \Exception
     */
    public function getCollection()
    {
        if (! $this->isInitialized()) {
            throw new \Exception('Display is not initialized');
        }

        if (! is_null($this->collection)) {
            return $this->collection;
        }

        $query = $this->getRepository()->getQuery();

        $this->modifyQuery($query);

        if (method_exists($query, 'defaultOrder')) {
            return $query->defaultOrder()->get();
        }

        return $query->get();
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder|Builder $query
     */
    protected function modifyQuery(\Illuminate\Database\Eloquent\Builder $query)
    {
        $this->extensions->modifyQuery($query);
    }

    /**
     * @return \Illuminate\Foundation\Application|mixed
     * @throws \Exception
     */
    protected function makeRepository()
    {
        $repository = parent::makeRepository();

        if (! ($repository instanceof TreeRepositoryInterface)) {
            throw new \Exception('Repository class must be instanced of [TreeRepositoryInterface]');
        }

        return $repository;
    }
}
