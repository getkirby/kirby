<?php

namespace Kirby\Cms;

use Kirby\TestCase;

class TranslationsRoutesTest extends TestCase
{
	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);

		$this->app->impersonate('kirby');
	}

	public function testList(): void
	{
		$app      = $this->app;
		$response = $app->api()->call('translations');
		$files    = glob($app->root('kirby') . '/i18n/translations/*.json');

		$this->assertCount(count($files), $response['data']);
	}

	public function testGet(): void
	{
		$app = $this->app;

		$response = $app->api()->call('translations/de');

		$this->assertSame('de', $response['data']['id']);
		$this->assertSame('Deutsch', $response['data']['name']);
		$this->assertSame('ltr', $response['data']['direction']);
	}
}
