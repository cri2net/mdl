<?php

trait Singleton
{
    public static function getInstance()
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new static();
        }
        return $instance;
    }

    protected function __construct()
    {
    }

    /**
     * Закрываем доступ к методу вне класса.
     * Паттерн Singleton не допускает вызов этой функции вне класса
     */
    protected function __clone()
    {
    }
}
