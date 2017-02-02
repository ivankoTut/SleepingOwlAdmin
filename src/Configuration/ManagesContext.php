<?php

namespace SleepingOwl\Admin\Configuration;

trait ManagesContext
{
    /**
     * Список установленных контекстов для текущего запроса
     *
     * @var array
     */
    protected $context = [];

    /**
     * Определение контекста по Request запросу
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return string
     */
    public function detectContext(\Illuminate\Http\Request $request)
    {
        $urlPrefix = config('sleeping_owl.url_prefix');

        $context = ($request->is($urlPrefix) or $request->is($urlPrefix.'/*'))
            ? static::CTX_BACKEND
            : static::CTX_FRONTEND;

        $this->setContext($context);
    }

    /**
     * Добавление контекста в текущий запрос
     *
     * @param string|string[] ...$context
     *
     * @return void
     */
    public function setContext(...$context)
    {
        foreach ($context as $name) {
            if (! $this->context($name)) {
                $this->context[] = $name;
            }
        }
    }

    /**
     * Если не переданы аргументы - получение списка контекстов для текущего запроса
     * При передачи аргументов, то проверка на наличие контекста
     *
     * @return array|bool
     */
    public function context()
    {
        if (func_num_args() > 0) {
            $context = is_array(func_get_arg(0)) ? func_get_arg(0) : func_get_args();

            foreach ($context as $name) {
                if (in_array($name, $this->context)) {
                    return true;
                }
            }

            return false;
        }

        return $this->context;
    }
}
