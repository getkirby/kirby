<?php

namespace Kirby\Content;

use Kirby\Cms\App;
use Kirby\Data\Data;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;

/**
 * @coversDefaultClass Kirby\Content\Version
 */
class VersionTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Content.Version';

	protected $app;
	protected $model;

	public function assertContentFileExists(string|null $language = null, VersionId|null $versionId = null)
	{
		$this->assertFileExists($this->contentFile($language, $versionId));
	}

	public function assertContentFileDoesNotExist(string|null $language = null, VersionId|null $versionId = null)
	{
		$this->assertFileDoesNotExist($this->contentFile($language, $versionId));
	}

	public function contentFile(string|null $language = null, VersionId|null $versionId = null): string
	{
		return
			$this->model->root() .
			// add the changes folder
			($versionId?->value() === 'changes' ? '/_changes/' : '/') .
			// template
			'article' .
			// language code
			($language === null ? '' : '.' . $language) .
			'.txt';
	}

	public function setUp(): void
	{
		Dir::make(static::TMP);
	}

	public function setUpMultiLanguage(): void
	{
		$this->app = new App([
			'languages' => [
				[
					'code'    => 'en',
					'default' => true
				],
				[
					'code' => 'de'
				]
			],
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'a-page',
						'template' => 'article',
					]
				]
			]
		]);

		$this->model = $this->app->page('a-page');

		Dir::make($this->model->root());
	}

	public function setUpSingleLanguage(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'a-page',
						'template' => 'article'
					]
				]
			]
		]);

		$this->model = $this->app->page('a-page');

		Dir::make($this->model->root());
	}

	public function tearDown(): void
	{
		App::destroy();
		Dir::remove(static::TMP);
	}

	/**
	 * @covers ::content
	 * @covers ::language
	 */
	public function testContentMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		Data::write($this->contentFile('en'), [
			'title' => 'Test'
		]);

		Data::write($this->contentFile('de'), [
			'title' => 'Töst'
		]);

		$this->assertSame('Test', $version->content('en')->get('title')->value());
		$this->assertSame('Test', $version->content($this->app->language('en'))->get('title')->value());
		$this->assertSame('Töst', $version->content('de')->get('title')->value());
		$this->assertSame('Töst', $version->content($this->app->language('de'))->get('title')->value());
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

		Data::write($this->contentFile(), [
			'title' => 'Test'
		]);

		$this->assertSame('Test', $version->content()->get('title')->value());
	}

	/**
	 * @covers ::contentFile
	 * @covers ::language
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
	 * @covers ::language
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

		Data::write($this->contentFile('en'), [
			'title' => 'Test'
		]);

		Data::write($this->contentFile('de'), [
			'title' => 'Test'
		]);

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

		Data::write($this->contentFile(), [
			'title' => 'Test'
		]);

		$this->assertContentFileExists();

		$version->delete();

		$this->assertContentFileDoesNotExist();
	}

	/**
	 * @covers ::ensure
	 * @covers ::language
	 */
	public function testEnsureMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		Data::write($this->contentFile('de'), [
			'title' => 'Test'
		]);

		$this->assertTrue($version->ensure('de'));
		$this->assertTrue($version->ensure($this->app->language('de')));
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

		Data::write($this->contentFile(), [
			'title' => 'Test'
		]);

		$this->assertTrue($version->ensure());
	}

	/**
	 * @covers ::ensure
	 */
	public function testEnsureWhenMissingMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('Version "published (de)" does not already exist');

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
			id: VersionId::published()
		);

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('Version "published" does not already exist');

		$version->ensure();
	}

	/**
	 * @covers ::exists
	 * @covers ::language
	 */
	public function testExistsMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$this->assertFalse($version->exists('de'));
		$this->assertFalse($version->exists($this->app->language('de')));

		Data::write($this->contentFile('de'), []);

		$this->assertTrue($version->exists('de'));
		$this->assertTrue($version->exists($this->app->language('de')));
	}

	/**
	 * @covers ::exists
	 */
	public function testExistsSingleLanguage(): void
	{
		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$this->assertFalse($version->exists());

		Data::write($this->contentFile(), []);

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
	 * @covers ::language
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
	 * @covers ::language
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
	 * @covers ::read
	 * @covers ::language
	 */
	public function testReadMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		Data::write($this->contentFile('en'), $contentEN = [
			'title' => 'Test'
		]);

		Data::write($this->contentFile('de'), $contentDE = [
			'title' => 'Töst'
		]);

		$this->assertSame($contentEN, $version->read('en'));
		$this->assertSame($contentEN, $version->read($this->app->language('en')));
		$this->assertSame($contentDE, $version->read('de'));
		$this->assertSame($contentDE, $version->read($this->app->language('de')));
	}

	/**
	 * @covers ::read
	 */
	public function testReadSingleLanguage(): void
	{
		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		Data::write($this->contentFile(), $content = [
			'title' => 'Test'
		]);

		$this->assertSame($content, $version->read());
	}

	/**
	 * @covers ::touch
	 * @covers ::touchLanguage
	 * @covers ::language
	 */
	public function testTouchAllLanguages(): void
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

		$version->touch();

		clearstatcache();

		$this->assertGreaterThanOrEqual($minTime, filemtime($rootEN));
		$this->assertGreaterThanOrEqual($minTime, filemtime($rootDE));
	}

	/**
	 * @covers ::touch
	 * @covers ::touchLanguage
	 * @covers ::language
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
	 * @covers ::touchLanguage
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
	 * @covers ::language
	 */
	public function testUpdateMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		Data::write($fileEN = $this->contentFile('en'), [
			'title' => 'Test English'
		]);

		Data::write($fileDE = $this->contentFile('de'), [
			'title' => 'Test Deutsch'
		]);

		// with Language argument
		$version->update([
			'title' => 'Updated Title English'
		], $this->app->language('en'));

		// with string argument
		$version->update([
			'title' => 'Updated Title Deutsch'
		], 'de');

		$this->assertSame('Updated Title English', Data::read($fileEN)['title']);
		$this->assertSame('Updated Title Deutsch', Data::read($fileDE)['title']);
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

		Data::write($file = $this->contentFile(), $content = [
			'title' => 'Test'
		]);

		$version->update([
			'title' => 'Updated Title'
		]);

		$this->assertSame('Updated Title', Data::read($file)['title']);
	}
}
