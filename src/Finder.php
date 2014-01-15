<?php namespace SuiteTea\ModularLaravel;

use Illuminate\Foundation\Application;

class Finder {

    protected $config;

    protected $modules = [];

    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->config = $this->app['config']->get('modularlaravel::config');
    }

    public function get($module)
    {
        return $this->modules[strtolower($module)];
    }

    public function all()
    {
        return $this->modules;
    }

    public function start()
    {
        $this->findFromCore();

        switch ($this->config['mode']) {
            case 'autoload' :
                $this->findAll();
                break;
            case 'database' :
                $this->findFromDatabase();
        }
    }

    public function boot()
    {
        foreach ($this->modules as $module)
        {
            $this->registerModule($module->name);
        }
    }

    public function registerModule($name)
    {
        $module = $this->modules[strtolower($name)];
        $provider = $module->getNamespace($module->name . 'ServiceProvider');
        if ($module->enabled) {
            $this->app->register($instance = new $provider($this->app, $module));
        }
    }

    private function findFromCore()
    {
        // Find core modules
        foreach ($this->config['core'] as $core_module) {
            $this->modules[strtolower($core_module['name'])] = new \SuiteTea\ModularLaravel\BaseModule(
                $core_module['name'],
                array(
                    'core' => true,
                    'enabled' => true
                )
            );
        }
    }

    private function findFromDatabase()
    {
        // Find optional modules
        $results = $this->app['db']->table($this->config['table'])->get();
        foreach ($results as $result) {
            $this->modules[strtolower($result->name)] = new \SuiteTea\ModularLaravel\BaseModule(
                $result->name,
                array(
                    'core' => false,
                    'enabled' => $result->status == 'enabled' ? true : false
                )
            );
        }
    }

    private function findAll()
    {
        if (is_dir($this->config['path'])) {
            foreach ($this->app['files']->directories($this->config['path']) as $directory) {
                $module_name = strtolower(pathinfo($directory, PATHINFO_BASENAME));
                if (! isset($this->modules[$module_name])) {
                    $this->modules[$module_name] = new \SuiteTea\ModularLaravel\BaseModule(
                        $module_name,
                        array(
                            'core' => false,
                            'enabled' => true
                        )
                    );
                }
            }
        }
    }

}