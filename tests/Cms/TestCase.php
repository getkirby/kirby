<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Blueprint\Blueprint;
use Kirby\TestCase as BaseTestCase;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

class TestCase extends BaseTestCase
{
	protected Page|null $page = null;

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

		$this->tearDownTmp();

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

	public function page(string|null $id = null)
	{
		if ($id !== null) {
			return $this->site()->find($id);
		}

		if ($this->page !== null) {
			return $this->site()->find($this->page);
		}

		return $this->site()->homePage();
	}

	public function assertHooks(
		array $hooks,
		Closure $action,
		$appProps = []
	): void {
		$phpUnit   = $this;
		$triggered = 0;

		foreach ($hooks as $name => $callback) {
			$hooks[$name] = function (...$arguments) use ($callback, $phpUnit, &$triggered) {
				$callback->call($phpUnit, ...$arguments);
				$triggered++;
			};
		}

		App::destroy();

		$app = new App([
			'hooks' => $hooks,
			'roots' => ['index' => '/dev/null'],
			'user'  => 'test@getkirby.com',
			'users' => [
				[
					'email' => 'test@getkirby.com',
					'role'  => 'admin'
				]
			],
			...$appProps
		]);

		$action->call($this, $app);
		$this->assertSame(count($hooks), $triggered);
	}
}
