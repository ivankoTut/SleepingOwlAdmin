<?php

namespace SleepingOwl\Admin\Display\Column;

class TreeControl extends Control
{
    /**
     * @var bool
     */
    protected $orderable = false;

    /**
     * Column view.
     * @var string
     */
    protected $view = 'column.tree_control';


    /**
     * Control constructor.
     *
     * @param string|null $label
     */
    public function __construct($label = null)
    {
        parent::__construct($label);

        $this->setHtmlAttribute('class', 'row-control');
    }

}
