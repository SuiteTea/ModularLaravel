<?php namespace SuiteTea\ModularLaravel\Test;

use \Mockery as m;
use SuiteTea\ModularLaravel\Manager;
use Illuminate\Support\Collection;
use Illuminate\Events\Dispatcher;

class ManagerEventsTest extends \PHPUnit_Framework_TestCase
{
    protected $manager;

    protected $dispatcher;

    protected function setUp()
    {
        $view = m::mock('Illuminate\View\Factory');
        $view->shouldReceive('addNamespace')->withAnyArgs();

        $loader = m::mock('Composer\Autoload\ClassLoader');
        $loader->shouldReceive('addPsr4')->withAnyArgs();
        $loader->shouldReceive('register');

        $container = m::mock('Illuminate\Container\Container');

        $this->dispatcher = new Dispatcher($container);

        $this->manager = new Manager(new Collection, $view, $loader, $this->dispatcher);
    }

    protected function tearDown()
    {
        m::close();
    }

    public function testModuleActivationFiresActivationEvent()
    {
        $this->manager->register(
            [
                'name' => 'auth',
                'directory' => __DIR__
            ]
        );

        $activationSuccess = false;

        $this->dispatcher->listen(
            Manager::EVENT_ACTIVE . '*',
            function ($module) use (&$activationSuccess) {
                $activationSuccess = $this->manager->active->has($module->name);
                $this->assertEquals('auth', $module->name);
            }
        );

        $this->manager->go();

        $this->assertTrue($activationSuccess);
    }

    public function testModuleActivationFiresActivationFailedEvent()
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

        $this->dispatcher->listen(
            Manager::EVENT_ACTIVATION_FAILED . '*',
            function ($module) {
                $this->assertEquals('attachments', $module->name);
            }
        );

        $this->manager->go();

        $this->assertFalse($this->manager->active->has('attachments'));
    }
}