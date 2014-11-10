<?php namespace SuiteTea\ModularLaravel;

use Illuminate\Support\ClassLoader;
use Illuminate\Support\Arr;
use Illuminate\View\Factory as ViewFactory;
use Illuminate\Config\Repository as ConfigManager;
use SuiteTea\ModularLaravel\Manager;

class BaseModule
{
    /**
     * Local reference of the view factory.
     *
     * @var \Illuminate\View\Factory
     */
    protected $view;

    /**
     * @var array
     */
    protected $config;

    /**
     * Local reference of the global ModularLaravel instance.
     *
     * @var \SuiteTea\ModularLaravel\Manager;
     */
    protected $moduleManager;

    /**
     * @var \Illuminate\Config\Repository
     */
    protected $configManager;

    /**
     * @param array $config
     * @param \Illuminate\View\Factory $view
     * @param \SuiteTea\ModularLaravel\Manager $moduleManager
     * @param \Illuminate\Config\Repository $configManager
     */
    public function __construct(
        array $config,
        ViewFactory $view,
        Manager $moduleManager,
        ConfigManager $configManager
    ) {
        $this->view = $view;
        $this->config = $config;
        $this->moduleManager = $moduleManager;
        $this->configManager = $configManager;
    }

    /**
     * Activate
     *
     * Handles all methods of activation:
     * - registers namespace
     * - autoloads files and classmaps
     * - registers a namespace for views
     *
     * @return void
     */
    public function activate()
    {
        $this->registerNamespace();
        $this->autoload();
        $this->registerViews();
        $this->registerConfigNamespace();
    }

    /**
     * Get a list of module's dependencies
     *
     * @return array
     */
    public function dependencies()
    {
        return $this->config('requires', []);
    }

    /**
     * Autoload
     *
     * Autoloads files and adds specified directories into the
     * global class autoloader.
     *
     * @return void
     */
    protected function autoload()
    {
        $directory = $this->config('directory');

        // Load files
        if ($files = $this->config('autoload.files', false)) {
            foreach ($files as $file) {
                include_once $directory.'/'.$file;
            }
        }

        // Register directories with the ClassLoader
        if ($classmap = $this->config('autoload.classmap', false)) {
            ClassLoader::addDirectories(array_map(function($dir) use ($directory)
            {
                return $directory.'/'.$dir;
            }, $classmap));
        }
    }

    /**
     * Register Views
     *
     * Adds a namespace to the 'View' instance for use with the double colon.
     *
     * @return void
     */
    protected function registerViews()
    {
        $this->view->addNamespace(strtolower($this->name), $this->directory.'/views');
    }

    /**
     * Register Namespace
     *
     * Registers the given namespace with the ModularLaravel instance
     * of the Composer PSR-4 autoloader.
     *
     * @return void
     */
    protected function registerNamespace()
    {
        if (isset($this->config['namespace'])) {
            $this->moduleManager->registerNamespace(
                rtrim($this->config('namespace'), '\\').'\\',
                $this->config('directory')
            );
        }
    }

    /**
     * Register Config Namespace
     *
     * Adds a namespace to the 'Config' instance for use with the double colon.
     *
     * @return void
     */
    protected function registerConfigNamespace()
    {
        $this->configManager->package(strtolower($this->name), $this->directory, strtolower($this->name));
    }

    /**
     * @param null $item the key of the config item to return
     * @param null $default the default return value if none is found
     * @return array
     */
    protected function config($item = null, $default = null)
    {
        if (is_null($item)) {
            return $this->config;
        } else {
            return Arr::get($this->config, $item, $default);
        }
    }

    public function __get($key)
    {
        return $this->config[$key];
    }
}