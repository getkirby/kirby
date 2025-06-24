<?php

namespace Kirby\Text;

use Kirby\Cms\App;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;

class LinkKirbyTagTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Text.LinkKirbyTag';

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			]
		]);

		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	public function testWithLangAttribute()
	{
		$app = $this->app->clone([
			'urls' => [
				'index' => 'https://getkirby.com'
			],
			'languages' => [
				'en' => [
					'code' => 'en'
				],
				'de' => [
					'code' => 'de'
				]
			],
			'site' => [
				'children' => [
					['slug' => 'a']
				]
			]
		]);

		$this->assertSame('<a href="https://getkirby.com/en/a">getkirby.com/en/a</a>', $app->kirbytags('(link: a lang: en)'));
		$this->assertSame('<a href="https://getkirby.com/de/a">getkirby.com/de/a</a>', $app->kirbytags('(link: a lang: de)'));
	}

	public function testWithHash()
	{
		$app = $this->app->clone([
			'urls' => [
				'index' => 'https://getkirby.com'
			],
			'languages' => [
				'en' => [
					'code' => 'en'
				],
				'de' => [
					'code' => 'de'
				]
			],
			'site' => [
				'children' => [
					['slug' => 'a']
				]
			]
		]);

		$this->assertSame('<a href="https://getkirby.com/en/a">getkirby.com/en/a</a>', $app->kirbytags('(link: a)'));
		$this->assertSame('<a href="https://getkirby.com/de/a">getkirby.com/de/a</a>', $app->kirbytags('(link: a lang: de)'));
		$this->assertSame('<a href="https://getkirby.com/en/a#anchor">getkirby.com/en/a</a>', $app->kirbytags('(link: a#anchor lang: en)'));
		$this->assertSame('<a href="https://getkirby.com/de/a#anchor">getkirby.com/de/a</a>', $app->kirbytags('(link: a#anchor lang: de)'));
	}

	public function testWithUuid()
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
								'content' => ['uuid' => 'file-uuid'],
							]
						]
					]
				]
			]
		]);

		$result = $app->kirbytags('(link: page://page-uuid)');
		$this->assertSame('<a href="https://getkirby.com/a">getkirby.com/a</a>', $result);

		$result = $app->kirbytags('(link: page://not-exists)');
		$this->assertSame('<a href="https://getkirby.com/error">getkirby.com/error</a>', $result);

		$result = $app->kirbytags('(link: file://file-uuid text: file)');
		$this->assertSame('<a href="' . $app->file('a/foo.jpg')->url() . '">file</a>', $result);

		$result = $app->kirbytags('(link: file://not-exists text: file)');
		$this->assertSame('<a href="https://getkirby.com/error">file</a>', $result);
	}

	public function testWithUuidDebug()
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
								'content' => ['uuid' => 'file-uuid'],
							]
						]
					]
				]
			],
			'options' => [
				'debug' => true
			]
		]);

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The linked page cannot be found');

		$app->kirbytags('(link: page://not-exists)');
	}

	public function testWithUuidDebugText()
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
								'content' => ['uuid' => 'file-uuid'],
							]
						]
					]
				]
			],
			'options' => [
				'debug' => true
			]
		]);

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The linked page cannot be found for the link text "click here"');

		$app->kirbytags('(link: page://not-exists text: click here)');
	}

	public function testWithUuidAndLang()
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
					'code'    => 'de',
					'name'    => 'Deutsch',
					'locale'  => 'de_DE',
					'url'     => '/de',
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
										'code' => 'en',
										'content' => ['uuid' => 'file-uuid']
									],
									[
										'code' => 'de',
										'content' => []
									]
								]
							]
						],
						'translations' => [
							[
								'code' => 'en',
								'content' => ['uuid' => 'page-uuid']
							],
							[
								'code' => 'de',
								'content' => ['slug' => 'ae']
							]
						]
					]
				]
			]
		]);

		$result = $app->kirbytags('(link: page://page-uuid lang: de)');
		$this->assertSame('<a href="https://getkirby.com/de/ae">getkirby.com/de/ae</a>', $result);

		$result = $app->kirbytags('(link: file://file-uuid text: file lang: de)');
		$this->assertSame('<a href="' . $app->file('a/foo.jpg')->url() . '">file</a>', $result);
	}
}
