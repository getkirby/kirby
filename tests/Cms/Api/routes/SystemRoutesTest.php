<?php

namespace Kirby\Cms;

use Kirby\TestCase;

class SystemRoutesTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.SystemRoutes';

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			]
		]);
	}

	public function testGetWithoutUser()
	{
		$response = $this->app->api()->call('system', 'GET');

		$this->assertArrayNotHasKey('user', $response['data']);
	}

	public function testGetWithUser()
	{
		$this->app->impersonate('kirby');

		$response = $this->app->api()->call('system', 'GET');

		$this->assertArrayHasKey('user', $response['data']);
	}
}
