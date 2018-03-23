<?php

namespace Kirby\Cms;

use Closure;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{

    public $page = null;

    public function setUp()
    {
        App::removePlugins();
    }

    public function tearDown()
    {
        App::removePlugins();
    }

    public function kirby($props = []): App
    {
        return new App($props);
    }

    public function site(): Site
    {
        return $this->kirby()->site();
    }

    public function pages()
    {
        return $this->site()->children();
    }

    public function page(string $id = null)
    {
        if ($id !== null) {
            return $this->site()->find($id);
        }

        if ($this->page !== null) {
            return $this->site()->find($this->page);
        }

        return $this->site()->homePage();
    }

    public function assertIsSite($input)
    {
        $this->assertInstanceOf(Site::class, $input);
    }

    public function assertIsPage($input, $id = null)
    {
        $this->assertInstanceOf(Page::class, $input);

        if (is_string($id)) {
            $this->assertEquals($id, $input->id());
        }

        if (is_a($id, Page::class)) {
            $this->assertEquals($input, $id);
        }
    }

    public function assertIsFile($input, $id = null)
    {
        $this->assertInstanceOf(File::class, $input);

        if (is_string($id)) {
            $this->assertEquals($id, $input->id());
        }

        if (is_a($id, File::class)) {
            $this->assertEquals($input, $id);
        }
    }

    public function assertHooks(array $hooks, Closure $action)
    {
        $phpUnit   = $this;
        $triggered = 0;

        foreach ($hooks as $name => $callback) {
            $hooks[$name] = function (...$arguments) use ($callback, $phpUnit, &$triggered) {
                $callback->call($phpUnit, ...$arguments);
                $triggered++;
            };
        }

        App::removePlugins();

        $app = new App([
            'hooks' => $hooks,
            'user'  => 'test@getkirby.com',
            'users' => [
                [
                    'email' => 'test@getkirby.com',
                    'role'  => 'admin'
                ]
            ]
        ]);

        $action->call($this);
        $this->assertEquals(count($hooks), $triggered);
    }

}
