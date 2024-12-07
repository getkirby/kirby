<?php

namespace Kirby\Content;

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
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
			id: VersionId::latest()
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
			id: VersionId::latest()
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
			id: VersionId::latest()
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
	 * @covers ::content
	 * @covers ::prepareFieldsForContent
	 */
	public function testContentPrepareFields(): void
	{
		$this->setUpSingleLanguage();

		// for pages
		$version = new Version(
			model: $this->model,
			id: VersionId::latest()
		);

		$version->update([
			'lock' => 'test',
			'slug' => 'foo',
			'text' => 'Lorem ipsum'
		]);

		$this->assertSame([
			'text' => 'Lorem ipsum'
		], $version->content()->toArray());

		// for files
		$model = new File([
			'filename' => 'test.jpg',
			'parent'   => $this->model
		]);

		$version = new Version(
			model: $model,
			id: VersionId::latest()
		);

		$version->create([
			'lock'     => 'test',
			'template' => 'foo',
			'text'     => 'Lorem ipsum'
		]);

		$this->assertSame([
			'template' => 'foo',
			'text'     => 'Lorem ipsum',
		], $version->content()->toArray());
	}

	/**
	 * @covers ::contentFile
	 */
	public function testContentFileMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::latest()
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
			id: VersionId::latest()
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
			id: VersionId::latest()
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
	 * @covers ::create
	 */
	public function testCreateMultiLanguageWhenLatestTranslationIsMissing(): void
	{
		$this->setUpMultiLanguage();

		$latest = new Version(
			model: $this->model,
			id: VersionId::latest()
		);

		$changes = new Version(
			model: $this->model,
			id: VersionId::changes()
		);

		$this->assertContentFileDoesNotExist('en', $latest->id());
		$this->assertContentFileDoesNotExist('en', $changes->id());
		$this->assertContentFileDoesNotExist('de', $latest->id());
		$this->assertContentFileDoesNotExist('de', $changes->id());

		// create the latest version for the default translation
		$latest->save([
			'title' => 'Test'
		], $this->app->language('en'));

		// create a changes version in the other language
		$changes->save([
			'title' => 'Translated Test',
		], $this->app->language('de'));

		$this->assertContentFileExists('en', $latest->id());
		$this->assertContentFileDoesNotExist('en', $changes->id());
		$this->assertContentFileExists('de', $latest->id());
		$this->assertContentFileExists('de', $changes->id());
	}

	/**
	 * @covers ::create
	 */
	public function testCreateSingleLanguage(): void
	{
		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::latest()
		);

		$this->assertContentFileDoesNotExist();

		$version->save([
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
			id: VersionId::latest()
		);

		// primary language
		$version->save([
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
			id: VersionId::latest()
		);

		$this->assertContentFileDoesNotExist('de');
		$this->assertContentFileDoesNotExist('en');

		$this->createContentMultiLanguage();

		$this->assertContentFileExists('en');
		$this->assertContentFileExists('de');

		$version->delete('en');

		$this->assertContentFileDoesNotExist('en');
		$this->assertContentFileExists('de');

		$version->delete('de');

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
			id: VersionId::latest()
		);

		$this->assertContentFileDoesNotExist();

		$this->createContentSingleLanguage();

		$this->assertContentFileExists();

		$version->delete();

		$this->assertContentFileDoesNotExist();
	}

	/**
	 * @covers ::exists
	 */
	public function testExistsLatestMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::latest()
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
	public function testExistsWithLanguageWildcard(): void
	{
		$this->setUpMultiLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::latest()
		);

		$this->createContentMultiLanguage();

		$this->assertTrue($version->exists('en'));
		$this->assertTrue($version->exists('de'));
		$this->assertTrue($version->exists('*'));

		// delete the German translation
		$version->delete('de');

		$this->assertTrue($version->exists('en'));
		$this->assertFalse($version->exists('de'));

		// The wildcard should now still return true
		// because the English translation still exists
		$this->assertTrue($version->exists('*'));
	}

	/**
	 * @covers ::exists
	 */
	public function testExistsLatestSingleLanguage(): void
	{
		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::latest()
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
			id: $id = VersionId::latest()
		);

		$this->assertSame($id, $version->id());
	}

	/**
	 * @covers ::isIdentical
	 */
	public function testIsIdenticalMultiLanguage()
	{
		$this->setUpMultiLanguage();

		$a = new Version(
			model: $this->model,
			id: VersionId::latest()
		);

		$b = new Version(
			model: $this->model,
			id: VersionId::changes()
		);

		$a->save($content = [
			'title'    => 'Title',
			'subtitle' => 'Subtitle',
		], 'en');

		$a->save($content, 'de');

		$b->save($content, 'en');

		$b->save([
			'title'    => 'Title',
			'subtitle' => 'Subtitle (changed)',
		], 'de');

		// no changes in English
		$this->assertTrue($a->isIdentical(VersionId::changes(), 'en'));

		// changed subtitle in German
		$this->assertFalse($a->isIdentical(VersionId::changes(), 'de'));
	}

	/**
	 * @covers ::isIdentical
	 */
	public function testIsIdenticalSingleLanguage()
	{
		$this->setUpSingleLanguage();

		$a = new Version(
			model: $this->model,
			id: VersionId::latest()
		);

		$b = new Version(
			model: $this->model,
			id: VersionId::changes()
		);

		$a->save($content = [
			'title'    => 'Title',
			'subtitle' => 'Subtitle',
		]);

		$b->save([
			'title'    => 'Title',
			'subtitle' => 'Subtitle (changed)',
		]);

		$this->assertFalse($a->isIdentical('changes'));
	}

	/**
	 * @covers ::isIdentical
	 */
	public function testIsIdenticalWithoutChanges()
	{
		$this->setUpSingleLanguage();

		$a = new Version(
			model: $this->model,
			id: VersionId::latest()
		);

		$b = new Version(
			model: $this->model,
			id: VersionId::changes()
		);

		$a->save([
			'title'    => 'Title',
			'subtitle' => 'Subtitle',
		]);

		$b->save([
			'title'    => 'Title',
			'subtitle' => 'Subtitle',
		]);

		$this->assertTrue($a->isIdentical('changes'));
	}

	/**
	 * @covers ::isIdentical
	 */
	public function testIsIdenticalWithSameVersion()
	{
		$this->setUpSingleLanguage();

		$a = new Version(
			model: $this->model,
			id: VersionId::latest()
		);

		$a->save([
			'title'    => 'Title',
			'subtitle' => 'Subtitle',
		]);

		$this->assertTrue($a->isIdentical('latest'));
	}

	/**
	 * @covers ::isLocked
	 */
	public function testIsLocked(): void
	{
		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::latest()
		);

		$this->assertFalse($version->isLocked());
	}

	/**
	 * @covers ::lock
	 */
	public function testLock(): void
	{
		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::latest()
		);

		$this->assertInstanceOf(Lock::class, $version->lock());
	}

	/**
	 * @covers ::model
	 */
	public function testModel(): void
	{
		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::latest()
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
			id: VersionId::latest()
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
			id: VersionId::latest()
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
			id: VersionId::latest()
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
			id: VersionId::latest()
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
			id: $versionId = VersionId::latest()
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
		$version->move('en', toLanguage: 'de');

		$this->assertContentFileDoesNotExist('en');
		$this->assertContentFileExists('de');

		$this->assertSame($content, Data::read($fileDE));

		// move with Language arguments
		$version->move($this->app->language('de'), toLanguage: $this->app->language('en'));

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

		$versionLatest = new Version(
			model: $this->model,
			id: $versionIdLatest = VersionId::latest()
		);

		$versionChanges = new Version(
			model: $this->model,
			id: $versionIdChanges = VersionId::changes()
		);

		$this->assertContentFileDoesNotExist('en', $versionIdLatest);
		$this->assertContentFileDoesNotExist('en', $versionIdChanges);

		$fileENLatest = $this->contentFile('en', $versionIdLatest);
		$fileENChanges   = $this->contentFile('en', $versionIdChanges);

		Data::write($fileENLatest, $content = [
			'title' => 'Test'
		]);

		$this->assertContentFileExists('en', $versionIdLatest);
		$this->assertContentFileDoesNotExist('en', $versionIdChanges);

		// move with string arguments
		$versionLatest->move('en', $versionIdChanges);

		$this->assertContentFileDoesNotExist('en', $versionIdLatest);
		$this->assertContentFileExists('en', $versionIdChanges);

		$this->assertSame($content, Data::read($fileENChanges));

		// move the version back
		$versionChanges->move('en', $versionIdLatest);

		$this->assertContentFileDoesNotExist('en', $versionIdChanges);
		$this->assertContentFileExists('en', $versionIdLatest);

		$this->assertSame($content, Data::read($fileENLatest));
	}

	/**
	 * @covers ::previewToken
	 */
	public function testPreviewToken()
	{
		$this->setUpSingleLanguage();

		// site
		$version = new Version(
			model: $this->app->site(),
			id: VersionId::latest()
		);
		$expected = substr(hash_hmac('sha1', '{"uri":"","versionId":"latest"}', static::TMP . '/content'), 0, 10);
		$this->assertSame($expected, $version->previewToken());

		// page
		$version = new Version(
			model: $this->model,
			id: VersionId::latest()
		);
		$expected = substr(hash_hmac('sha1', '{"uri":"a-page","versionId":"latest"}', static::TMP . '/content'), 0, 10);
		$this->assertSame($expected, $version->previewToken());
	}

	/**
	 * @covers ::previewToken
	 */
	public function testPreviewTokenCustomSalt()
	{
		$this->setUpSingleLanguage();

		$this->app->clone([
			'options' => [
				'content' => [
					'salt' => 'testsalt'
				]
			]
		]);

		$version = new Version(
			model: $this->model,
			id: VersionId::latest()
		);

		$expected = substr(hash_hmac('sha1', '{"uri":"a-page","versionId":"latest"}', 'testsalt'), 0, 10);
		$this->assertSame($expected, $version->previewToken());
	}

	/**
	 * @covers ::previewToken
	 */
	public function testPreviewTokenCustomSaltCallback()
	{
		$this->setUpSingleLanguage();

		$this->app = $this->app->clone([
			'options' => [
				'content' => [
					'salt' => function ($model) {
						$this->assertNull($model);

						return 'salt-lake-city';
					}
				]
			]
		]);

		$version = new Version(
			model: $this->model,
			id: VersionId::latest()
		);

		$expected = substr(hash_hmac('sha1', '{"uri":"a-page","versionId":"latest"}', 'salt-lake-city'), 0, 10);
		$this->assertSame($expected, $version->previewToken());
	}

	/**
	 * @covers ::previewToken
	 */
	public function testPreviewTokenInvalidModel()
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Invalid model type');

		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model->file(),
			id: VersionId::latest()
		);

		$version->previewToken();
	}

	/**
	 * @covers ::previewToken
	 */
	public function testPreviewTokenMissingHomePage()
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The home page does not exist');

		$app = new App([
			'roots' => [
				'index' => static::TMP
			]
		]);

		$version = new Version(
			model: $app->site(),
			id: VersionId::latest()
		);

		$version->previewToken();
	}

	/**
	 * @covers ::publish
	 */
	public function testPublish()
	{
		$this->setUpSingleLanguage();
		$this->app->impersonate('kirby');

		$version = new Version(
			model: $this->model,
			id: VersionId::changes()
		);

		Data::write($fileLatest = $this->contentFile(null, VersionId::latest()), [
			'title' => 'Title Latest'
		]);

		Data::write($fileChanges = $this->contentFile(null, VersionId::changes()), [
			'title' => 'Title changes'
		]);

		$this->assertFileExists($fileLatest);
		$this->assertFileExists($fileChanges);

		$version->publish();

		$this->assertFileDoesNotExist($fileChanges);

		$this->assertSame('Title changes', Data::read($fileLatest)['title']);
	}

	/**
	 * @covers ::publish
	 */
	public function testPublishAlreadyLatestVersion()
	{
		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::latest()
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
			id: VersionId::latest()
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
			id: VersionId::latest()
		);

		$expected = $this->createContentSingleLanguage();

		$this->assertSame($expected['content'], $version->read());
	}

	/**
	 * @covers ::read
	 */
	public function testReadLatestWithoutContentFile(): void
	{
		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::latest()
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
			id: VersionId::latest()
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
			id: VersionId::latest()
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
			id: VersionId::latest()
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
			id: VersionId::latest()
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
			id: VersionId::latest()
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
			id: VersionId::latest()
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
			id: VersionId::latest()
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
			id: VersionId::latest()
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
			id: VersionId::latest()
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
			id: VersionId::latest()
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
			id: VersionId::latest()
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
			id: VersionId::latest()
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
			id: VersionId::latest()
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
			id: VersionId::latest()
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
			id: VersionId::latest()
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
			id: VersionId::latest()
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

	/**
	 * @covers ::url
	 * @covers ::urlWithQueryParams
	 */
	public function testUrlPage()
	{
		$this->setUpSingleLanguage();

		// authenticate
		$this->app->impersonate('kirby');

		$version = new Version(
			model: $this->model,
			id: VersionId::latest()
		);

		$this->assertSame('/a-page', $version->url());
	}

	/**
	 * @covers ::url
	 */
	public function testUrlPageUnauthenticated()
	{
		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::latest()
		);

		$this->assertNull($version->url());
	}

	public static function pageUrlProvider(): array
	{
		return [
			// latest version
			[null, '/test', null, false, 'latest'],
			[null, '/test?{token}', 'test', true, 'latest'],
			[true, '/test', null, false, 'latest'],
			[true, '/test?{token}', 'test', true, 'latest'],
			['https://test.com', 'https://test.com', null, false, 'latest'],
			['https://test.com', 'https://test.com', null, true, 'latest'],
			['/something/different', '/something/different', 'something\/different', false, 'latest'],
			['/something/different', '/something/different?{token}', 'something\/different', true, 'latest'],
			['{{ site.url }}#{{ page.slug }}', '/#test', null, false, 'latest'],
			['{{ site.url }}#{{ page.slug }}', '/?{token}#test', '', true, 'latest'],
			['{{ page.url }}?preview=true', '/test?preview=true', null, false, 'latest'],
			['{{ page.url }}?preview=true', '/test?preview=true&{token}', 'test', true, 'latest'],
			['{{ page.url }}/param:something', '/test/param:something', null, false, 'latest'],
			['{{ page.url }}/param:something', '/test/param:something?{token}', 'test', true, 'latest'],
			[false, null, null, false, 'latest'],
			[false, null, null, true, 'latest'],
			[null, null, null, false, 'latest', false],

			// changes version
			[null, '/test?{token}&_version=changes', 'test', false, 'changes'],
			[null, '/test?{token}&_version=changes', 'test', true, 'changes'],
			[true, '/test?{token}&_version=changes', 'test', false, 'changes'],
			[true, '/test?{token}&_version=changes', 'test', true, 'changes'],
			['https://test.com', 'https://test.com', null, false, 'changes'],
			['https://test.com', 'https://test.com', null, true, 'changes'],
			['/something/different', '/something/different?{token}&_version=changes', 'something\/different', false, 'changes'],
			['/something/different', '/something/different?{token}&_version=changes', 'something\/different', true, 'changes'],
			['{{ site.url }}#{{ page.slug }}', '/?{token}&_version=changes#test', '', false, 'changes'],
			['{{ site.url }}#{{ page.slug }}', '/?{token}&_version=changes#test', '', true, 'changes'],
			['{{ page.url }}?preview=true', '/test?preview=true&{token}&_version=changes', 'test', false, 'changes'],
			['{{ page.url }}?preview=true', '/test?preview=true&{token}&_version=changes', 'test', true, 'changes'],
			['{{ page.url }}/param:something', '/test/param:something?{token}&_version=changes', 'test', false, 'changes'],
			['{{ page.url }}/param:something', '/test/param:something?{token}&_version=changes', 'test', true, 'changes'],
			[false, null, null, false, 'changes'],
			[false, null, null, true, 'changes'],
			[null, null, null, false, 'changes', false],
		];
	}

	/**
	 * @covers ::previewTokenFromUrl
	 * @covers ::url
	 * @covers ::urlFromOption
	 * @covers ::urlWithQueryParams
	 * @dataProvider pageUrlProvider
	 */
	public function testUrlPageCustom(
		$input,
		$expected,
		$expectedUri,
		bool $draft,
		string $versionId,
		bool $authenticated = true
	): void {
		$this->setUpSingleLanguage();

		$app = $this->app->clone([
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
		if ($authenticated === true) {
			$app->impersonate('test@getkirby.com');
		}

		$options = [];

		if ($input !== null) {
			$options = [
				'preview' => $input
			];
		}

		$page = new Page([
			'slug' => 'test',
			'isDraft' => $draft,
			'blueprint' => [
				'name'    => 'test',
				'options' => $options
			]
		]);

		if ($expected !== null) {
			$expectedToken = substr(
				hash_hmac(
					'sha1',
					'{"uri":"' . $expectedUri . '","versionId":"' . $versionId . '"}',
					$page->kirby()->root('content')
				),
				0,
				10
			);
			$expected = str_replace(
				'{token}',
				'_token=' . $expectedToken,
				$expected
			);
		}

		$version = new Version(
			model: $page,
			id: VersionId::from($versionId)
		);

		$this->assertSame($expected, $version->url());
	}

	/**
	 * @covers ::url
	 * @covers ::urlWithQueryParams
	 */
	public function testUrlSite()
	{
		$this->setUpSingleLanguage();

		// authenticate
		$this->app->impersonate('kirby');

		$version = new Version(
			model: $this->app->site(),
			id: VersionId::latest()
		);

		$this->assertSame('/', $version->url());
	}

	/**
	 * @covers ::url
	 */
	public function testUrlSiteUnauthenticated()
	{
		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->app->site(),
			id: VersionId::latest()
		);

		$this->assertNull($version->url());
	}

	public static function siteUrlProvider(): array
	{
		return [
			// latest version
			[null, '/', 'latest'],
			['https://test.com', 'https://test.com', 'latest'],
			['{{ site.url }}#test', '/#test', 'latest'],
			[false, null, 'latest'],
			[null, null, 'latest', false],

			// changes version
			[null, '/?{token}&_version=changes', 'changes'],
			['https://test.com', 'https://test.com', 'changes'],
			['{{ site.url }}#test', '/?{token}&_version=changes#test', 'changes'],
			[false, null, 'changes'],
			[null, null, 'changes', false],
		];
	}

	/**
	 * @covers ::previewTokenFromUrl
	 * @covers ::url
	 * @covers ::urlFromOption
	 * @covers ::urlWithQueryParams
	 * @dataProvider siteUrlProvider
	 */
	public function testUrlSiteCustom(
		$input,
		$expected,
		string $versionId,
		bool $authenticated = true
	): void {
		$this->setUpSingleLanguage();

		$options = [];

		if ($input !== null) {
			$options = [
				'preview' => $input
			];
		}

		$app = $this->app->clone([
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
			],
			'site' => [
				'blueprint' => [
					'name'    => 'site',
					'options' => $options
				]
			]
		]);

		// authenticate
		if ($authenticated === true) {
			$app->impersonate('test@getkirby.com');
		}

		$site = $app->site();

		if ($expected !== null) {
			$expectedToken = substr(
				hash_hmac(
					'sha1',
					'{"uri":"","versionId":"' . $versionId . '"}',
					$site->kirby()->root('content')
				),
				0,
				10
			);
			$expected = str_replace(
				'{token}',
				'_token=' . $expectedToken,
				$expected
			);
		}

		$version = new Version(
			model: $site,
			id: VersionId::from($versionId)
		);

		$this->assertSame($expected, $version->url());
	}

	/**
	 * @covers ::url
	 */
	public function testUrlInvalidModel()
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Only pages and the site have a content preview URL');

		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model->file(),
			id: VersionId::latest()
		);

		$version->url();
	}
}
