<?php

function module_namespace($module)
{
    return app('suitetea.module')->get($module)->getNamespace();
}

function module_controller($module, $controller)
{
    return module_namespace($module) . '\Controllers\\' . $controller;
}