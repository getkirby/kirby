<?php

namespace Kirby\Text\Tag;

use Kirby\Cms\App;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LinkTag::class)]
class LinkTagTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Text.LinkTag';

	protected function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			]
		]);

		Dir::make(static::TMP);
	}

	protected function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	public function testRenderWithLangAttribute(): void
	{
		$this->app->clone([
			'urls' => [
				'index' => 'https://getkirby.com'
			],
			'languages' => [
				['code' => 'en'],
				['code' => 'de']
			],
			'site' => [
				'children' => [
					['slug' => 'a']
				]
			]
		]);

		$this->assertSame(
			'<a href="https://getkirby.com/en/a">getkirby.com/en/a</a>',
			(LinkTag::factory('link', 'a', ['lang' => 'en']))->render()
		);
		$this->assertSame(
			'<a href="https://getkirby.com/de/a">getkirby.com/de/a</a>',
			(LinkTag::factory('link', 'a', ['lang' => 'de']))->render()
		);
	}

	public function testRenderWithHash(): void
	{
		$this->app->clone([
			'urls' => [
				'index' => 'https://getkirby.com'
			],
			'languages' => [
				['code' => 'en'],
				['code' => 'de']
			],
			'site' => [
				'children' => [
					['slug' => 'a']
				]
			]
		]);

		$this->assertSame(
			'<a href="https://getkirby.com/en/a">getkirby.com/en/a</a>',
			(LinkTag::factory('link', 'a'))->render()
		);
		$this->assertSame(
			'<a href="https://getkirby.com/de/a">getkirby.com/de/a</a>',
			(LinkTag::factory('link', 'a', ['lang' => 'de']))->render()
		);
		$this->assertSame(
			'<a href="https://getkirby.com/en/a#anchor">getkirby.com/en/a</a>',
			(LinkTag::factory('link', 'a#anchor', ['lang' => 'en']))->render()
		);
		$this->assertSame(
			'<a href="https://getkirby.com/de/a#anchor">getkirby.com/de/a</a>',
			(LinkTag::factory('link', 'a#anchor', ['lang' => 'de']))->render()
		);
	}

	public function testRenderWithUuid(): void
	{
		$app = $this->app->clone([
			'urls' => [
				'index' => 'https://getkirby.com'
			],
			'site' => [
				'children' => [
					[
						'slug'    => 'a',
						'content' => ['uuid' => 'page-uuid'],
						'files'   => [
							[
								'filename' => 'foo.jpg',
								'content'  => ['uuid' => 'file-uuid'],
							]
						]
					]
				]
			]
		]);

		$this->assertSame(
			'<a href="https://getkirby.com/a">getkirby.com/a</a>',
			(LinkTag::factory('link', 'page://page-uuid'))->render()
		);
		$this->assertSame(
			'',
			(LinkTag::factory('link', 'page://not-exists'))->render()
		);
		$this->assertSame(
			'<a href="' . $app->file('a/foo.jpg')->url() . '">file</a>',
			(LinkTag::factory('link', 'file://file-uuid', ['text' => 'file']))->render()
		);
		$this->assertSame(
			'<span>file</span>',
			(LinkTag::factory('link', 'file://not-exists', ['text' => 'file']))->render()
		);
	}

	public function testRenderWithUuidDebug(): void
	{
		$this->app->clone([
			'urls' => [
				'index' => 'https://getkirby.com'
			],
			'site' => [
				'children' => [
					[
						'slug'    => 'a',
						'content' => ['uuid' => 'page-uuid']
					]
				]
			],
			'options' => [
				'debug' => true
			]
		]);

		$this->assertSame(
			'<span class="kirby-broken-link">🚨 The link &quot;page://not-exists&quot; cannot be found</span>',
			(LinkTag::factory('link', 'page://not-exists'))->render()
		);
		$this->assertSame(
			'<span class="kirby-broken-link my-link">🚨 The link &quot;page://not-exists&quot; cannot be found</span>',
			(LinkTag::factory('link', 'page://not-exists', ['class' => 'my-link']))->render()
		);
	}

	public function testRenderWithUuidDebugText(): void
	{
		$this->app->clone([
			'urls' => [
				'index' => 'https://getkirby.com'
			],
			'site' => [
				'children' => [
					[
						'slug'    => 'a',
						'content' => ['uuid' => 'page-uuid']
					]
				]
			],
			'options' => [
				'debug' => true
			]
		]);

		$this->assertSame(
			'<span class="kirby-broken-link">🚨 The link &quot;page://not-exists&quot; cannot be found for the link text &quot;click here&quot;</span>',
			(LinkTag::factory('link', 'page://not-exists', ['text' => 'click here']))->render()
		);
	}

	public function testRenderWithUuidAndLang(): void
	{
		$app = $this->app->clone([
			'urls' => [
				'index' => 'https://getkirby.com'
			],
			'languages' => [
				[
					'code'    => 'en',
					'name'    => 'English',
					'default' => true,
					'locale'  => 'en_US',
					'url'     => '/',
				],
				[
					'code'   => 'de',
					'name'   => 'Deutsch',
					'locale' => 'de_DE',
					'url'    => '/de',
				],
			],
			'site' => [
				'children' => [
					[
						'slug'  => 'a',
						'files' => [
							[
								'filename'     => 'foo.jpg',
								'translations' => [
									[
										'code'    => 'en',
										'content' => ['uuid' => 'file-uuid']
									],
									[
										'code'    => 'de',
										'content' => []
									]
								]
							]
						],
						'translations' => [
							[
								'code'    => 'en',
								'content' => ['uuid' => 'page-uuid']
							],
							[
								'code'    => 'de',
								'content' => ['slug' => 'ae']
							]
						]
					]
				]
			]
		]);

		$this->assertSame(
			'<a href="https://getkirby.com/de/ae">getkirby.com/de/ae</a>',
			(LinkTag::factory('link', 'page://page-uuid', ['lang' => 'de']))->render()
		);
		$this->assertSame(
			'<a href="' . $app->file('a/foo.jpg')->url() . '">file</a>',
			(LinkTag::factory('link', 'file://file-uuid', ['text' => 'file', 'lang' => 'de']))->render()
		);
	}
}
