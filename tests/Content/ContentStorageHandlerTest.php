<?php

namespace Kirby\Content;

use Kirby\Cms\File;
use Kirby\Cms\Language;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use Kirby\Data\Data;
use Kirby\Filesystem\Dir;

/**
 * @coversDefaultClass Kirby\Content\ContentStorageHandler
 * @covers ::__construct
 */
class ContentStorageHandlerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Content.ContentStorageHandler';

	/**
	 * @covers ::all
	 */
	public function testAllMultiLanguageForFile()
	{
		$this->setUpMultiLanguage();

		$handler = new TestContentStorageHandler(
			new File([
				'filename' => 'test.jpg',
				'parent'   => new Page(['slug' => 'test'])
			])
		);

		$versions = iterator_to_array($handler->all(), false);

		$this->assertCount(0, $versions);

		// create all possible versions
		$handler->create(VersionId::published(), $this->app->language('en'), []);
		$handler->create(VersionId::published(), $this->app->language('de'), []);

		$handler->create(VersionId::changes(), $this->app->language('en'), []);
		$handler->create(VersionId::changes(), $this->app->language('de'), []);

		// count again
		$versions = iterator_to_array($handler->all(), false);

		// article.en.txt
		// article.de.txt
		// _changes/article.en.txt
		// _changes/article.de.txt
		$this->assertCount(4, $versions);
	}

	/**
	 * @covers ::all
	 */
	public function testAllSingleLanguageForFile()
	{
		$this->setUpSingleLanguage();

		$handler = new TestContentStorageHandler(
			new File([
				'filename' => 'test.jpg',
				'parent'   => new Page(['slug' => 'test'])
			])
		);

		$versions = iterator_to_array($handler->all(), false);

		$this->assertCount(0, $versions);

		// create all possible versions
		$handler->create(VersionId::published(), Language::single(), []);
		$handler->create(VersionId::changes(), Language::single(), []);

		// count again
		$versions = iterator_to_array($handler->all(), false);

		// article.txt
		// _changes/article.txt
		$this->assertCount(2, $versions);
	}

	/**
	 * @covers ::all
	 */
	public function testAllMultiLanguageForPage()
	{
		$this->setUpMultiLanguage();

		$handler = new TestContentStorageHandler(
			new Page(['slug' => 'test', 'isDraft' => false])
		);

		$versions = iterator_to_array($handler->all(), false);

		$this->assertCount(0, $versions);

		// create all possible versions
		$handler->create(VersionId::published(), $this->app->language('en'), []);
		$handler->create(VersionId::published(), $this->app->language('de'), []);

		$handler->create(VersionId::changes(), $this->app->language('en'), []);
		$handler->create(VersionId::changes(), $this->app->language('de'), []);

		// count again
		$versions = iterator_to_array($handler->all(), false);

		// A page that's not in draft mode can have published and changes versions
		// and thus should have changes and published for every language
		$this->assertCount(4, $versions);
	}

	/**
	 * @covers ::all
	 */
	public function testAllMultiLanguageForPageDraft()
	{
		$this->setUpMultiLanguage();

		$handler = new TestContentStorageHandler(
			new Page(['slug' => 'test', 'isDraft' => true])
		);

		$versions = iterator_to_array($handler->all(), false);

		$this->assertCount(0, $versions);

		$handler->create(VersionId::published(), $this->app->language('en'), []);
		$handler->create(VersionId::published(), $this->app->language('de'), []);

		$handler->create(VersionId::changes(), $this->app->language('en'), []);
		$handler->create(VersionId::changes(), $this->app->language('de'), []);

		// count again
		$versions = iterator_to_array($handler->all(), false);

		// A draft page has only changes and thus should only have
		// a changes for every language, but no published versions
		$this->assertCount(2, $versions);
	}

	/**
	 * @covers ::all
	 */
	public function testAllSingleLanguageForPage()
	{
		$this->setUpSingleLanguage();

		$handler = new TestContentStorageHandler(
			new Page(['slug' => 'test', 'isDraft' => false])
		);

		$versions = iterator_to_array($handler->all(), false);

		$this->assertCount(0, $versions);

		// create all possible versions
		$handler->create(VersionId::published(), Language::single(), []);
		$handler->create(VersionId::changes(), Language::single(), []);

		// count again
		$versions = iterator_to_array($handler->all(), false);

		// A page that's not in draft mode can have published and changes versions
		$this->assertCount(2, $versions);
	}

	/**
	 * @covers ::all
	 */
	public function testAllSingleLanguageForPageDraft()
	{
		$this->setUpSingleLanguage();

		$handler = new TestContentStorageHandler(
			new Page(['slug' => 'test', 'isDraft' => true])
		);

		$versions = iterator_to_array($handler->all(), false);

		$this->assertCount(0, $versions);

		// create all possible versions
		$handler->create(VersionId::published(), Language::single(), []);
		$handler->create(VersionId::changes(), Language::single(), []);

		// count again
		$versions = iterator_to_array($handler->all(), false);

		// A draft page has only changes and thus should only have
		// a single version in a single language installation
		$this->assertCount(1, $versions);
	}

	/**
	 * @covers ::deleteLanguage
	 */
	public function testDeleteLanguageMultiLanguage()
	{
		$this->setUpMultiLanguage();

		$handler = new TestContentStorageHandler(
			model: $this->model
		);

		// create two versions for the German language
		$handler->create(VersionId::published(), $this->app->language('de'), []);
		$handler->create(VersionId::changes(), $this->app->language('de'), []);

		$this->assertTrue($handler->exists(VersionId::published(), $this->app->language('de')));
		$this->assertTrue($handler->exists(VersionId::changes(), $this->app->language('de')));

		$handler->deleteLanguage($this->app->language('de'));

		$this->assertFalse($handler->exists(VersionId::published(), $this->app->language('de')));
		$this->assertFalse($handler->exists(VersionId::changes(), $this->app->language('de')));
	}

	/**
	 * @covers ::deleteLanguage
	 */
	public function testDeleteLanguageSingleLanguage()
	{
		$this->setUpSingleLanguage();

		// Use the plain text handler, as the abstract class and the test handler do not
		// implement the necessary methods to test this.
		$handler = new TestContentStorageHandler(
			model: $this->model
		);

		$language = Language::single();

		// create two versions
		$handler->create(VersionId::published(), $language, []);
		$handler->create(VersionId::changes(), $language, []);

		$this->assertTrue($handler->exists(VersionId::published(), $language));
		$this->assertTrue($handler->exists(VersionId::changes(), $language));

		$handler->deleteLanguage($language);

		$this->assertFalse($handler->exists(VersionId::published(), $language));
		$this->assertFalse($handler->exists(VersionId::changes(), $language));
	}

	/**
	 * @covers ::dynamicVersions
	 */
	public function testDynamicVersionsForFile()
	{
		$handler = new TestContentStorageHandler(
			new File([
				'filename' => 'test.jpg',
				'parent'   => new Page(['slug' => 'test'])
			])
		);

		$versions = $handler->dynamicVersions();

		$this->assertCount(2, $versions);
		$this->assertSame(VersionId::CHANGES, $versions[0]->value());
		$this->assertSame(VersionId::PUBLISHED, $versions[1]->value());
	}

	/**
	 * @covers ::dynamicVersions
	 */
	public function testDynamicVersionsForPage()
	{
		$handler = new TestContentStorageHandler(
			new Page(['slug' => 'test', 'isDraft' => false])
		);

		$versions = $handler->dynamicVersions();

		$this->assertCount(2, $versions);
		$this->assertSame(VersionId::CHANGES, $versions[0]->value());
		$this->assertSame(VersionId::PUBLISHED, $versions[1]->value());
	}

	/**
	 * @covers ::dynamicVersions
	 */
	public function testDynamicVersionsForPageDraft()
	{
		$handler = new TestContentStorageHandler(
			new Page(['slug' => 'test', 'isDraft' => true])
		);

		$versions = $handler->dynamicVersions();

		$this->assertCount(1, $versions);
		$this->assertSame(VersionId::CHANGES, $versions[0]->value());
	}

	/**
	 * @covers ::dynamicVersions
	 */
	public function testDynamicVersionsForSite()
	{
		$handler = new TestContentStorageHandler(
			new Site()
		);

		$versions = $handler->dynamicVersions();

		$this->assertCount(2, $versions);
		$this->assertSame(VersionId::CHANGES, $versions[0]->value());
		$this->assertSame(VersionId::PUBLISHED, $versions[1]->value());
	}

	/**
	 * @covers ::dynamicVersions
	 */
	public function testDynamicVersionsForUser()
	{
		$handler = new TestContentStorageHandler(
			new User(['email' => 'test@getkirby.com'])
		);

		$versions = $handler->dynamicVersions();

		$this->assertCount(2, $versions);
		$this->assertSame(VersionId::CHANGES, $versions[0]->value());
		$this->assertSame(VersionId::PUBLISHED, $versions[1]->value());
	}

	/**
	 * @covers ::model
	 */
	public function testModel()
	{
		$this->setUpSingleLanguage();

		$handler = new TestContentStorageHandler(
			model: $this->model
		);

		$this->assertSame($this->model, $handler->model());
	}

	/**
	 * @covers ::move
	 */
	public function testMoveMultiLanguage()
	{
		$this->setUpMultiLanguage();

		$handler = new TestContentStorageHandler(
			model: $this->model
		);

		$en = $this->app->language('en');
		$de = $this->app->language('de');

		$handler->create(VersionId::published(), $en, []);

		$this->assertTrue($handler->exists(VersionId::published(), $en));
		$this->assertFalse($handler->exists(VersionId::published(), $de));

		$handler->move(
			VersionId::published(),
			$en,
			VersionId::published(),
			$de
		);

		$this->assertFalse($handler->exists(VersionId::published(), $en));
		$this->assertTrue($handler->exists(VersionId::published(), $de));
	}

	public function testMoveSingleLanguage()
	{
		$this->setUpSingleLanguage();

		$handler = new TestContentStorageHandler(
			model: $this->model
		);

		$handler->create(VersionId::published(), Language::single(), []);

		$this->assertTrue($handler->exists(VersionId::published(), Language::single()));
		$this->assertFalse($handler->exists(VersionId::changes(), Language::single()));

		$handler->move(
			VersionId::published(),
			Language::single(),
			VersionId::changes(),
			Language::single()
		);

		$this->assertFalse($handler->exists(VersionId::published(), Language::single()));
		$this->assertTrue($handler->exists(VersionId::changes(), Language::single()));
	}

	/**
	 * @covers ::moveLanguage
	 */
	public function testMoveSingleLanguageToMultiLanguage()
	{
		$this->setUpMultiLanguage();

		// Use the plain text handler, as it offers the most
		// realistic, testable results for this test
		$handler = new PlainTextContentStorageHandler(
			model: $this->model
		);

		Data::write($filePublished = $this->model->root() . '/article.txt', []);
		Data::write($fileChanges   = $this->model->root() . '/_changes/article.txt', []);

		$this->assertFileExists($filePublished);
		$this->assertFileExists($fileChanges);

		$handler->moveLanguage(
			Language::single(),
			$this->app->language('en')
		);

		$this->assertFileDoesNotExist($filePublished);
		$this->assertFileDoesNotExist($fileChanges);

		$this->assertFileExists($this->model->root() . '/article.en.txt');
		$this->assertFileExists($this->model->root() . '/_changes/article.en.txt');
	}

	/**
	 * @covers ::moveLanguage
	 */
	public function testMoveMultiLanguageToSingleLanguage()
	{
		$this->setUpMultiLanguage();

		// Use the plain text handler, as it offers the most
		// realistic, testable results for this test
		$handler = new PlainTextContentStorageHandler(
			model: $this->model
		);

		Data::write($filePublished = $this->model->root() . '/article.en.txt', []);
		Data::write($fileChanges   = $this->model->root() . '/_changes/article.en.txt', []);

		$this->assertFileExists($filePublished);
		$this->assertFileExists($fileChanges);

		$handler->moveLanguage(
			$this->app->language('en'),
			Language::single(),
		);

		$this->assertFileDoesNotExist($filePublished);
		$this->assertFileDoesNotExist($fileChanges);

		$this->assertFileExists($this->model->root() . '/article.txt');
		$this->assertFileExists($this->model->root() . '/_changes/article.txt');
	}

	/**
	 * @covers ::touchLanguage
	 */
	public function testTouchLanguageMultiLanguage()
	{
		$this->setUpMultiLanguage();

		// Use the plain text handler, as the abstract class and the test handler do not
		// implement the necessary methods to test this.
		$handler = new PlainTextContentStorageHandler(
			model: $this->model
		);

		Dir::make($this->model->root());
		Dir::make($this->model->root() . '/_changes');

		touch($filePublished = $this->model->root() . '/article.de.txt', 123456);
		touch($fileChanges   = $this->model->root() . '/_changes/article.de.txt', 123456);

		$this->assertSame(123456, filemtime($filePublished));
		$this->assertSame(123456, filemtime($fileChanges));

		$minTime = time();

		$handler->touchLanguage($this->app->language('de'));

		clearstatcache();

		$this->assertGreaterThanOrEqual($minTime, filemtime($fileChanges));
		$this->assertGreaterThanOrEqual($minTime, filemtime($filePublished));
	}

}
