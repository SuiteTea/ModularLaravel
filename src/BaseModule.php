<?php namespace SuiteTea\ModularLaravel;

use Illuminate\Foundation\Application;
use Illuminate\View\Environment as View;

class BaseModule extends \Illuminate\Support\ServiceProvider {

    protected $attributes = [];
	protected $view;
    /**
     * IoC
     * @var Illuminate\Foundation\Application
     */
    protected $app;

    public function __construct($name, $path, Application $app, View $view, $enabled = null)
    {
        $this->app = $app;
        $this->view = $view;
        $this->name = $this->makeName($name);
        $this->path = $path;
        $this->namespace = $this->buildNamespace($name);

        if (! is_null($enabled)) {
            $this->enabled = $enabled;
        }

        $this->loadConfig();
    }

    public function getNamespace($path = null)
    {
        $namespace = $this->namespace;

        $namespace .= ! is_null($path) ? '\\' . $path : '';

        return $namespace;
    }

    public function register()
    {
        if ($this->enabled) {
            $package_name = strtolower('modules/' . $this->name);
            $this->package($package_name, $package_name, $this->path);

            $this->registerProvider();
            $this->loadFiles();
            $this->registerViews();
        }
    }

    public function registerProvider()
    {
        if (isset($this->attributes['provider'])) {
            $provider = $this->namespace . '\\' . $this->provider;
            $this->app->register(new $provider($this->app));
        }
    }

    public function loadFiles()
    {
        if (isset($this->attributes['autoload'])) {
        
        	$instance = $this;

            $this->app->booted(function() use ($instance)
			{
	            foreach ($instance->autoload as $file) {
	                $path = $instance->path($file);
	                if ($instance->app['files']->exists($path)) {
	                    require $path;
	                }
	            }
	        });
        }
    }

    public function loadConfig()
    {
        $path = $this->path . '/config.php';
        if ($this->app['files']->exists($path)) {
            $config = $this->app['files']->getRequire($path);

            if (isset($config['name'])) {
                $this->name = $this->makeName($config['name']);
                $this->namespace = $this->buildNamespace($this->name);
                unset($config['name']);
            }

            if (isset($this->enabled)) {
                unset($config['enabled']);
            }

            foreach ($config as $key => $value) {
                $this->$key = $value;
            }
        }
    }
    
    public function registerViews() 
    {
	    $this->view->addNamespace(strtolower($this->name), $this->path.'/views');
    }
    
    public function path($path = null)
    {
        if (! is_null($path)) {
            return $this->path . '/' . ltrim($path, '/');
        }
        return $this->path;
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
        $namespace = $this->app['config']->get('modularlaravel::config.namespace');
        return '\\' . $namespace . '\\' . str_replace(' ', '', ucwords($name));
    }

    private function makeName($name)
    {
        return preg_replace('/\s{1,}/', '', ucwords($name));
    }

}