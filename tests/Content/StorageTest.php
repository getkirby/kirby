<?php

namespace Kirby\Content;

use Kirby\Cms\File;
use Kirby\Cms\Language;
use Kirby\Cms\Page;
use Kirby\Data\Data;
use Kirby\Filesystem\Dir;

/**
 * @coversDefaultClass Kirby\Content\Storage
 * @covers ::__construct
 */
class StorageTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Content.Storage';

	/**
	 * @covers ::all
	 */
	public function testAllMultiLanguageForFile()
	{
		$this->setUpMultiLanguage();

		$handler = new TestStorage(
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

		$handler = new TestStorage(
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

		$handler = new TestStorage(
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
	public function testAllSingleLanguageForPage()
	{
		$this->setUpSingleLanguage();

		$handler = new TestStorage(
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
	 * @covers ::copy
	 */
	public function testCopyMultiLanguage()
	{
		$this->setUpMultiLanguage();

		$handler = new TestStorage(model: $this->model);

		$en = $this->app->language('en');
		$de = $this->app->language('de');

		$handler->create(VersionId::published(), $en, []);

		$this->assertTrue($handler->exists(VersionId::published(), $en));
		$this->assertFalse($handler->exists(VersionId::published(), $de));

		$handler->copy(
			VersionId::published(),
			$en,
			toLanguage: $de
		);

		$this->assertTrue($handler->exists(VersionId::published(), $en));
		$this->assertTrue($handler->exists(VersionId::published(), $de));
	}

	/**
	 * @covers ::copy
	 */
	public function testCopySingleLanguage()
	{
		$this->setUpSingleLanguage();

		$handler = new TestStorage(model: $this->model);

		$handler->create(VersionId::published(), Language::single(), []);

		$this->assertTrue($handler->exists(VersionId::published(), Language::single()));
		$this->assertFalse($handler->exists(VersionId::changes(), Language::single()));

		$handler->copy(
			VersionId::published(),
			Language::single(),
			VersionId::changes()
		);

		$this->assertTrue($handler->exists(VersionId::published(), Language::single()));
		$this->assertTrue($handler->exists(VersionId::changes(), Language::single()));
	}

	/**
	 * @covers ::copy
	 */
	public function testCopytoAnotherStorage()
	{
		$this->setUpSingleLanguage();

		$handler1 = new TestStorage(model: $this->model);
		$handler2 = new TestStorage(model: $this->model);

		$handler1->create(VersionId::published(), Language::single(), []);

		$this->assertTrue($handler1->exists(VersionId::published(), Language::single()));
		$this->assertFalse($handler2->exists(VersionId::published(), Language::single()));

		$handler1->copy(
			VersionId::published(),
			Language::single(),
			toStorage: $handler2
		);

		$this->assertTrue($handler1->exists(VersionId::published(), Language::single()));
		$this->assertTrue($handler2->exists(VersionId::published(), Language::single()));
	}

	/**
	 * @covers ::copyAll
	 */
	public function testCopyAll()
	{
		$this->setUpSingleLanguage();

		$handler1 = new TestStorage(model: $this->model);
		$handler2 = new TestStorage(model: $this->model);

		$handler1->create(VersionId::published(), Language::single(), []);
		$handler1->create(VersionId::changes(), Language::single(), []);

		$this->assertTrue($handler1->exists(VersionId::published(), Language::single()));
		$this->assertTrue($handler1->exists(VersionId::changes(), Language::single()));
		$this->assertFalse($handler2->exists(VersionId::published(), Language::single()));
		$this->assertFalse($handler2->exists(VersionId::changes(), Language::single()));

		$handler1->copyAll(to: $handler2);

		$this->assertTrue($handler1->exists(VersionId::published(), Language::single()));
		$this->assertTrue($handler1->exists(VersionId::changes(), Language::single()));
		$this->assertTrue($handler2->exists(VersionId::published(), Language::single()));
		$this->assertTrue($handler2->exists(VersionId::changes(), Language::single()));
	}

	/**
	 * @covers ::deleteLanguage
	 */
	public function testDeleteLanguageMultiLanguage()
	{
		$this->setUpMultiLanguage();

		$handler = new TestStorage(model: $this->model);

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
		$handler = new TestStorage(model: $this->model);

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
	 * @covers ::from
	 */
	public function testFrom()
	{
		$this->setUpMultiLanguage();

		$handlerA = new PlainTextStorage(model: $this->model);

		$versionPublished = VersionId::published();
		$versionChanges   = VersionId::changes();

		$en = $this->app->language('en');
		$de = $this->app->language('de');

		// create all possible versions
		$handlerA->create($versionPublished, $en, $publishedEN = [
			'title' => 'Published EN'
		]);

		$handlerA->create($versionPublished, $de, $publishedDE = [
			'title' => 'Published DE'
		]);

		$handlerA->create($versionChanges, $en, $changesEN = [
			'title' => 'Changes EN'
		]);

		$handlerA->create($versionChanges, $de, $changesDE = [
			'title' => 'Changes DE'
		]);

		// create a new handler with all the versions from the first one
		$handlerB = TestStorage::from($handlerA);

		$this->assertNotSame($handlerA, $handlerB);

		$this->assertSame($publishedEN, $handlerB->read($versionPublished, $en));
		$this->assertSame($publishedDE, $handlerB->read($versionPublished, $de));

		$this->assertSame($changesEN, $handlerB->read($versionChanges, $en));
		$this->assertSame($changesDE, $handlerB->read($versionChanges, $de));
	}

	/**
	 * @covers ::model
	 */
	public function testModel()
	{
		$this->setUpSingleLanguage();

		$handler = new TestStorage(model: $this->model);

		$this->assertSame($this->model, $handler->model());
	}

	/**
	 * @covers ::move
	 */
	public function testMoveMultiLanguage()
	{
		$this->setUpMultiLanguage();

		$handler = new TestStorage(model: $this->model);

		$en = $this->app->language('en');
		$de = $this->app->language('de');

		$handler->create(VersionId::published(), $en, []);

		$this->assertTrue($handler->exists(VersionId::published(), $en));
		$this->assertFalse($handler->exists(VersionId::published(), $de));

		$handler->move(
			VersionId::published(),
			$en,
			toLanguage: $de
		);

		$this->assertFalse($handler->exists(VersionId::published(), $en));
		$this->assertTrue($handler->exists(VersionId::published(), $de));
	}

	/**
	 * @covers ::move
	 */
	public function testMoveSingleLanguage()
	{
		$this->setUpSingleLanguage();

		$handler = new TestStorage(model: $this->model);


		$handler->create(VersionId::published(), Language::single(), []);

		$this->assertTrue($handler->exists(VersionId::published(), Language::single()));
		$this->assertFalse($handler->exists(VersionId::changes(), Language::single()));

		$handler->move(
			VersionId::published(),
			Language::single(),
			VersionId::changes()
		);

		$this->assertFalse($handler->exists(VersionId::published(), Language::single()));
		$this->assertTrue($handler->exists(VersionId::changes(), Language::single()));
	}

	/**
	 * @covers ::move
	 */
	public function testMovetoAnotherStorage()
	{
		$this->setUpSingleLanguage();

		$handler1 = new TestStorage(model: $this->model);
		$handler2 = new TestStorage(model: $this->model);

		$handler1->create(VersionId::published(), Language::single(), []);

		$this->assertTrue($handler1->exists(VersionId::published(), Language::single()));
		$this->assertFalse($handler2->exists(VersionId::published(), Language::single()));

		$handler1->move(
			VersionId::published(),
			Language::single(),
			toStorage: $handler2
		);

		$this->assertFalse($handler1->exists(VersionId::published(), Language::single()));
		$this->assertTrue($handler2->exists(VersionId::published(), Language::single()));
	}

	/**
	 * @covers ::moveAll
	 */
	public function testMoveAll()
	{
		$this->setUpSingleLanguage();

		$handler1 = new TestStorage(model: $this->model);
		$handler2 = new TestStorage(model: $this->model);

		$handler1->create(VersionId::published(), Language::single(), []);
		$handler1->create(VersionId::changes(), Language::single(), []);

		$this->assertTrue($handler1->exists(VersionId::published(), Language::single()));
		$this->assertTrue($handler1->exists(VersionId::changes(), Language::single()));
		$this->assertFalse($handler2->exists(VersionId::published(), Language::single()));
		$this->assertFalse($handler2->exists(VersionId::changes(), Language::single()));

		$handler1->moveAll(to: $handler2);

		$this->assertFalse($handler1->exists(VersionId::published(), Language::single()));
		$this->assertFalse($handler1->exists(VersionId::changes(), Language::single()));
		$this->assertTrue($handler2->exists(VersionId::published(), Language::single()));
		$this->assertTrue($handler2->exists(VersionId::changes(), Language::single()));
	}

	/**
	 * @covers ::moveLanguage
	 */
	public function testMoveSingleLanguageToMultiLanguage()
	{
		$this->setUpMultiLanguage();

		// Use the plain text handler, as it offers the most
		// realistic, testable results for this test
		$handler = new PlainTextStorage(model: $this->model);

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
		$handler = new PlainTextStorage(model: $this->model);


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
	 * @covers ::replaceStrings
	 */
	public function testReplaceStrings()
	{
		$this->setUpMultiLanguage();

		$versionId = VersionId::changes();
		$language  = $this->app->language('en');

		$handler = new TestStorage(model: $this->model);

		$fields = [
			'foo' => 'one step',
			'bar' => 'two steps'
		];

		$handler->create($versionId, $language, $fields);
		$this->assertSame($fields, $handler->read($versionId, $language));

		$handler->replaceStrings($versionId, $language, ['step' => 'jump']);

		$expected = [
			'foo' => 'one jump',
			'bar' => 'two jumps'
		];

		$this->assertSame($expected, $handler->read($versionId, $language));
	}

	/**
	 * @covers ::touchLanguage
	 */
	public function testTouchLanguageMultiLanguage()
	{
		$this->setUpMultiLanguage();

		// Use the plain text handler, as the abstract class and the test handler do not
		// implement the necessary methods to test this.
		$handler = new PlainTextStorage(model: $this->model);

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