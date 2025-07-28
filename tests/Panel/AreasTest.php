<?php

namespace Kirby\Panel;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Areas::class)]
class AreasTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Areas';

	public function testArea(): void
	{
		// defaults
		$area = Areas::area('test', []);

		$this->assertInstanceOf(Area::class, $area);
		$this->assertSame('test', $area->id());
		$this->assertSame('test', $area->label());
		$this->assertSame([], $area->breadcrumb());
		$this->assertSame('test', $area->breadcrumbLabel());
		$this->assertSame('test', $area->title());
		$this->assertFalse($area->menu());
		$this->assertNull($area->search());
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
		$areas = Areas::for($this->app);
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
		$areas       = Areas::for($this->app);
		$withCustoms = $areas->buttons();

		$this->assertSame(2, count($withCustoms) - count($core));
		$this->assertSame(['component' => 'test-b'], array_pop($withCustoms));
		$this->assertSame(['component' => 'test-a'], array_pop($withCustoms));
	}

	public function testFor(): void
	{
		// unauthenticated / uninstalled
		$areas = Areas::for($this->app);

		$this->assertTrue($areas->has('installation'));
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
		$areas = Areas::for($this->app);

		$this->assertTrue($areas->has('login'));
		$this->assertTrue($areas->has('logout'));
		$this->assertCount(2, $areas);

		// simulate a logged in user
		$this->app->impersonate('test@getkirby.com');

		// authenticated
		$areas = Areas::for($this->app);

		$this->assertTrue($areas->has('search'));
		$this->assertTrue($areas->has('site'));
		$this->assertTrue($areas->has('system'));
		$this->assertTrue($areas->has('users'));
		$this->assertTrue($areas->has('account'));
		$this->assertTrue($areas->has('logout'));
		$this->assertTrue($areas->has('lab'));
		$this->assertCount(7, $areas);

		// authenticated with plugins
		$app = $this->app->clone([
			'areas' => [
				'todos' => fn () => []
			]
		]);

		$app->impersonate('test@getkirby.com');

		$areas = Areas::for($app);

		$this->assertTrue($areas->has('todos'));
		$this->assertCount(8, $areas);
	}
}
