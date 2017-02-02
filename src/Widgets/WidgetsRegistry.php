<?php

namespace SleepingOwl\Admin\Widgets;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\View\View;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\Factory;
use SleepingOwl\Admin\Contracts\Widgets\WidgetInterface;
use SleepingOwl\Admin\Contracts\Widgets\WidgetsRegistryInterface;

class WidgetsRegistry implements WidgetsRegistryInterface
{
    /**
     * @var Collection|WidgetInterface[]
     */
    protected $widgets;

    /**
     * @var Application
     */
    protected $app;

    /**
     * BlocksRegistry constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->widgets = new Collection();
        $this->app = $app;

        $app->booted(function ($app) {
            $this->placeWidgets(
                $app[Factory::class]
            );
        });
    }

    /**
     * @param $widget
     *
     * @return $this
     */
    public function registerWidget($widget)
    {
        $this->widgets->push($widget);

        return $this;
    }

    /**
     * @param Factory $factory
     */
    public function placeWidgets(Factory $factory)
    {
        if ($this->widgets->count() === 0) {
            return;
        }

        $groupedBlocks = $this->widgets
            ->map(function ($class) {
                return $this->makeWidget($class);
            })
            ->filter(function (WidgetInterface $block) {
                return $block->active();
            })
            ->groupBy(function (WidgetInterface $block) {
                return $block->template();
            });

        foreach ($groupedBlocks as $template => $widgets) {
            $factory->composer($template, function (View $view) use ($widgets) {
                $factory = $view->getFactory();

                /** @var Collection|WidgetInterface[] $widgets */
                $widgets = $widgets->sortBy(function (WidgetInterface $block) {
                    return $block->position();
                });

                foreach ($widgets as $widget) {
                    $widget->setInjectableView($view);

                    $factory->inject(
                        $widget->block(),
                        $widget->toHtml()
                    );
                }
            });
        }
    }

    /**
     * @param  mixed  $widget
     * @return mixed
     */
    public function makeWidget($widget)
    {
        return is_string($widget) ? $this->createClassWidget($widget) : $widget;
    }

    /**
     * @param $widget
     *
     * @return \Closure
     */
    public function createClassWidget($widget)
    {
        return $this->app->make($widget);
    }
}
