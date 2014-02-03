<?php namespace SuiteTea\ModularLaravel;

use Illuminate\Foundation\Application;
use ClassLoader;
use SuiteTea\ModularLaravel\BaseModule;
use Illuminate\View\Environment as View;

class Finder {

    protected $config;

    protected $modules = [];

    protected $app;
    
    protected $view;

    public function __construct(Application $app, View $view)
    {
        $this->app = $app;
        $this->view = $view;
        $this->config = $this->app['config']->get('modularlaravel::config');
        
        ClassLoader::addDirectories($this->config['path']);
    }

    public function get($module)
    {
        return $this->modules[$this->formatName($module)];
    }

    public function all()
    {
        return $this->modules;
    }

    public function go()
    {
        if (is_array($this->config['mode'])) {
            foreach ($this->config['mode'] as $mode) {
                $method = $mode . 'Loader';
                $this->$method();
            }
        } else {
            $method = $this->config['mode'] . 'Loader';
            $this->$method();
        }

        $this->registerModules();
    }

    private function manualLoader()
    {
        // Find core modules
        foreach ($this->config['modules'] as $module) {
            $enabled = $this->config['force_enable_manual'] ? true : null;
            $this->modules[$this->formatName($module)] = new BaseModule(
                $module,
                $this->modulePath($module),
                $this->app,
                $enabled
            );
        }
    }

    private function databaseLoader()
    {
        // Find optional modules
        $results = $this->app['db']->table($this->config['table'])->get();
        foreach ($results as $result) {
            $this->modules[$this->formatName($result->name)] = new BaseModule(
                $result->name,
                $this->modulePath($result->name),
                $this->app
            );
        }
    }

    private function autoLoader()
    {
        if (is_dir($this->config['path'])) {
            foreach ($this->app['files']->directories($this->config['path']) as $directory) {
                $module_name = $this->formatName(pathinfo($directory, PATHINFO_BASENAME));

                // Load if not already loaded
                if (! isset($this->modules[$module_name])) {
                    $this->modules[$module_name] = new BaseModule(
                        $module_name,
                        $this->modulePath($module_name),
                        $this->app,
                        $this->view
                    );
                }
            }
        }
    }

    private function registerModules()
    {
        foreach ($this->modules as $module) {
            $module->register();
        }
    }

    private function modulePath($module)
    {
        return $this->config['path'] . '/' . $this->formatName($module);
    }

    private function formatName($name)
    {
        return str_replace(' ', '', strtolower($name));
    }

}