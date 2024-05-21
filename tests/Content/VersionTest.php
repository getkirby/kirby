<?php

namespace Kirby\Content;

use Kirby\Cms\App;
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
	 */
	public function testContentMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$version->create([
			'title' => 'Test'
		], 'en');

		$version->create([
			'title' => 'Töst'
		], 'de');

		$this->assertSame('Test', $version->content('en')->get('title')->value());
		$this->assertSame('Töst', $version->content('de')->get('title')->value());
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

		$version->create([
			'title' => 'Test'
		]);

		$this->assertSame('Test', $version->content()->get('title')->value());
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

		$this->assertFalse($version->exists('de'));

		$version->create([
			'title' => 'Test'
		], 'de');

		$this->assertTrue($version->exists('de'));
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

		$this->assertFalse($version->exists());

		$version->create([
			'title' => 'Test'
		]);

		$this->assertTrue($version->exists());
	}

	/**
	 * @covers ::delete
	 * @covers ::deleteLanguage
	 */
	public function testDeleteMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$this->assertFalse($version->exists('de'));

		$version->create([
			'title' => 'Test'
		], 'de');

		$this->assertTrue($version->exists('de'));

		$version->delete('de');

		$this->assertFalse($version->exists('de'));
	}

	/**
	 * @covers ::delete
	 * @covers ::deleteLanguage
	 */
	public function testDeleteSingleLanguage(): void
	{
		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$this->assertFalse($version->exists());

		$version->create([
			'title' => 'Test'
		]);

		$this->assertTrue($version->exists());

		$version->delete();

		$this->assertFalse($version->exists());
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

		$version->create([], 'de');
		$this->assertTrue($version->ensure('de'));
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

		$version->create([]);
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
	 */
	public function testExistsMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$this->assertFalse($version->exists('de'));

		$version->create([], 'de');

		$this->assertTrue($version->exists('de'));
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

		$version->create([]);

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

		touch($this->model->root() . '/article.de.txt', $modified = 123456);

		$this->assertSame($modified, $version->modified('de'));
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
		$this->assertNull($version->modified('de'));
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

		touch($this->model->root() . '/article.txt', $modified = 123456);

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
	 * @covers ::read
	 */
	public function testReadMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$version->create($contentEN = [
			'title' => 'Test'
		], 'en');

		$version->create($contentDE = [
			'title' => 'Töst'
		], 'de');

		$this->assertSame($contentEN, $version->read('en'));
		$this->assertSame($contentDE, $version->read('de'));
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

		$version->create($content = [
			'title' => 'Test'
		]);

		$this->assertSame($content, $version->read());
	}

	/**
	 * @covers ::touch
	 * @covers ::touchLanguage
	 */
	public function testTouchMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		touch($root = $this->model->root() . '/article.de.txt', 123456);
		$this->assertSame(123456, filemtime($root));

		$minTime = time();

		$version->touch('de');

		clearstatcache();

		$this->assertGreaterThanOrEqual($minTime, filemtime($root));
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

		touch($root = $this->model->root() . '/article.txt', 123456);
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

		$version->create([
			'title' => 'Test'
		], 'de');

		$this->assertSame('Test', $version->read('de')['title']);

		$version->update([
			'title' => 'Updated Title'
		], 'de');

		$this->assertSame('Updated Title', $version->read('de')['title']);
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

		$version->create([
			'title' => 'Test'
		]);

		$this->assertSame('Test', $version->read()['title']);

		$version->update([
			'title' => 'Updated Title'
		]);

		$this->assertSame('Updated Title', $version->read()['title']);
	}
}
