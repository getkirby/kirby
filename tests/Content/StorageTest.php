<?php

namespace Kirby\Content;

use Kirby\Cms\File;
use Kirby\Cms\Language;
use Kirby\Cms\Page;
use Kirby\Data\Data;
use Kirby\Filesystem\Dir;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Storage::class)]
class StorageTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Content.Storage';

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
		$handler->create(VersionId::latest(), $this->app->language('en'), []);
		$handler->create(VersionId::latest(), $this->app->language('de'), []);

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
		$handler->create(VersionId::latest(), Language::single(), []);
		$handler->create(VersionId::changes(), Language::single(), []);

		// count again
		$versions = iterator_to_array($handler->all(), false);

		// article.txt
		// _changes/article.txt
		$this->assertCount(2, $versions);
	}

	public function testAllMultiLanguageForPage()
	{
		$this->setUpMultiLanguage();

		$handler = new TestStorage(
			new Page(['slug' => 'test', 'isDraft' => false])
		);

		$versions = iterator_to_array($handler->all(), false);

		$this->assertCount(0, $versions);

		// create all possible versions
		$handler->create(VersionId::latest(), $this->app->language('en'), []);
		$handler->create(VersionId::latest(), $this->app->language('de'), []);

		$handler->create(VersionId::changes(), $this->app->language('en'), []);
		$handler->create(VersionId::changes(), $this->app->language('de'), []);

		// count again
		$versions = iterator_to_array($handler->all(), false);

		// A page that's not in draft mode can have Latest and changes versions
		// and thus should have changes and Latest for every language
		$this->assertCount(4, $versions);
	}

	public function testAllSingleLanguageForPage()
	{
		$this->setUpSingleLanguage();

		$handler = new TestStorage(
			new Page(['slug' => 'test', 'isDraft' => false])
		);

		$versions = iterator_to_array($handler->all(), false);

		$this->assertCount(0, $versions);

		// create all possible versions
		$handler->create(VersionId::latest(), Language::single(), []);
		$handler->create(VersionId::changes(), Language::single(), []);

		// count again
		$versions = iterator_to_array($handler->all(), false);

		// A page that's not in draft mode can have Latest and changes versions
		$this->assertCount(2, $versions);
	}

	public function testCopyMultiLanguage()
	{
		$this->setUpMultiLanguage();

		$handler = new TestStorage(model: $this->model);

		$en = $this->app->language('en');
		$de = $this->app->language('de');

		$handler->create(VersionId::latest(), $en, []);

		$this->assertTrue($handler->exists(VersionId::latest(), $en));
		$this->assertFalse($handler->exists(VersionId::latest(), $de));

		$handler->copy(
			VersionId::latest(),
			$en,
			toLanguage: $de
		);

		$this->assertTrue($handler->exists(VersionId::latest(), $en));
		$this->assertTrue($handler->exists(VersionId::latest(), $de));
	}

	public function testCopySingleLanguage()
	{
		$this->setUpSingleLanguage();

		$handler = new TestStorage(model: $this->model);

		$handler->create(VersionId::latest(), Language::single(), []);

		$this->assertTrue($handler->exists(VersionId::latest(), Language::single()));
		$this->assertFalse($handler->exists(VersionId::changes(), Language::single()));

		$handler->copy(
			VersionId::latest(),
			Language::single(),
			VersionId::changes()
		);

		$this->assertTrue($handler->exists(VersionId::latest(), Language::single()));
		$this->assertTrue($handler->exists(VersionId::changes(), Language::single()));
	}

	public function testCopytoAnotherStorage()
	{
		$this->setUpSingleLanguage();

		$handler1 = new TestStorage(model: $this->model);
		$handler2 = new TestStorage(model: $this->model);

		$handler1->create(VersionId::latest(), Language::single(), []);

		$this->assertTrue($handler1->exists(VersionId::latest(), Language::single()));
		$this->assertFalse($handler2->exists(VersionId::latest(), Language::single()));

		$handler1->copy(
			VersionId::latest(),
			Language::single(),
			toStorage: $handler2
		);

		$this->assertTrue($handler1->exists(VersionId::latest(), Language::single()));
		$this->assertTrue($handler2->exists(VersionId::latest(), Language::single()));
	}

	public function testCopyAll()
	{
		$this->setUpSingleLanguage();

		$handler1 = new TestStorage(model: $this->model);
		$handler2 = new TestStorage(model: $this->model);

		$handler1->create(VersionId::latest(), Language::single(), []);
		$handler1->create(VersionId::changes(), Language::single(), []);

		$this->assertTrue($handler1->exists(VersionId::latest(), Language::single()));
		$this->assertTrue($handler1->exists(VersionId::changes(), Language::single()));
		$this->assertFalse($handler2->exists(VersionId::latest(), Language::single()));
		$this->assertFalse($handler2->exists(VersionId::changes(), Language::single()));

		$handler1->copyAll(to: $handler2);

		$this->assertTrue($handler1->exists(VersionId::latest(), Language::single()));
		$this->assertTrue($handler1->exists(VersionId::changes(), Language::single()));
		$this->assertTrue($handler2->exists(VersionId::latest(), Language::single()));
		$this->assertTrue($handler2->exists(VersionId::changes(), Language::single()));
	}

	public function testDeleteLanguageMultiLanguage()
	{
		$this->setUpMultiLanguage();

		$handler = new TestStorage(model: $this->model);

		// create two versions for the German language
		$handler->create(VersionId::latest(), $this->app->language('de'), []);
		$handler->create(VersionId::changes(), $this->app->language('de'), []);

		$this->assertTrue($handler->exists(VersionId::latest(), $this->app->language('de')));
		$this->assertTrue($handler->exists(VersionId::changes(), $this->app->language('de')));

		$handler->deleteLanguage($this->app->language('de'));

		$this->assertFalse($handler->exists(VersionId::latest(), $this->app->language('de')));
		$this->assertFalse($handler->exists(VersionId::changes(), $this->app->language('de')));
	}

	public function testDeleteLanguageSingleLanguage()
	{
		$this->setUpSingleLanguage();

		// Use the plain text handler, as the abstract class and the test handler do not
		// implement the necessary methods to test this.
		$handler = new TestStorage(model: $this->model);

		$language = Language::single();

		// create two versions
		$handler->create(VersionId::latest(), $language, []);
		$handler->create(VersionId::changes(), $language, []);

		$this->assertTrue($handler->exists(VersionId::latest(), $language));
		$this->assertTrue($handler->exists(VersionId::changes(), $language));

		$handler->deleteLanguage($language);

		$this->assertFalse($handler->exists(VersionId::latest(), $language));
		$this->assertFalse($handler->exists(VersionId::changes(), $language));
	}

	public function testFrom()
	{
		$this->setUpMultiLanguage();

		$handlerA = new PlainTextStorage(model: $this->model);

		$versionLatest  = VersionId::latest();
		$versionChanges = VersionId::changes();

		$en = $this->app->language('en');
		$de = $this->app->language('de');

		// create all possible versions
		$handlerA->create($versionLatest, $en, $LatestEN = [
			'title' => 'Latest EN'
		]);

		$handlerA->create($versionLatest, $de, $LatestDE = [
			'title' => 'Latest DE'
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

		$this->assertSame($LatestEN, $handlerB->read($versionLatest, $en));
		$this->assertSame($LatestDE, $handlerB->read($versionLatest, $de));

		$this->assertSame($changesEN, $handlerB->read($versionChanges, $en));
		$this->assertSame($changesDE, $handlerB->read($versionChanges, $de));
	}

	public function testIsSameStorageLocation()
	{
		$this->setUpSingleLanguage();

		$handler = new TestStorage(model: $this->model);

		$this->assertTrue($handler->isSameStorageLocation(
			VersionId::latest(),
			Language::single(),
			VersionId::latest(),
			Language::single()
		));
	}

	public function testIsSameStorageLocationWithDifferentVersionIds()
	{
		$this->setUpSingleLanguage();

		$handler = new TestStorage(model: $this->model);

		$this->assertFalse($handler->isSameStorageLocation(
			VersionId::latest(),
			Language::single(),
			VersionId::changes(),
			Language::single()
		));
	}

	public function testIsSameStorageLocationWithDifferentLanguages()
	{
		$this->setUpMultiLanguage();

		$handler = new TestStorage(model: $this->model);

		$this->assertFalse($handler->isSameStorageLocation(
			VersionId::latest(),
			Language::ensure('en'),
			VersionId::latest(),
			Language::ensure('de')
		));
	}

	public function testIsSameStorageLocationWithDifferentStorageInstances()
	{
		$this->setUpSingleLanguage();

		$handler1 = new TestStorage(model: $this->model);
		$handler2 = new TestStorage(model: $this->model);

		$this->assertFalse($handler1->isSameStorageLocation(
			VersionId::latest(),
			Language::single(),
			VersionId::latest(),
			Language::single(),
			$handler2
		));
	}

	public function testModel()
	{
		$this->setUpSingleLanguage();

		$handler = new TestStorage(model: $this->model);

		$this->assertSame($this->model, $handler->model());
	}

	public function testMoveMultiLanguage()
	{
		$this->setUpMultiLanguage();

		$handler = new TestStorage(model: $this->model);

		$en = $this->app->language('en');
		$de = $this->app->language('de');

		$handler->create(VersionId::latest(), $en, []);

		$this->assertTrue($handler->exists(VersionId::latest(), $en));
		$this->assertFalse($handler->exists(VersionId::latest(), $de));

		$handler->move(
			VersionId::latest(),
			$en,
			toLanguage: $de
		);

		$this->assertFalse($handler->exists(VersionId::latest(), $en));
		$this->assertTrue($handler->exists(VersionId::latest(), $de));
	}

	public function testMoveSingleLanguage()
	{
		$this->setUpSingleLanguage();

		$handler = new TestStorage(model: $this->model);


		$handler->create(VersionId::latest(), Language::single(), []);

		$this->assertTrue($handler->exists(VersionId::latest(), Language::single()));
		$this->assertFalse($handler->exists(VersionId::changes(), Language::single()));

		$handler->move(
			VersionId::latest(),
			Language::single(),
			VersionId::changes()
		);

		$this->assertFalse($handler->exists(VersionId::latest(), Language::single()));
		$this->assertTrue($handler->exists(VersionId::changes(), Language::single()));
	}

	public function testMoveToTheSameStorageLocation()
	{
		$this->setUpSingleLanguage();

		$handler = new TestStorage(model: $this->model);

		$content   = ['title' => 'Test'];
		$versionId = VersionId::latest();
		$language  = Language::single();

		// create some content to move
		$handler->create($versionId, $language, $content);

		$this->assertTrue($handler->exists($versionId, $language));
		$this->assertSame($content, $handler->read($versionId, $language));

		$handler->move(
			$versionId,
			$language,
			$versionId,
			$language,
			$handler
		);

		$this->assertTrue($handler->exists($versionId, $language));
		$this->assertSame($content, $handler->read($versionId, $language), 'The content should still be the same');
	}

	public function testMoveToAnotherStorage()
	{
		$this->setUpSingleLanguage();

		$handler1 = new TestStorage(model: $this->model);
		$handler2 = new TestStorage(model: $this->model);

		$handler1->create(VersionId::latest(), Language::single(), []);

		$this->assertTrue($handler1->exists(VersionId::latest(), Language::single()));
		$this->assertFalse($handler2->exists(VersionId::latest(), Language::single()));

		$handler1->move(
			VersionId::latest(),
			Language::single(),
			toStorage: $handler2
		);

		$this->assertFalse($handler1->exists(VersionId::latest(), Language::single()));
		$this->assertTrue($handler2->exists(VersionId::latest(), Language::single()));
	}

	public function testMoveAll()
	{
		$this->setUpSingleLanguage();

		$handler1 = new TestStorage(model: $this->model);
		$handler2 = new TestStorage(model: $this->model);

		$handler1->create(VersionId::latest(), Language::single(), []);
		$handler1->create(VersionId::changes(), Language::single(), []);

		$this->assertTrue($handler1->exists(VersionId::latest(), Language::single()));
		$this->assertTrue($handler1->exists(VersionId::changes(), Language::single()));
		$this->assertFalse($handler2->exists(VersionId::latest(), Language::single()));
		$this->assertFalse($handler2->exists(VersionId::changes(), Language::single()));

		$handler1->moveAll(to: $handler2);

		$this->assertFalse($handler1->exists(VersionId::latest(), Language::single()));
		$this->assertFalse($handler1->exists(VersionId::changes(), Language::single()));
		$this->assertTrue($handler2->exists(VersionId::latest(), Language::single()));
		$this->assertTrue($handler2->exists(VersionId::changes(), Language::single()));
	}

	public function testMoveSingleLanguageToMultiLanguage()
	{
		$this->setUpMultiLanguage();

		// Use the plain text handler, as it offers the most
		// realistic, testable results for this test
		$handler = new PlainTextStorage(model: $this->model);

		Data::write($fileLatest = $this->model->root() . '/article.txt', []);
		Data::write($fileChanges   = $this->model->root() . '/_changes/article.txt', []);

		$this->assertFileExists($fileLatest);
		$this->assertFileExists($fileChanges);

		$handler->moveLanguage(
			Language::single(),
			$this->app->language('en')
		);

		$this->assertFileDoesNotExist($fileLatest);
		$this->assertFileDoesNotExist($fileChanges);

		$this->assertFileExists($this->model->root() . '/article.en.txt');
		$this->assertFileExists($this->model->root() . '/_changes/article.en.txt');
	}

	public function testMoveMultiLanguageToSingleLanguage()
	{
		$this->setUpMultiLanguage();

		// Use the plain text handler, as it offers the most
		// realistic, testable results for this test
		$handler = new PlainTextStorage(model: $this->model);


		Data::write($fileLatest = $this->model->root() . '/article.en.txt', []);
		Data::write($fileChanges   = $this->model->root() . '/_changes/article.en.txt', []);

		$this->assertFileExists($fileLatest);
		$this->assertFileExists($fileChanges);

		$handler->moveLanguage(
			$this->app->language('en'),
			Language::single(),
		);

		$this->assertFileDoesNotExist($fileLatest);
		$this->assertFileDoesNotExist($fileChanges);

		$this->assertFileExists($this->model->root() . '/article.txt');
		$this->assertFileExists($this->model->root() . '/_changes/article.txt');
	}

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

	public function testReplaceStringsWithNullValues()
	{
		$this->setUpSingleLanguage();

		$handler = new TestStorage(model: $this->model);

		$handler->create(VersionId::latest(), Language::single(), [
			'foo' => null,
			'bar' => 'two steps'
		]);

		$handler->replaceStrings(VersionId::latest(), Language::single(), [
			'step' => 'jump'
		]);

		$this->assertSame([
			'foo' => null,
			'bar' => 'two jumps'
		], $handler->read(VersionId::latest(), Language::single()));
	}

	public function testTouchLanguageMultiLanguage()
	{
		$this->setUpMultiLanguage();

		// Use the plain text handler, as the abstract class and the test handler do not
		// implement the necessary methods to test this.
		$handler = new PlainTextStorage(model: $this->model);

		Dir::make($this->model->root());
		Dir::make($this->model->root() . '/_changes');

		touch($fileLatest = $this->model->root() . '/article.de.txt', 123456);
		touch($fileChanges   = $this->model->root() . '/_changes/article.de.txt', 123456);

		$this->assertSame(123456, filemtime($fileLatest));
		$this->assertSame(123456, filemtime($fileChanges));

		$minTime = time();

		$handler->touchLanguage($this->app->language('de'));

		clearstatcache();

		$this->assertGreaterThanOrEqual($minTime, filemtime($fileChanges));
		$this->assertGreaterThanOrEqual($minTime, filemtime($fileLatest));
	}

}
