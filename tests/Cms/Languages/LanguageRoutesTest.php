<?php

namespace Kirby\Cms;

use Kirby\TestCase;

class LanguageRoutesTest extends TestCase
{
	public function setUp(): void
	{
		App::destroy();

		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'languages' => [
				[
					'code'    => 'en',
					'name'    => 'English',
					'default' => true,
					'locale'  => 'en_US.UTF-8',
					'url'     => '/',
				],
				[
					'code'    => 'de',
					'name'    => 'Deutsch',
					'locale'  => 'de_AT.UTF-8',
					'url'     => '/de',
				],
			]
		]);
	}

	public function testFallback()
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'     => 'error',
						'template' => 'error'
					]
				]
			]
		]);

		$app->call('notes');
		$this->assertSame($app->language()->code(), 'en');

		$app->call('de/notes');
		$this->assertSame($app->language()->code(), 'de');
	}

	public function testNotNextWhenFalsyReturn()
	{
		$a = $b = $c = $d = $e = 0;

		$app = $this->app->clone([
			'options' => [
				'routes' => [
					[
						'pattern' => 'route-a',
						'action'  => function () use (&$a) {
							$a++;
							return false;
						},
					],
					[
						'pattern' => 'route-b',
						'language' => '*',
						'action'  => function ($language) use (&$b) {
							$b++;
							return false;
						},
					],
					[
						'pattern' => 'route-c',
						'language' => 'en',
						'action'  => function ($language) use (&$c) {
							$c++;
							return false;
						},
					],
					[
						'pattern' => 'route-d',
						'language' => 'de',
						'action'  => function ($language) use (&$d) {
							$d++;
							return false;
						},
					],
					[
						'pattern' => 'route-e',
						'language' => '*',
						'action'  => function ($language) use (&$e) {
							$e++;
							return null;
						},
					],
				],
			]
		]);

		$this->assertSame(0, $a);
		$this->assertSame(0, $b);
		$this->assertSame(0, $c);
		$this->assertSame(0, $d);
		$this->assertSame(0, $e);

		$app->call('route-a');
		$app->call('route-b');
		$app->call('route-c');
		$app->call('de/route-d');
		$app->call('route-e');

		$this->assertSame(1, $a);
		$this->assertSame(1, $b);
		$this->assertSame(1, $c);
		$this->assertSame(1, $d);
		$this->assertSame(2, $e);
	}
}
