<?php namespace SuiteTea\ModularLaravel;

class Facade extends \Illuminate\Support\Facades\Facade
{
    protected static function getFacadeAccessor() { return 'suitetea.modules'; }
}