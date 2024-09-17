<?php

namespace Kirby\Content;

use Kirby\Data\Data;
use Kirby\Exception\LogicException;
use Kirby\Exception\NotFoundException;

/**
 * @coversDefaultClass Kirby\Content\Version
 * @covers ::__construct
 */
class VersionTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Content.Version';

	/**
	 * @covers ::content
	 */
	public function testContentMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$expected = $this->createContentMultiLanguage();

		$this->assertSame($expected['en']['content']['title'], $version->content('en')->get('title')->value());
		$this->assertSame($expected['en']['content']['title'], $version->content($this->app->language('en'))->get('title')->value());
		$this->assertSame($expected['en']['content']['title'], $version->content()->get('title')->value());
		$this->assertSame($expected['de']['content']['title'], $version->content('de')->get('title')->value());
		$this->assertSame($expected['de']['content']['title'], $version->content($this->app->language('de'))->get('title')->value());
	}

	/**
	 * @covers ::content
	 */
	public function testContentSingleLanguage(): void
	{
		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$expected = $this->createContentSingleLanguage();

		$this->assertSame($expected['content']['title'], $version->content()->get('title')->value());
	}

	/**
	 * @covers ::content
	 */
	public function testContentWithFallback(): void
	{
		$this->setUpMultiLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		// write something to the content file to make sure it
		// can be read from disk for the test.
		Data::write($this->model->root() . '/article.en.txt', $content = [
			'title' => 'Test'
		]);

		$this->assertSame($content, $version->content()->toArray());
		$this->assertSame($content, $version->content('en')->toArray());

		// make sure that the content fallback works
		$this->assertSame($version->content('en')->toArray(), $version->content('de')->toArray());
	}

	/**
	 * @covers ::contentFile
	 */
	public function testContentFileMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$this->assertSame($this->contentFile('en'), $version->contentFile());
		$this->assertSame($this->contentFile('en'), $version->contentFile('en'));
		$this->assertSame($this->contentFile('en'), $version->contentFile($this->app->language('en')));
		$this->assertSame($this->contentFile('de'), $version->contentFile('de'));
		$this->assertSame($this->contentFile('de'), $version->contentFile($this->app->language('de')));
	}

	/**
	 * @covers ::contentFile
	 */
	public function testContentFileSingleLanguage(): void
	{
		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$this->assertSame($this->contentFile(), $version->contentFile());
	}

	/**
	 * @covers ::create
	 */
	public function testCreateMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$this->assertContentFileDoesNotExist('en');
		$this->assertContentFileDoesNotExist('de');

		// with Language argument
		$version->create([
			'title' => 'Test'
		], $this->app->language('en'));

		// with string argument
		$version->create([
			'title' => 'Test'
		], 'de');

		$this->assertContentFileExists('en');
		$this->assertContentFileExists('de');
	}

	/**
	 * @covers ::create
	 */
	public function testCreateSingleLanguage(): void
	{
		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$this->assertContentFileDoesNotExist();

		$version->create([
			'title' => 'Test'
		]);

		$this->assertContentFileExists();
	}

	/**
	 * @covers ::create
	 * @covers ::convertFieldNamesToLowerCase
	 * @covers ::prepareFieldsBeforeWrite
	 */
	public function testCreateWithDirtyFields(): void
	{
		$this->setUpMultiLanguage();

		// add a blueprint with an untranslatable field
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/article' => [
					'fields' => [
						'date' => [
							'type'      => 'date',
							'translate' => false
						]
					]
				]
			]
		]);

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		// primary language
		$version->create([
			'title'    => 'Test',
			'uuid'     => '12345',
			'Subtitle' => 'Subtitle',
			'date'     => '2012-12-12'
		], 'en');

		// secondary language
		$version->create([
			'title'    => 'Test',
			'uuid'     => '12345',
			'Subtitle' => 'Subtitle',
			'date'     => '2012-12-12'
		], 'de');

		// check for lower case field names
		$this->assertArrayHasKey('subtitle', $version->read('en'));
		$this->assertArrayHasKey('subtitle', $version->read('de'));

		// check for removed uuid field in secondary language
		$this->assertArrayHasKey('uuid', $version->read('en'));
		$this->assertArrayNotHasKey('uuid', $version->read('de'));

		// check for untranslatable fields
		$this->assertArrayHasKey('date', $version->read('en'));
		$this->assertArrayNotHasKey('date', $version->read('de'));
	}

	/**
	 * @covers ::delete
	 */
	public function testDeleteMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$this->assertContentFileDoesNotExist('de');
		$this->assertContentFileDoesNotExist('en');

		$this->createContentMultiLanguage();

		$this->assertContentFileExists('en');
		$this->assertContentFileExists('de');

		$version->delete();

		$this->assertContentFileDoesNotExist('en');
		$this->assertContentFileDoesNotExist('de');
	}

	/**
	 * @covers ::delete
	 */
	public function testDeleteSingleLanguage(): void
	{
		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$this->assertContentFileDoesNotExist();

		$this->createContentSingleLanguage();

		$this->assertContentFileExists();

		$version->delete();

		$this->assertContentFileDoesNotExist();
	}

	/**
	 * @covers ::diff
	 */
	public function testDiffMultiLanguage()
	{
		$this->setUpMultiLanguage();

		$a = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$b = new Version(
			model: $this->model,
			id: VersionId::changes()
		);

		$a->create($content = [
			'title'    => 'Title',
			'subtitle' => 'Subtitle',
		], 'en');

		$a->create($content, 'de');

		$b->create($content, 'en');

		$b->create([
			'title'    => 'Title',
			'subtitle' => 'Subtitle (changed)',
		], 'de');

		// no changes in English
		$diffEN = $a->diff(VersionId::changes(), 'en');
		$expectedEN = [];

		$this->assertSame($expectedEN, $diffEN);

		// changed subtitle in German
		$diffDE = $a->diff(VersionId::changes(), 'de');
		$expectedDE = ['subtitle' => 'Subtitle (changed)'];

		$this->assertSame($expectedDE, $diffDE);
	}

	/**
	 * @covers ::diff
	 */
	public function testDiffSingleLanguage()
	{
		$this->setUpSingleLanguage();

		$a = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$b = new Version(
			model: $this->model,
			id: VersionId::changes()
		);

		$a->create([
			'title'    => 'Title',
			'subtitle' => 'Subtitle',
		]);

		$b->create([
			'title'    => 'Title',
			'subtitle' => 'Subtitle (changed)',
		]);

		$diff = $a->diff(VersionId::changes());

		// the result array should contain the changed fields
		// the changed values
		$expected = ['subtitle' => 'Subtitle (changed)'];

		$this->assertSame($expected, $diff);
	}

	/**
	 * @covers ::diff
	 */
	public function testDiffWithoutChanges()
	{
		$this->setUpSingleLanguage();

		$a = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$b = new Version(
			model: $this->model,
			id: VersionId::changes()
		);

		$a->create([
			'title'    => 'Title',
			'subtitle' => 'Subtitle',
		]);

		$b->create([
			'title'    => 'Title',
			'subtitle' => 'Subtitle',
		]);

		$diff = $a->diff(VersionId::changes());

		$this->assertSame([], $diff);
	}

	/**
	 * @covers ::ensure
	 */
	public function testEnsureMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$this->createContentMultiLanguage();

		$this->assertNull($version->ensure('en'));
		$this->assertNull($version->ensure($this->app->language('en')));

		$this->assertNull($version->ensure('de'));
		$this->assertNull($version->ensure($this->app->language('de')));
	}

	/**
	 * @covers ::ensure
	 */
	public function testEnsureSingleLanguage(): void
	{
		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$this->createContentSingleLanguage();

		$this->assertNull($version->ensure());
	}

	/**
	 * @covers ::ensure
	 */
	public function testEnsureWhenMissingMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::changes()
		);

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('Version "changes (de)" does not already exist');

		$version->ensure('de');
	}

	/**
	 * @covers ::ensure
	 */
	public function testEnsureWhenMissingSingleLanguage(): void
	{
		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::changes()
		);

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('Version "changes" does not already exist');

		$version->ensure();
	}

	/**
	 * @covers ::ensure
	 */
	public function testEnsureWithInvalidLanguage(): void
	{
		$this->setUpMultiLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('Invalid language: fr');

		$version->ensure('fr');
	}

	/**
	 * @covers ::exists
	 */
	public function testExistsPublishedMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$this->assertDirectoryExists($this->model->root());

		// the default version + default language exists without
		// content file as long as the page directory exists
		$this->assertTrue($version->exists('en'));
		$this->assertTrue($version->exists($this->app->language('en')));

		// the secondary language only exists as soon as the content
		// file also exists
		$this->assertFalse($version->exists('de'));
		$this->assertFalse($version->exists($this->app->language('de')));

		$this->createContentMultiLanguage();

		$this->assertTrue($version->exists('de'));
		$this->assertTrue($version->exists($this->app->language('de')));
	}

	/**
	 * @covers ::exists
	 */
	public function testExistsPublishedSingleLanguage(): void
	{
		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$this->assertDirectoryExists($this->model->root());

		// the default version exists without content file as long as
		// the page directory exists
		$this->assertTrue($version->exists());
	}

	/**
	 * @covers ::id
	 */
	public function testId(): void
	{
		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model,
			id: $id = VersionId::published()
		);

		$this->assertSame($id, $version->id());
	}

	/**
	 * @covers ::model
	 */
	public function testModel(): void
	{
		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$this->assertSame($this->model, $version->model());
	}

	/**
	 * @covers ::modified
	 */
	public function testModifiedMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		touch($this->contentFile('de'), $modified = 123456);

		$this->assertSame($modified, $version->modified('de'));
		$this->assertSame($modified, $version->modified($this->app->language('de')));
	}

	/**
	 * @covers ::modified
	 */
	public function testModifiedMultiLanguageIfNotExists(): void
	{
		$this->setUpMultiLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$this->assertNull($version->modified('en'));
		$this->assertNull($version->modified($this->app->language('en')));
		$this->assertNull($version->modified('de'));
		$this->assertNull($version->modified($this->app->language('de')));
	}

	/**
	 * @covers ::modified
	 */
	public function testModifiedSingleLanguage(): void
	{
		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		touch($this->contentFile(), $modified = 123456);

		$this->assertSame($modified, $version->modified());
	}

	/**
	 * @covers ::modified
	 */
	public function testModifiedSingleLanguageIfNotExists(): void
	{
		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$this->assertNull($version->modified());
	}

	/**
	 * @covers ::move
	 */
	public function testMoveToLanguage(): void
	{
		$this->setUpMultiLanguage();

		$version = new Version(
			model: $this->model,
			id: $versionId = VersionId::published()
		);

		$this->assertContentFileDoesNotExist('en');
		$this->assertContentFileDoesNotExist('de');

		$fileEN = $this->contentFile('en');
		$fileDE = $this->contentFile('de');

		Data::write($fileEN, $content = [
			'title' => 'Test'
		]);

		$this->assertContentFileExists('en');
		$this->assertContentFileDoesNotExist('de');

		// move with string arguments
		$version->move('en', $versionId, 'de');

		$this->assertContentFileDoesNotExist('en');
		$this->assertContentFileExists('de');

		$this->assertSame($content, Data::read($fileDE));

		// move with Language arguments
		$version->move($this->app->language('de'), $versionId, $this->app->language('en'));

		$this->assertContentFileExists('en');
		$this->assertContentFileDoesNotExist('de');

		$this->assertSame($content, Data::read($fileEN));
	}

	/**
	 * @covers ::move
	 */
	public function testMoveToVersion(): void
	{
		$this->setUpMultiLanguage();

		$versionPublished = new Version(
			model: $this->model,
			id: $versionIdPublished = VersionId::published()
		);

		$versionChanges = new Version(
			model: $this->model,
			id: $versionIdChanges = VersionId::changes()
		);

		$this->assertContentFileDoesNotExist('en', $versionIdPublished);
		$this->assertContentFileDoesNotExist('en', $versionIdChanges);

		$fileENPublished = $this->contentFile('en', $versionIdPublished);
		$fileENChanges   = $this->contentFile('en', $versionIdChanges);

		Data::write($fileENPublished, $content = [
			'title' => 'Test'
		]);

		$this->assertContentFileExists('en', $versionIdPublished);
		$this->assertContentFileDoesNotExist('en', $versionIdChanges);

		// move with string arguments
		$versionPublished->move('en', $versionIdChanges, 'en');

		$this->assertContentFileDoesNotExist('en', $versionIdPublished);
		$this->assertContentFileExists('en', $versionIdChanges);

		$this->assertSame($content, Data::read($fileENChanges));

		// move the version back
		$versionChanges->move('en', $versionIdPublished, 'en');

		$this->assertContentFileDoesNotExist('en', $versionIdChanges);
		$this->assertContentFileExists('en', $versionIdPublished);

		$this->assertSame($content, Data::read($fileENPublished));
	}

	/**
	 * @covers ::publish
	 */
	public function testPublish()
	{
		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::changes()
		);

		Data::write($filePublished = $this->contentFile(null, VersionId::published()), [
			'title' => 'Title published'
		]);

		Data::write($fileChanges = $this->contentFile(null, VersionId::changes()), [
			'title' => 'Title changes'
		]);

		$this->assertFileExists($filePublished);
		$this->assertFileExists($fileChanges);

		$version->publish();

		$this->assertFileDoesNotExist($fileChanges);

		$this->assertSame('Title changes', Data::read($filePublished)['title']);
	}

	/**
	 * @covers ::publish
	 */
	public function testPublishAlreadyPublishedVersion()
	{
		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$this->createContentSingleLanguage();

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('This version is already published');

		$version->publish();
	}

	/**
	 * @covers ::read
	 * @covers ::convertFieldNamesToLowerCase
	 * @covers ::prepareFieldsAfterRead
	 */
	public function testReadMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$expected = $this->createContentMultiLanguage();

		$this->assertSame($expected['en']['content'], $version->read('en'));
		$this->assertSame($expected['en']['content'], $version->read($this->app->language('en')));
		$this->assertSame($expected['de']['content'], $version->read('de'));
		$this->assertSame($expected['de']['content'], $version->read($this->app->language('de')));
	}

	/**
	 * @covers ::read
	 * @covers ::convertFieldNamesToLowerCase
	 * @covers ::prepareFieldsAfterRead
	 */
	public function testReadSingleLanguage(): void
	{
		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$expected = $this->createContentSingleLanguage();

		$this->assertSame($expected['content'], $version->read());
	}

	/**
	 * @covers ::read
	 */
	public function testReadPublishedWithoutContentFile(): void
	{
		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$this->assertFileDoesNotExist($this->contentFile());

		// the page has empty content if there's no default content file
		$this->assertSame([], $version->read());
	}

	/**
	 * @covers ::read
	 * @covers ::convertFieldNamesToLowerCase
	 * @covers ::prepareFieldsAfterRead
	 */
	public function testReadWithDirtyFields(): void
	{
		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		Data::write($this->contentFile(), [
			'Title'    => 'Dirty title',
			'subTitle' => 'Dirty subtitle'
		]);

		// check for lower case field names
		$this->assertArrayHasKey('title', $version->read());
		$this->assertArrayHasKey('subtitle', $version->read());
	}

	/**
	 * @covers ::read
	 * @covers ::convertFieldNamesToLowerCase
	 * @covers ::prepareFieldsAfterRead
	 */
	public function testReadWithInvalidLanguage(): void
	{
		$this->setUpMultiLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('Invalid language: fr');

		$version->read('fr');
	}

	/**
	 * @covers ::replace
	 * @covers ::convertFieldNamesToLowerCase
	 * @covers ::prepareFieldsBeforeWrite
	 */
	public function testReplaceMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$expected = $this->createContentMultiLanguage();

		// with Language argument
		$version->replace([
			'title' => 'Updated Title English'
		], $this->app->language('en'));

		// with string argument
		$version->replace([
			'title' => 'Updated Title Deutsch',
		], 'de');

		$this->assertSame(['title' => 'Updated Title English'], Data::read($expected['en']['file']));
		$this->assertSame(['title' => 'Updated Title Deutsch'], Data::read($expected['de']['file']));
	}

	/**
	 * @covers ::replace
	 * @covers ::convertFieldNamesToLowerCase
	 * @covers ::prepareFieldsBeforeWrite
	 */
	public function testReplaceSingleLanguage(): void
	{
		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$expected = $this->createContentSingleLanguage();

		$version->replace([
			'title' => 'Updated Title'
		]);

		$this->assertSame(['title' => 'Updated Title'], Data::read($expected['file']));
	}

	/**
	 * @covers ::replace
	 * @covers ::convertFieldNamesToLowerCase
	 * @covers ::prepareFieldsBeforeWrite
	 */
	public function testReplaceWithDirtyFields(): void
	{
		$this->setUpMultiLanguage();

		// add a blueprint with an untranslatable field
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/article' => [
					'fields' => [
						'date' => [
							'type'      => 'date',
							'translate' => false
						]
					]
				]
			]
		]);

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$this->createContentMultiLanguage();

		// primary language
		$version->replace([
			'title'    => 'Test',
			'uuid'     => '12345',
			'Subtitle' => 'Subtitle',
			'date'     => '2012-12-12'
		], 'en');

		// secondary language
		$version->replace([
			'title'    => 'Test',
			'uuid'     => '12345',
			'Subtitle' => 'Subtitle',
			'date'     => '2012-12-12'
		], 'de');

		// check for lower case field names
		$this->assertArrayHasKey('subtitle', $version->read('en'));
		$this->assertArrayHasKey('subtitle', $version->read('de'));

		// check for removed uuid field in secondary language
		$this->assertArrayHasKey('uuid', $version->read('en'));
		$this->assertArrayNotHasKey('uuid', $version->read('de'));

		// check for untranslatable fields
		$this->assertArrayHasKey('date', $version->read('en'));
		$this->assertArrayNotHasKey('date', $version->read('de'));
	}

	/**
	 * @covers ::save
	 */
	public function testSaveExistingMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$expected = $this->createContentMultiLanguage();

		// with Language argument
		$version->save([
			'title' => 'Updated Title English'
		], $this->app->language('en'));

		// with string argument
		$version->save([
			'title' => 'Updated Title Deutsch',
		], 'de');

		$this->assertSame('Updated Title English', Data::read($expected['en']['file'])['title']);
		$this->assertSame('Subtitle English', Data::read($expected['en']['file'])['subtitle']);
		$this->assertSame('Updated Title Deutsch', Data::read($expected['de']['file'])['title']);
		$this->assertSame('Subtitle Deutsch', Data::read($expected['de']['file'])['subtitle']);
	}

	/**
	 * @covers ::save
	 */
	public function testSaveExistingSingleLanguage(): void
	{
		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$expected = $this->createContentSingleLanguage();

		$version->save([
			'title' => 'Updated Title'
		]);

		$this->assertSame('Updated Title', Data::read($expected['file'])['title']);
		$this->assertSame('Subtitle', Data::read($expected['file'])['subtitle']);
	}

	/**
	 * @covers ::save
	 */
	public function testSaveNonExistingMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$this->assertContentFileDoesNotExist('en');
		$this->assertContentFileDoesNotExist('de');

		// with Language argument
		$version->save([
			'title' => 'Test'
		], $this->app->language('en'));

		// with string argument
		$version->save([
			'title' => 'Test'
		], 'de');

		$this->assertContentFileExists('en');
		$this->assertContentFileExists('de');
	}

	/**
	 * @covers ::save
	 */
	public function testSaveNonExistingSingleLanguage(): void
	{
		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$this->assertContentFileDoesNotExist();

		$version->save([
			'title' => 'Test'
		]);

		$this->assertContentFileExists();
	}

	/**
	 * @covers ::save
	 */
	public function testSaveOverwriteMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$expected = $this->createContentMultiLanguage();

		// with Language argument
		$version->save([
			'title' => 'Updated Title English'
		], $this->app->language('en'), true);

		// with string argument
		$version->save([
			'title' => 'Updated Title Deutsch',
		], 'de', true);

		$this->assertSame(['title' => 'Updated Title English'], Data::read($expected['en']['file']));
		$this->assertSame(['title' => 'Updated Title Deutsch'], Data::read($expected['de']['file']));
	}

	/**
	 * @covers ::save
	 */
	public function testSaveOverwriteSingleLanguage(): void
	{
		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$expected = $this->createContentSingleLanguage();

		$version->save([
			'title' => 'Updated Title'
		], 'default', true);

		$this->assertSame(['title' => 'Updated Title'], Data::read($expected['file']));
	}

	/**
	 * @covers ::touch
	 */
	public function testTouchMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		touch($rootEN = $this->contentFile('en'), 123456);
		touch($rootDE = $this->contentFile('de'), 123456);

		$this->assertSame(123456, filemtime($rootEN));
		$this->assertSame(123456, filemtime($rootDE));

		$minTime = time();

		// with Language argument
		$version->touch($this->app->language('en'));

		// with string argument
		$version->touch('de');

		clearstatcache();

		$this->assertGreaterThanOrEqual($minTime, filemtime($rootEN));
		$this->assertGreaterThanOrEqual($minTime, filemtime($rootDE));
	}

	/**
	 * @covers ::touch
	 */
	public function testTouchSingleLanguage(): void
	{
		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		touch($root = $this->contentFile(), 123456);
		$this->assertSame(123456, filemtime($root));

		$minTime = time();

		$version->touch();

		clearstatcache();

		$this->assertGreaterThanOrEqual($minTime, filemtime($root));
	}

	/**
	 * @covers ::update
	 */
	public function testUpdateMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$expected = $this->createContentMultiLanguage();

		// with Language argument
		$version->update([
			'title' => 'Updated Title English'
		], $this->app->language('en'));

		// with string argument
		$version->update([
			'title' => 'Updated Title Deutsch',
		], 'de');

		$this->assertSame('Updated Title English', Data::read($expected['en']['file'])['title']);
		$this->assertSame('Subtitle English', Data::read($expected['en']['file'])['subtitle']);
		$this->assertSame('Updated Title Deutsch', Data::read($expected['de']['file'])['title']);
		$this->assertSame('Subtitle Deutsch', Data::read($expected['de']['file'])['subtitle']);
	}

	/**
	 * @covers ::update
	 */
	public function testUpdateSingleLanguage(): void
	{
		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$expected = $this->createContentSingleLanguage();

		$version->update([
			'title' => 'Updated Title'
		]);

		$this->assertSame('Updated Title', Data::read($expected['file'])['title']);
		$this->assertSame('Subtitle', Data::read($expected['file'])['subtitle']);
	}

	/**
	 * @covers ::update
	 * @covers ::convertFieldNamesToLowerCase
	 * @covers ::prepareFieldsBeforeWrite
	 */
	public function testUpdateWithDirtyFields(): void
	{
		$this->setUpMultiLanguage();

		// add a blueprint with an untranslatable field
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/article' => [
					'fields' => [
						'date' => [
							'type'      => 'date',
							'translate' => false
						]
					]
				]
			]
		]);

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$this->createContentMultiLanguage();

		// primary language
		$version->update([
			'title'    => 'Test',
			'uuid'     => '12345',
			'Subtitle' => 'Subtitle',
			'date'     => '2012-12-12'
		], 'en');

		// secondary language
		$version->update([
			'title'    => 'Test',
			'uuid'     => '12345',
			'Subtitle' => 'Subtitle',
			'date'     => '2012-12-12'
		], 'de');

		// check for lower case field names
		$this->assertArrayHasKey('subtitle', $version->read('en'));
		$this->assertArrayHasKey('subtitle', $version->read('de'));

		// check for removed uuid field in secondary language
		$this->assertArrayHasKey('uuid', $version->read('en'));
		$this->assertArrayNotHasKey('uuid', $version->read('de'));

		// check for untranslatable fields
		$this->assertArrayHasKey('date', $version->read('en'));
		$this->assertArrayNotHasKey('date', $version->read('de'));
	}
}
