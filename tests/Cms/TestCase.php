<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected $page = null;
    protected $app;

    public function setUp(): void
    {
        App::destroy();

        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);

        Blueprint::$loaded = [];

        I18n::$locale       = null;
        I18n::$fallback     = 'en';
        I18n::$translations = [];
        Str::$language      = [];
    }

    public function tearDown(): void
    {
        App::destroy();
        Blueprint::$loaded = [];
    }

    public function kirby($props = [])
    {
        return new App($props);
    }

    public function site()
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

    public function assertHooks(array $hooks, Closure $action, $appProps = [])
    {
        $phpUnit   = $this;
        $triggered = 0;

        foreach ($hooks as $name => $callback) {
            $hooks[$name] = function (...$arguments) use ($callback, $phpUnit, &$triggered) {
                $callback->call($phpUnit, ...$arguments);
                $triggered++;
            };
        }

        App::destroy();

        $app = new App(array_merge([
            'hooks' => $hooks,
            'roots' => ['index' => '/dev/null'],
            'user'  => 'test@getkirby.com',
            'users' => [
                [
                    'email' => 'test@getkirby.com',
                    'role'  => 'admin'
                ]
            ]
        ], $appProps));

        $action->call($this, $app);
        $this->assertEquals(count($hooks), $triggered);
    }
}
