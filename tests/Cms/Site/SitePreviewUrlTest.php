<?php

namespace Kirby\Cms;


use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Site::class)]
class SitePreviewUrlTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.SitePreviewUrl';

	public static function previewUrlProvider(): array
	{
		return [
			[null, '/'],
			['https://test.com', 'https://test.com'],
			['{{ site.url }}#test', '/#test'],
			[false, null],
			[null, null, false],
		];
	}

	#[DataProvider('previewUrlProvider')]
	public function testCustomPreviewUrl(
		$input,
		$expected,
		bool $authenticated = true
	): void {
		$this->app = $this->app->clone([
			'urls' => [
				'index' => '/'
			],
			'users' => [
				[
					'id'    => 'test',
					'email' => 'test@getkirby.com',
					'role'  => 'editor'
				]
			],
			'roles' => [
				[
					'id'    => 'editor',
					'name'  => 'editor',
				]
			]
		]);

		// authenticate
		if ($authenticated) {
			$this->app->impersonate('test@getkirby.com');
		}

		$options = [];

		if ($input !== null) {
			$options = [
				'preview' => $input
			];
		}

		// simple
		$site = new Site([
			'blueprint' => [
				'name'    => 'site',
				'options' => $options
			]
		]);

		$this->assertSame($expected, $site->previewUrl());
	}
}
