<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Filesystem\Dir;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;
use Kirby\TestCase as BaseTestCase;

require_once __DIR__ . '/mocks.php';

class TestCase extends BaseTestCase
{
	protected $app;
	protected $page = null;

	public function setUp(): void
	{
		App::destroy();

		$this->app = new App([
			'roots' => [
				'index' => $this->hasTmp() ? static::TMP : '/dev/null'
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

		if ($this->hasTmp() === true) {
			Dir::remove(static::TMP);
		}

		// mock class
		ErrorLog::$log = '';
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
		$this->assertSame(count($hooks), $triggered);
	}
}
