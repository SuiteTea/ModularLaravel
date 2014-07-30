<?php namespace SuiteTea\ModularLaravel\Test;

use \Mockery as m;
use SuiteTea\ModularLaravel\Manager;
use Illuminate\Support\Collection;

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $manager;

    protected function setUp()
    {
        $view = m::mock('Illuminate\View\Factory');
        $view->shouldReceive('addNamespace')->withAnyArgs();

        $loader = m::mock('Composer\Autoload\ClassLoader');
        $loader->shouldReceive('addPsr4')->withAnyArgs();
        $loader->shouldReceive('register');

        $dispatcher = m::mock('Illuminate\Events\Dispatcher');
        $dispatcher->shouldReceive('fire');

        $this->manager = new Manager(new Collection, $view, $loader, $dispatcher);
    }

    protected function tearDown()
    {
        m::close();
    }

    public function testModuleRegistrationConfigValidationPasses()
    {
        $this->manager->register(
            [
                'name' => 'attachments',
                'directory' => __DIR__
            ]
        );

        $this->assertTrue($this->manager->registry->has('attachments'));
    }

    public function testModuleRegistrationConfigValidationFails()
    {
        // Should fail when a 'required' parameter is not passed. In this
        // instance, 'directory' is not passed.
        $this->setExpectedException('InvalidArgumentException');
        $this->assertInstanceOf(
            'InvalidArgumentException',
            $this->manager->register(
                [
                    'name' => 'attachments'
                ]
            )
        );
    }

    public function testModuleRegistrationDuplicateFails()
    {
        // Enter an 'attachments' module for duplication validation testing
        $this->manager->register(
            [
                'name' => 'attachments',
                'directory' => __DIR__
            ]
        );

        // Should fail because 'attachments' already exists'
        $this->setExpectedException('SuiteTea\ModularLaravel\Exceptions\DuplicateModuleException');
        $this->assertInstanceOf(
            'SuiteTea\ModularLaravel\Exceptions\DuplicateModuleException',
            $this->manager->register(
                [
                    'name' => 'attachments',
                    'directory' => __DIR__
                ]
            )
        );
    }

    public function testModuleManagerGoActivatesModules()
    {
        $this->manager->register(
            [
                'name' => 'attachments',
                'directory' => __DIR__
            ]
        );
        $this->manager->register(
            [
                'name' => 'auth',
                'directory' => __DIR__
            ]
        );

        $this->manager->go();

        $this->assertTrue($this->manager->active->has('auth'));
    }

    public function testModuleManagerWaitsForModuleDependencies()
    {
        $this->manager->register(
            [
                'name' => 'attachments',
                'directory' => __DIR__,
                'requires' => [
                    'auth'
                ]
            ]
        );
        $this->manager->register(
            [
                'name' => 'auth',
                'directory' => __DIR__
            ]
        );

        $this->manager->go();

        $this->assertTrue($this->manager->active->has('attachments'));
    }

    public function testModuleFailsToActivateIfMissingDependencies()
    {
        $this->manager->register(
            [
                'name' => 'attachments',
                'directory' => __DIR__,
                'requires' => [
                    'auth'
                ]
            ]
        );

        $this->manager->go();

        $this->assertFalse($this->manager->active->has('attachments'));
    }

    public function testMultipleModulesFailToActivateIfMissingDependencies()
    {
        $this->manager->register(
            [
                'name' => 'attachments',
                'directory' => __DIR__,
                'requires' => [
                    'auth'
                ]
            ]
        );

        $this->manager->register(
            [
                'name' => 'quotes',
                'directory' => __DIR__,
                'requires' => [
                    'auth'
                ]
            ]
        );

        $this->manager->go();

        $this->assertFalse($this->manager->active->has('attachments'));
        $this->assertFalse($this->manager->active->has('quotes'));
    }
}