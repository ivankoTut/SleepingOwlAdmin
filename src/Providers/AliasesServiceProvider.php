<?php

namespace SleepingOwl\Admin\Providers;

use Illuminate\Support\ServiceProvider;
use SleepingOwl\Admin\Contracts\Display\DisplayColumnEditableFactoryInterface;
use SleepingOwl\Admin\Contracts\Display\DisplayColumnFactoryInterface;
use SleepingOwl\Admin\Contracts\Display\DisplayColumnFilterFactoryInterface;
use SleepingOwl\Admin\Contracts\Display\DisplayFactoryInterface;
use SleepingOwl\Admin\Contracts\Display\DisplayFilterFactoryInterface;
use SleepingOwl\Admin\Contracts\Form\FormElementFactoryInterface;
use SleepingOwl\Admin\Contracts\Form\FormFactoryInterface;
use SleepingOwl\Admin\Factories\DisplayColumnEditableFactory;
use SleepingOwl\Admin\Factories\DisplayColumnFactory;
use SleepingOwl\Admin\Factories\DisplayColumnFilterFactory;
use SleepingOwl\Admin\Factories\DisplayFactory;
use SleepingOwl\Admin\Factories\DisplayFilterFactory;
use SleepingOwl\Admin\Factories\FormElementFactory;
use SleepingOwl\Admin\Factories\FormFactory;

class AliasesServiceProvider extends ServiceProvider
{
    /**
     * @var array
     */
    protected $elements = [
        'display' => [DisplayFactory::class, DisplayFactoryInterface::class],
        'column_filter' => [DisplayColumnFilterFactory::class, DisplayColumnFilterFactoryInterface::class],
        'display.filter' => [DisplayFilterFactory::class, DisplayFilterFactoryInterface::class],
        'table.column' => [DisplayColumnFactory::class, DisplayColumnFactoryInterface::class],
        'table.column.editable' => [DisplayColumnEditableFactory::class, DisplayColumnEditableFactoryInterface::class],
        'form' => [FormFactory::class, FormFactoryInterface::class],
        'form.element' => [FormElementFactory::class, FormElementFactoryInterface::class],
    ];

    public function register()
    {
        foreach ($this->elements as $element => list($factory, $contract)) {
            $serviceContainer = 'sleeping_owl.'.$element;
            $this->app->instance($serviceContainer, $this->app->make($factory));
            $this->app->alias($serviceContainer, $contract);
        }
    }
}
