<?php

namespace Kirby\Cms;

use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Url::class)]
class UrlTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.Url';

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

	public function testHome(): void
	{
		$this->assertSame('https://getkirby.com', Url::home());
	}

	public function testSlug(): void
	{
		// default length
		$this->assertSame(
			'this-is-a-very-long-sentence-that-should-be-used-to-test-the-url-slug-method-by-default-it-should-limit-the-slug-to-255-characters-so-let-s-see-how-well-this-works-for-this-new-method-would-be-a-pity-if-at-one-point-this-breaks-would-be-a-pity-if-at-one-p',
			Url::slug('This is a very long sentence that should be used to test the Url::slug() method. By default it should limit the slug to 255 characters. So let\'s see how well this works for this new method. Would be a pity if at one point this breaks. Would be a pity if at one point this breaks. Would be a pity if at one point this breaks.')
		);

		// custom length
		$app = new App([
			'options' => [
				'slugs.maxlength' => 40
			]
		]);

		$this->assertSame(
			'this-is-a-very-long-sentence-that-should',
			Url::slug('This is a very long sentence that should be used to test the `slugs.maxlength` option. This should be cut already after 40 characters.')
		);

		// no length restriction
		$app = new App([
			'options' => [
				'slugs.maxlength' => false
			]
		]);

		$this->assertSame(
			'this-is-a-very-long-sentence-that-should-be-used-to-test-the-url-slug-method-by-default-it-should-limit-the-slug-to-255-characters-but-we-can-disable-the-shortening-so-let-s-see-how-well-this-works-for-this-new-method-would-be-a-pity-if-at-one-point-this-breaks-would-be-a-pity-if-at-one-point-this-breaks-would-be-a-pity-if-at-one-point-this-breaks',
			Url::slug('This is a very long sentence that should be used to test the Url::slug() method. By default it should limit the slug to 255 characters, but we can disable the shortening. So let\'s see how well this works for this new method. Would be a pity if at one point this breaks. Would be a pity if at one point this breaks. Would be a pity if at one point this breaks.')
		);
	}

	public function testTo(): void
	{
		$this->assertSame('https://getkirby.com', Url::to());
		$this->assertSame('https://getkirby.com', Url::to(''));
		$this->assertSame('https://getkirby.com', Url::to('/'));
		$this->assertSame('https://getkirby.com/projects', Url::to('projects'));
	}

	public function testToWithLanguage(): void
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

	public function testToTemplateAsset(): void
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
