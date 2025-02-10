<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
use Kirby\Filesystem\Dir;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Language::class)]
class LanguageConversionTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.LanguageConversion';

	public function setUp(): void
	{
		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	public function testConvertFromSingleLanguageToMultiLanguage(): void
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			],
		]);

		// create the page model
		Data::write($app->root('content') . '/test/test.txt', [
			'title' => 'Title'
		]);

		$this->assertCount(0, $app->languages());
		$this->assertFalse($app->multilang());

		// get the page model
		$page = $app->page('test');

		// and check if the content is loadable
		$this->assertSame('Title', $page->content()->title()->value());

		// create a new Language to switch to multi-language mode
		$app->impersonate('kirby', function () {
			Language::create([
				'code' => 'en',
			]);
		});

		// make sure that Kirby actually switched to multi-language mode
		$this->assertCount(1, $app->languages());
		$this->assertTrue($app->multilang());

		// the content file should now have been moved to .en
		$this->assertFileExists($page->root() . '/test.en.txt');
		$this->assertFileDoesNotExist($page->root() . '/test.txt');

		// the content should still be loadable for the page
		$this->assertSame('Title', $page->content()->title()->value());
		$this->assertSame('Title', $page->content('en')->title()->value());

		$this->assertSame('Title', Data::read($page->root() . '/test.en.txt')['title']);
	}

	public function testConvertFromMultiLanguageToSingleLanguage(): void
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			],
		]);

		// create two languages
		Data::write($app->root('languages') . '/en.php', [
			'code'    => 'en',
			'default' => true
		]);

		Data::write($app->root('languages') . '/de.php', [
			'code' => 'de',
		]);

		// create some models
		Data::write($app->root('content') . '/test/test.en.txt', [
			'title' => 'English Title'
		]);

		Data::write($app->root('content') . '/test/test.de.txt', [
			'title' => 'German Title'
		]);

		$this->assertCount(2, $app->languages());
		$this->assertTrue($app->multilang());

		// get the page model
		$page = $app->page('test');

		$this->assertSame('English Title', $page->content('en')->title()->value());
		$this->assertSame('German Title', $page->content('de')->title()->value());

		// delete the first language to check file removal
		$app->impersonate('kirby', function () use ($app) {
			$app->language('de')->delete();
		});

		// the .de file should now be gone
		$this->assertFileDoesNotExist($page->root() . '/test.de.txt');

		// the .en file should still be there
		$this->assertFileExists($page->root() . '/test.en.txt');

		// delete the last language to switch to single-language mode
		$app->impersonate('kirby', function () use ($app) {
			$app->language('en')->delete();
		});

		// the .en file should now be gone
		$this->assertFileDoesNotExist($page->root() . '/test.en.txt');

		// … and should be replaced by the content file without language code
		$this->assertFileExists($page->root() . '/test.txt');

		// meanwhile, Kirby should have been switched to single language mode
		$this->assertCount(0, $app->languages());
		$this->assertFalse($app->multilang());

		// the page should still load the english content
		$this->assertSame('English Title', $page->content()->title()->value());
		$this->assertSame('English Title', Data::read($page->root() . '/test.txt')['title']);
	}

}
