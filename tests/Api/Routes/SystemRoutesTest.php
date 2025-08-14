<?php

namespace Kirby\Api;

use Kirby\Cms\App;
use Kirby\TestCase;

class SystemRoutesTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.SystemRoutes';

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			]
		]);
	}

	public function testGetWithoutUser(): void
	{
		$response = $this->app->api()->call('system', 'GET');

		$this->assertArrayNotHasKey('user', $response['data']);
	}

	public function testGetWithUser(): void
	{
		$this->app->impersonate('kirby');

		$response = $this->app->api()->call('system', 'GET');

		$this->assertArrayHasKey('user', $response['data']);
	}
}
