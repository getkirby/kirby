<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Page::class)]
class PagePreviewUrlTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.PagePreviewUrl';

	public static function providerForBlueprintSettings(): array
	{
		return [
			[null, '/test', null, false],
			[null, '/test?{token}', 'test', true],
			[true, '/test', null, false],
			[true, '/test?{token}', 'test', true],
			['/something/different', '/something/different', null, false],
			['/something/different', '/something/different?{token}', 'something\/different', true],
			['{{ site.url }}#{{ page.slug }}', '/#test', null, false],
			['{{ site.url }}#{{ page.slug }}', '/?{token}#test', '', true],
			['{{ page.url }}?preview=true', '/test?preview=true&{token}', 'test', true],
			[false, null, null, false],
			[false, null, null, true],
			[null, null, null, false, false],
		];
	}

	public function testPreviewUrl(): void
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$this->assertSame('/test', $page->previewUrl());
	}

	#[DataProvider('providerForBlueprintSettings')]
	public function testPreviewUrlWithBlueprintSettings(
		$input,
		$expected,
		$expectedUri,
		bool $draft,
		bool $authenticated = true
	): void {
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
		$page = new Page([
			'slug' => 'test',
			'isDraft' => $draft,
			'blueprint' => [
				'name'    => 'test',
				'options' => $options
			]
		]);

		if ($draft === true && $expected !== null) {
			$expectedToken = substr(hash_hmac('sha1', '{"uri":"' . $expectedUri . '","versionId":"latest"}', $page->kirby()->root('content')), 0, 10);
			$expected = str_replace(
				'{token}',
				'_token=' . $expectedToken,
				$expected
			);
		}

		$this->assertSame($expected, $page->previewUrl());
	}

	public function testPreviewUrlUnauthenticated(): void
	{
		// log out
		$this->app->impersonate();

		$page = new Page([
			'slug' => 'test'
		]);

		$this->assertNull($page->previewUrl());
	}
}
