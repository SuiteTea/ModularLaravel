<?php namespace SuiteTea\ModularLaravel;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Collection;
use Composer\Autoload\ClassLoader;
use SuiteTea\ModularLaravel\Manager;

class ModularLaravelServiceProvider extends ServiceProvider {

    public function boot()
    {
        $this->app->booted(function()
        {
            $this->app['suitetea.modules']->go();
        });
    }

    public function register()
    {
        // Register The Laravel Auto Loader for 5.0
        // Since it is not registered by default
        \Illuminate\Support\ClassLoader::register();

        $this->app['suitetea.modules'] = $this->app->share(function($app)
        {
            return new Manager(new Collection, $app['view'], new ClassLoader, $app['events']);
        });

        $this->app->booting(function()
        {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias('ModularLaravel', 'SuiteTea\ModularLaravel\Facade');
        });
    }

}