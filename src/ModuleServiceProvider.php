<?php namespace SuiteTea\ModularLaravel;

abstract class ModuleServiceProvider extends \Illuminate\Support\ServiceProvider {

    protected $app;
    protected $module;
    
    public function __construct($app, $module)
    {
        $this->app = $app;
        $this->module = $module;

        $this->load();
    }

    public function load() {}

}