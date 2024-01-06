<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\TestCase;
use Kirby\Toolkit\Str;

class ContentLocksTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.ContentLocks';

	protected $app;

	public function app()
	{
		return new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug'  => 'test'
					]
				]
			]
		]);
	}

	public function setUp(): void
	{
		$this->app = $this->app();
		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	public function testFile()
	{
		$app = $this->app;
		$page = $app->page('test');
		$this->assertTrue(Str::endsWith($app->locks()->file($page), 'content/test/.lock'));
	}

	public function testId()
	{
		$app = $this->app;
		$page = $app->page('test');
		$this->assertSame('/test', $app->locks()->id($page));
	}

	public function testGetSet()
	{
		$app = $this->app;
		$page = $app->page('test');
		$root = static::TMP . '/content/test';

		// create temp directory
		$this->assertSame($root . '/.lock', $app->locks()->file($page));
		Dir::make($root);

		// check if empty
		$this->assertSame([], $app->locks()->get($page));
		$this->assertFalse(F::exists($app->locks()->file($page)));

		// set data
		$this->assertTrue($app->locks()->set($page, [
			'lock'   => ['user' => 'homer'],
			'unlock' => []
		]));

		// check if exists
		$this->assertTrue(F::exists($app->locks()->file($page)));
		$this->assertSame([
			'lock' => ['user' => 'homer']
		], $app->locks()->get($page));

		// set null data
		$this->assertTrue($app->locks()->set($page, []));

		// check if empty
		$this->assertSame([], $app->locks()->get($page));
		$this->assertFalse(F::exists($app->locks()->file($page)));
	}
}
