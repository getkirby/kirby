<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Site::class)]
class SitePreviewUrlTest extends ModelTestCase
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
			],
			'children' => [
				['slug' => 'home']
			]
		]);

		$this->assertSame($expected, $site->previewUrl());
	}

	public function testPreviewUrlMissingHomePage(): void
	{
		$site = new Site();

		$this->assertNull($site->previewUrl());
	}

	public function testPreviewUrlMissingPermission(): void
	{
		$this->app = $this->app->clone([
			'users' => [
				[
					'id'    => 'test',
					'email' => 'test@getkirby.com',
					'role'  => 'editor'
				]
			],
			'roles' => [
				[
					'id'          => 'editor',
					'name'        => 'editor',
					'permissions' => [
						'pages' => [
							'preview' => false
						]
					]
				]
			]
		]);

		$this->app->impersonate('test@getkirby.com');

		$site = new Site([
			'children' => [
				['slug' => 'home']
			]
		]);

		$this->assertNull($site->previewUrl());
		$this->assertNull($site->previewUrl('latest'));
		$this->assertNull($site->previewUrl('changes'));
	}

	public function testPreviewUrlUnauthenticated(): void
	{
		// log out
		$this->app->impersonate();

		$site = new Site([
			'children' => [
				['slug' => 'home']
			]
		]);

		$this->assertNull($site->previewUrl());
	}
}
