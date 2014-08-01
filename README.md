# Modular Laravel

The Modular Laravel allows for Laravel code to be organized in smaller sets within an application.

## Install

Via Composer

	{
    	"require": {
        	"suitetea/modularlaravel": "0.3.*"
    	}
	}
	
Next run an update from Composer

	composer update
	
After installation is complete, add the service provider to the `app/config/app.php` providers array.

	SuiteTea\ModularLaravel\ModularLaravelServiceProvider

## Modules

Modules are self contained packages that can be installed via Composer or instantiated manually.

Modules follow the PSR-4 package structure and should adhere to its standards.

### Registration

Modules need to register with ModularLaravel. Example registration with available configuration is below:

```
ModularLaravel::register([
	'name' => 'attachments',
	'directory' => 'app/modules/attachments',
	'requires' => [
		'uploader',
		'file_system'
	],
	'namespace' => 'Modules\Attachments',
    'autoload' => [
        'files' => [
            'routes.php'
        ],
        'classmap' => [
            'controllers'
        ]
    ]
]);
```

### Pre-Registration

You can pre-register a module before ModularLaravel is instantiated. This is useful when a module is installed via Composer. You can autoload a file, pre-register the module, and when Laravel is booted, the module will attempt activation.

```
use SuiteTea\ModularLaravel\Manager;

Manager::preRegister([
	'name' => 'attachments',
	'directory' => 'app/modules/attachments',
	'requires' => [
		'uploader',
		'file_system'
	],
	'namespace' => 'Modules\Attachments',
    'autoload' => [
        'files' => [
            'routes.php'
        ],
        'classmap' => [
            'controllers'
        ]
    ]
]);
```

### Config Options

- **name** (required) - The name of the module. Used for registration and dependency management.
- **directory** (required) - The root directory of the module. (If autoloading via Composer, `__DIR__` can be used as a shortcut)
- **requires** - Any modules that this module depends upon. These dependencies must be installed and activated in order for this module to be activated.
- **namespace** - You can specify a specific namespace for a module for use with class autoloaders. Specifying this option will register the directory with a class autoloader. This is helpful if the module is not installed via Composer.
- **autoload** - Similar to Composer's autoload. Only accepts 'files' and 'classmap'. `files` will include any files in this array. `classmap` will add these directories to the class autoloader.

### Events

ModularLaravel fires two types of events when booting. 

- **modules.active** - fires when a module is activated successfully. This event appends the module name to the event name, ex: `"modules.active attachments"`.
- **modules.activation_failed** - fires when a module cannot be activated. Similar to the `modules.active` event, this event appends the module name to the end of the event, ex: `"modules.activation_failed attachments"`.

### Views

A module can include a `views` directory. ModuleLaravel registers a view namespace equal to the name of the module. This is helpful when referring to a specific module's views. 

Example: a view file called `upload.blade.php` would be referrenced like so - `View::make('attachments::upload');

===

**Todo:**

- Create a configuration array that dictates which modules to activate. This can be manually coded or dynamic through a database.
