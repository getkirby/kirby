<?php

namespace Kirby\Cms;

use Kirby\TestCase;

class LockRoutesTest extends TestCase
{
	protected $app;

	public function setUp(): void
	{
		$this->app = new App([
			'options' => [
				'api.allowImpersonation' => true
			],
			'roots' => [
				'index' => '/dev/null'
			]
		]);
	}

	public function testGet()
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
					]
				]
			]
		]);

		$app->impersonate('kirby');

		$response = $app->api()->call('pages/a/lock');
		$expected = [
			'lock' => false
		];

		$this->assertSame($expected, $response);
	}
}
