<?php

namespace Kirby\Cms;

use Kirby\TestCase;

class SystemRoutesTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.SystemRoutes';

	protected $app;

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			]
		]);
	}

	public function testGetWithInvalidServerSoftware()
	{
		// set invalid server software
		$app = $this->app->clone([
			'server' => [
				'SERVER_SOFTWARE' => 'invalid'
			]
		]);

		$response = $app->api()->call('system', 'GET');

		$this->assertFalse($response['data']['isOk']);
		$this->assertFalse($response['data']['requirements']['server']);
	}

	public function testGetWithValidServerSoftware()
	{
		$app = $this->app->clone([
			'server' => [
				'SERVER_SOFTWARE' => 'apache'
			]
		]);

		$response = $app->api()->call('system', 'GET');

		$this->assertTrue($response['data']['isOk']);
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
