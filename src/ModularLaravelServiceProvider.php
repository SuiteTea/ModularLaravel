<?php namespace SuiteTea\ModularLaravel;

use Illuminate\Support\ServiceProvider;
use SuiteTea\ModularLaravel\Finder as ModuleFinder;
use Illuminate\View\Environment as View;

class ModularLaravelServiceProvider extends ServiceProvider {

    public function boot()
    {
        $this->package('suitetea/modularlaravel', 'modularlaravel', __DIR__);
        $this->app['suitetea.module']->go();
    }

    public function register()
    {
        $this->app['suitetea.module'] = $this->app->share(function($app)
        {
            return new ModuleFinder($app, $app['view']);
        });
    }

}