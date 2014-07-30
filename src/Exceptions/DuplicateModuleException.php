<?php namespace SuiteTea\ModularLaravel\Exceptions;

class DuplicateModuleException extends \Exception
{
    protected $message = 'The module is a duplicate';
}