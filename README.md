# Modular Laravel

The Modular Laravel allows for Laravel code to be organized in smaller sets within an application.

## Install

Via Composer

	{
    	"require": {
        	"suitetea/modularlaravel": "dev-master"
    	}
	}
	"minimum-stability" : "dev"
	
\***note:** Modular Laravel is not yet listed on Packagist. You will need to add the following to your Composer file.

	"repositories": [
        {
            "type": "git",
            "url": "https://github.com/SuiteTea/ModularLaravel.git"
        }
    ]
    
Add our modules path to the `classmap` in our Composer file:

	"autoload": {
		"classmap": [
			"app/commands",
			"app/controllers",
			"app/models",
			"app/database/migrations",
			"app/database/seeds",
			"app/tests/TestCase.php",
			"app/modules"
		]
	}

Next run an update from Composer

	composer update
	
After installation is complete, add the service provider to the `app/config/app.php` providers array.

	SuiteTea\ModularLaravel\ModularLaravelServiceProvider

## Modules

### Structure

* /modules
  * /SomeModule
    * /controllers
    * /views
    * /ServiceProvider.php
    * /routes.php
    * /config.php
    
### Namespace

Module class namespaces will be prefixed with the default modules namespace, `App\Modules`, followed by the module name. Ex: `App\Modules\SomeModule`.

### Module Config

	<?php

	return array(

		// Spaces are allowed
	    'name' => 'Some Module',

	    'enabled' => true,

		// Optional
	    'provider' => 'ServiceProvider',
		
		// Optional
	    'autoload' => array(
	        'routes.php'
	    )
	
	);

## Package Config

Package config:

	<?php

	return array(

	    /**
	     * Path to modules
	     */
	    'path' => app_path() . '/modules',

	    /**
	     * Namespace of modules
	     */
	    'namespace' => 'App\\Modules',

	    /**
	     * Finder mode (ie: 'auto', 'database', 'manual')
	     * May also be an array: array('manual', 'auto').
	     */
	    'mode' => 'auto',

	    /**
	     * Any manually declared modules, when in 'manual mode', will
	     * automatically be enabled reguardless of their configuration settings.
	     */
	    'force_enable_manual' => false,

	    /**
	     * Database table to find and store modules
	     */
	    'table' => 'modules',

	    /**
	     * Modules defined.
	     * Modules automatically load and cannot be disabled.
	     *
	     * Example:
	     * 
	     * 'modules' => ['Module Name Here']
	     *
	     * The example module name directory will be 'modulenamehere'.
	     */
	    'modules' => []

	);

By default, all modules will be automatically loaded and registered with the app (`'mode' => 'auto'`).

To utilize a database to manage optional modules, run the package migration then change the `mode` to `'database'`:

	php artisan migrate --package="suitetea/modularlaravel"

If you wish to override the package settings, publish the package settings and edit that file.

Publish config: `php artisan config:publish suitetea/modularlaravel`. The new config settings are located in `app/config/packages/suitetea/modularlaravel/config.php`.