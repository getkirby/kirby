<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Areas::class)]
class AreasTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Areas';

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP,
			]
		]);
	}

	public function tearDown(): void
	{
		// clear session file first
		$this->app->session()->destroy();

		Dir::remove(static::TMP);
	}

	public function testArea(): void
	{
		// defaults
		$area = Areas::area('test', []);

		$this->assertSame('test', $area->id());
		$this->assertSame('test', $area->label());
		$this->assertSame([], $area->breadcrumb());
		$this->assertSame('test', $area->breadcrumbLabel());
		$this->assertSame('test', $area->title());
		$this->assertFalse($area->menu());
		$this->assertSame('test', $area->link());
		$this->assertNull($area->search());
	}

	public function testToArray(): void
	{
		// unauthenticated / uninstalled
		$areas = new Areas();
		$areas = $areas->toArray();

		$this->assertArrayHasKey('installation', $areas);
		$this->assertCount(1, $areas);

		// create the first admin
		$this->app = $this->app->clone([
			'users' => [
				[
					'email' => 'test@getkirby.com',
					'role'  => 'admin'
				]
			]
		]);

		// unauthenticated / installed
		$areas = new Areas();
		$areas = $areas->toArray();

		$this->assertArrayHasKey('login', $areas);
		$this->assertArrayHasKey('logout', $areas);
		$this->assertCount(2, $areas);

		// simulate a logged in user
		$this->app->impersonate('test@getkirby.com');

		// authenticated
		$areas = new Areas();
		$areas = $areas->toArray();

		$this->assertArrayHasKey('search', $areas);
		$this->assertArrayHasKey('site', $areas);
		$this->assertArrayHasKey('system', $areas);
		$this->assertArrayHasKey('users', $areas);
		$this->assertArrayHasKey('account', $areas);
		$this->assertArrayHasKey('logout', $areas);
		$this->assertArrayHasKey('lab', $areas);
		$this->assertCount(7, $areas);

		// authenticated with plugins
		$app = $this->app->clone([
			'areas' => [
				'todos' => fn () => []
			]
		]);

		$app->impersonate('test@getkirby.com');

		$areas = new Areas();
		$areas = $areas->toArray();

		$this->assertArrayHasKey('todos', $areas);
		$this->assertCount(8, $areas);
	}

	public function testButtons(): void
	{
		$this->app = $this->app->clone([
			'users' => [
				[
					'email' => 'test@getkirby.com',
					'role'  => 'admin'
				]
			]
		]);

		$this->app->impersonate('test@getkirby.com');
		$areas = new Areas();
		$core  = $areas->buttons();

		// add custom buttons
		$this->app = $this->app->clone([
			'areas' => [
				'test' => fn () => [
					'buttons' => [
						'a' => ['component' => 'test-a'],
						'b' => ['component' => 'test-b']
					]
				]
			]
		]);

		$this->app->impersonate('test@getkirby.com');
		$areas       = new Areas();
		$withCustoms = $areas->buttons();

		$this->assertSame(2, count($withCustoms) - count($core));
		$this->assertSame(['component' => 'test-b'], array_pop($withCustoms));
		$this->assertSame(['component' => 'test-a'], array_pop($withCustoms));
	}
}
