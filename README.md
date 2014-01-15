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
    * /SomeModuleServiceProvider.php
    * /routes.php
    
### Namespace

Module class namespaces will be prefixed with the default modules namespace, `App\Modules`, followed by the module name. Ex: `App\Modules\SomeModule`.

### Service Provider

Module service providers extend the `\SuiteTea\ModularLaravel\ModuleServiceProvider` class. This class is an extension to the default Laravel. It adds a `load` method that can be used to include any files needed for the module.

## Config

Package config:

	<?php
	
	return array(
		
		/**
		 * Modules directory
		 */
		'path' => app_path() . '/modules',
		
		/**
		 * Modules namespace
		 */
		'namespace' => 'App\\Modules',
		
		/**
		 * Finder mode (ie: 'autoload', 'database')
		 */
		'mode' => 'autoload',
		
		/**
		 * Database table to find and store modules
		 */
		'table' => 'modules',
		
		/**
		 * Core modules
		 * Core modules automatically load and cannot be disabled.
		 *
		 * Example:
		 *
		 * 'core' => [
		 *     [ 'name' => 'Some Module' ]
		 * ]
		 */
		'core' => []
		
	);

By default, all modules will be automatically loaded and registered with the app (`'mode' => 'autoload'`).

To utilize a database to manage optional modules, first run the following artisan migration:

	php artisan migrate --package="suitetea/modularlaravel"

If you wish to override the package settings, publish the package settings and edit that file.

Publish config: `php artisan config:publish suitetea/modularlaravel`. The new config settings are located in `app/config/packages/suitetea/modularlaravel/config.php`.

## Example Module Service Provider

	<?php namespace SuiteTea\Modules\Contacts;
	
	class ContactsServiceProvider extends \SuiteTea\ModularLaravel\ModuleServiceProvider {
	
		public function load()
		{
			$package_name = strtolower($this->module->name);
			$this->package('app/' . $package_name, $package_name, __DIR__);
			
			require __DIR__ . '/routes.php';
		}
	
	}