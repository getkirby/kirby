<?php

namespace Kirby\Cms;

use Exception;
use Kirby\TestCase;

class LanguagesRoutesTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.LanguagesRoutes';

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'options' => [
				'api.allowImpersonation' => true,
				'languages' => true
			],
			'languages' => [
				[
					'code'    => 'en',
					'name'    => 'English',
					'default' => true
				],
				[
					'code'    => 'de',
					'name'    => 'Deutsch',
				]
			]
		]);

		$this->app->impersonate('kirby');
	}

	public function testList()
	{
		$app = $this->app;

		$response = $app->api()->call('languages');

		$this->assertSame('en', $response['data'][0]['code']);
		$this->assertSame('de', $response['data'][1]['code']);
	}

	public function testListDisabled()
	{
		$app = $this->app->clone([
			'options' => [
				'languages' => false
			]
		]);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('No route found for path: "languages" and request method: "GET"');

		$response = $app->api()->call('languages');
	}

	public function testCreateDisabled()
	{
		$app = $this->app->clone([
			'options' => [
				'languages' => false
			]
		]);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('No route found for path: "languages" and request method: "POST"');

		$response = $app->api()->call('languages', 'POST');
	}

	public function testGet()
	{
		$app = $this->app;

		$response = $app->api()->call('languages/de');

		$this->assertSame('de', $response['data']['code']);
	}

	public function testGetDisabled()
	{
		$app = $this->app->clone([
			'options' => [
				'languages' => false
			]
		]);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('No route found for path: "languages/de" and request method: "GET"');

		$response = $app->api()->call('languages/de');
	}

	public function testUpdateDisabled()
	{
		$app = $this->app->clone([
			'options' => [
				'languages' => false
			]
		]);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('No route found for path: "languages/de" and request method: "PATCH"');

		$response = $app->api()->call('languages/de', 'PATCH');
	}

	public function testDeleteDisabled()
	{
		$app = $this->app->clone([
			'options' => [
				'languages' => false
			]
		]);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('No route found for path: "languages/de" and request method: "DELETE"');

		$response = $app->api()->call('languages/de', 'DELETE');
	}
}
