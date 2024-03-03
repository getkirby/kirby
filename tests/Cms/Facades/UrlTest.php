<?php

namespace Kirby\Cms;

use Kirby\Filesystem\F;

class UrlTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.Url';

	protected $app;

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'urls' => [
				'index' => 'https://getkirby.com'
			]
		]);
	}

	public function testHome()
	{
		$this->assertSame('https://getkirby.com', Url::home());
	}

	public function testTo()
	{
		$this->assertSame('https://getkirby.com', Url::to());
		$this->assertSame('https://getkirby.com', Url::to(''));
		$this->assertSame('https://getkirby.com', Url::to('/'));
		$this->assertSame('https://getkirby.com/projects', Url::to('projects'));
	}

	public function testToWithLanguage()
	{
		$this->app->clone([
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
					['slug' => 'a'],
					['slug' => 'b'],
					[
						'slug' => 'c',
						'translations' => [
							[
								'code' => 'de',
								'content' => [
									'slug' => 'custom'
								]
							]
						]
					]
				]
			]
		]);

		$this->assertSame('https://getkirby.com/en/a', Url::to('a'));
		$this->assertSame('https://getkirby.com/en/a', Url::to('a', 'en'));
		$this->assertSame('https://getkirby.com/de/a', Url::to('a', 'de'));

		$this->assertSame('https://getkirby.com/en/a', Url::to('a', ['language' => 'en']));
		$this->assertSame('https://getkirby.com/de/a', Url::to('a', ['language' => 'de']));

		// translated slug
		$this->assertSame('https://getkirby.com/de/custom', Url::to('c', 'de'));
	}

	public function testToTemplateAsset()
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'urls' => [
				'index' => 'https://getkirby.com'
			],
			'site' => [
				'children' => [
					[
						'slug' => 'test',
					]
				]
			]
		]);

		$app->site()->visit('test');

		F::write($app->root('assets') . '/css/default.css', 'test');

		$expected = 'https://getkirby.com/assets/css/default.css';

		$this->assertSame($expected, Url::toTemplateAsset('css', 'css'));

		F::write($app->root('assets') . '/js/default.js', 'test');

		$expected = 'https://getkirby.com/assets/js/default.js';

		$this->assertSame($expected, Url::toTemplateAsset('js', 'js'));
	}
}
