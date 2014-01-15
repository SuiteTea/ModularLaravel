<?php namespace SuiteTea\ModularLaravel;

class BaseModule {

    protected $attributes = [];

    public function __construct($name, array $attributes)
    {
        $name = preg_replace('/\s{1,}/', '', ucwords($name));

        $this->attributes = $attributes;
        $this->name = $name;
        $this->namespace = $this->buildNamespace($name);
    }

    public function getNamespace($path = null)
    {
        $namespace = $this->namespace;

        $namespace .= ! is_null($path) ? '\\' . $path : '';

        return $namespace;
    }

    public function __get($key)
    {
        return $this->attributes[$key];
    }

    public function __set($key, $value)
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    private function buildNamespace($name)
    {
        $namespace = app('config')->get('modularlaravel::config.namespace');
        return '\\' . $namespace . '\\' . str_replace(' ', '', ucwords($name));
    }

}