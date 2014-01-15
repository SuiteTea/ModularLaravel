<?php namespace SuiteTea\ModularLaravel;

use Illuminate\Support\ServiceProvider;
use SuiteTea\ModularLaravel\Finder as ModuleFinder;

class ModularLaravelServiceProvider extends ServiceProvider {

    public function boot()
    {
        $this->package('suitetea/modularlaravel', 'modularlaravel', __DIR__);
        $this->app['suitetea.module']->start();
        $this->app['suitetea.module']->boot();
    }

    public function register()
    {
        $this->registerModule();
    }

    public function registerModule()
    {
        $this->app['suitetea.module'] = $this->app->share(function($app)
        {
            return new ModuleFinder($app);
        });
    }

}