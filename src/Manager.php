<?php namespace SuiteTea\ModularLaravel;

use Illuminate\Events\Dispatcher;
use Illuminate\View\Factory as ViewFactory;
use Illuminate\Config\Repository as ConfigManager;
use Illuminate\Support\Collection;
use Composer\Autoload\ClassLoader;
use SuiteTea\ModularLaravel\Exceptions\DuplicateModuleException;
use SuiteTea\ModularLaravel\BaseModule;

class Manager
{
    const EVENT_ACTIVE = 'modules.active';

    const EVENT_ACTIVATION_FAILED = 'modules.activation_failed';

    /**
     * Local reference to global application.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $view;

    /**
     * @var \Composer\Autoload\ClassLoader
     */
    protected $classLoader;

    /**
     * @var \Illuminate\Events\Dispatcher
     */
    protected $dispatcher;

    /**
     * Collection of the registered modules.
     *
     * @var \Illuminate\Support\Collection
     * @alias registry
     */
    protected $registeredModules;

    /**
     * Collection of modules that have been activated.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $activeModules;

    /**
     * Config parameters validation rules
     *
     * @var array
     */
    protected $registryRules = [
        'name' => 'required',
        'directory' => 'required'
    ];

    /**
     * @var \Illuminate\Config\Repository
     */
    protected $configManager;

    /**
     * @param \Illuminate\Support\Collection $collection
     */
    public function __construct(
        Collection $collection,
        ViewFactory $view,
        ClassLoader $classLoader,
        Dispatcher $dispatcher,
        ConfigManager $configManager
    ) {
        $this->view = $view;
        $this->classLoader = $classLoader;
        $this->dispatcher = $dispatcher;
        $this->configManager = $configManager
        $this->registeredModules = new $collection;
        $this->activeModules = new $collection;
    }

    /**
     * Pre-register
     *
     * Used with autoloaded modules to register automatically.
     * 
     * @param array $config
     * @return void
     */
    public static function preRegister(array $config)
    {
        if (! isset($GLOBALS['suitetea.modules'])) {
            $GLOBALS['suitetea.modules'] = [];
        }

        $GLOBALS['suitetea.modules'][] = $config;
    }

    /**
     * Registers a module with the Manager.
     *
     * @param array $config
     */
    public function register(array $config)
    {
        $this->validate($config, $this->registeredModules);

        $this->registeredModules->put(
            $config['name'],
            new BaseModule($config, $this->view, $this, $this->configManager)
        );
    }

    /**
     * Go
     *
     * Goes through all of the registered modules and attempts
     * to activate them. Performs checks to ensure that modules
     * that declare dependencies that have not been satisfied
     * will not be activated.
     *
     * Registers any directories with the class loader
     *
     * @return void
     */
    public function go()
    {
        $this->handlePreRegistered();

        $toActivate = $this->registeredModules->all();

        // This 'while' loop runs continuously allowing for modules
        // to be checked multiple times which allows for dependencies.
        while (count($toActivate) > 0) {
            foreach ($toActivate as $name => $module) {
                $canActivate = true;
                $dependencies = $module->dependencies();

                // If we've got dependencies, check them all to make sure all have been activated
                // before activating this particular module.
                if (! empty($dependencies)) {
                    foreach ($dependencies as $dependency) {

                        // First off, if this dependency hasn't even been register, we can never
                        // activate this module, so let's skip it and not try again.
                        if (! $this->registeredModules->has($dependency)) {
                            $canActivate = false;
                            $this->dispatcher->fire(self::EVENT_ACTIVATION_FAILED.' '.$name, $module);
                            unset($toActivate[$name]);
                            continue;
                        }

                        // At this point, the dependency has been registered but not activated,
                        // so let's skip this module and come back to it.
                        if (! $this->activeModules->has($dependency)) {
                            $canActivate = false;
                        }
                    }
                }

                // Now let's activate this sucka! Also, we remove it from the list
                // so we don't try to activate it again.
                if ($canActivate) {
                    $this->activeModules->put($name, $module->activate());
                    $this->dispatcher->fire(self::EVENT_ACTIVE." $name", $module);
                    unset($toActivate[$name]);
                }
            }
        }

        $this->classLoader->register();
    }

    public function registerNamespace($namespace, $path)
    {
        $this->classLoader->addPsr4($namespace, $path);
    }

    /**
     * Checks to see that a module has the correct parameters
     * in the config, and has not already been registered.
     *
     * @param array $config
     * @param $collection The collection to validate against
     * @throws \InvalidArgumentException
     * @throws Exceptions\DuplicateModuleException
     */
    protected function validate(array $config, $collection)
    {
        // Check the config against the default field rules
        foreach ($this->registryRules as $name => $rule) {
            if ($rule === 'required' && ! array_key_exists($name, $config)) {
                throw new \InvalidArgumentException(sprintf('The %s parameter is required', $name));
            }
        }

        // See if this module is already registered
        if ($collection->has($config['name'])) {
            throw new DuplicateModuleException(
                sprintf(
                    'The module %s has already been registered',
                    ucwords($config['name'])
                )
            );
        }
    }

    /**
     * Handle Pre-registered
     *
     * When the class is instantiated, register any pre-registered
     * modules before trying activation. 
     * 
     * @return void
     */
    protected function handlePreRegistered()
    {
        if (isset($GLOBALS['suitetea.modules'])) {
            foreach ($GLOBALS['suitetea.modules'] as $module) {
                $this->register($module);
            }
        }
    }

    public function get($name)
    {
        return $this->$name;
    }

    public function __get($name)
    {
        switch ($name) {
            case 'registry' :
                return $this->registeredModules;
                break;
            case 'active' :
                return $this->activeModules;
                break;
        }
        return $this->$name;
    }
}