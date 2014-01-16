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