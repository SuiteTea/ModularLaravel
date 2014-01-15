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
     * Finder mode (ie: 'autoload', 'database')
     */
    'mode' => 'autoload',

    /**
     * Database table to find and store modules
     */
    'table' => 'modules',

    /**
     * Core modules defined.
     * Core modules automatically load and cannot be disabled.
     *
     * Example:
     * 
     * 'core' => [
     *     [ 'name' => 'Module Name Here' ]
     * ]
     */
    'core' => []

);