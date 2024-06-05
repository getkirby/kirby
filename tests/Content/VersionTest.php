<?php

namespace Kirby\Content;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;

/**
 * @coversDefaultClass Kirby\Content\Version
 */
class VersionTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Content.Version';

	protected $model;

	public function setUp(): void
	{
		Dir::make(static::TMP);

		$this->model = new Page([
			'kirby'    => new App(),
			'root'     => static::TMP,
			'slug'     => 'a-page',
			'template' => 'article'
		]);
	}

	public function tearDown(): void
	{
		App::destroy();
		Dir::remove(static::TMP);
	}

	/**
	 * @covers ::create
	 */
	public function testCreate(): void
	{
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
	 * @covers ::create
	 */
	public function testCreateLanguage(): void
	{
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
	 * @covers ::delete
	 */
	public function testDelete(): void
	{
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
	 * @covers ::exists
	 */
	public function testExists(): void
	{
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
		$version = new Version(
			model: $this->model,
			id: VersionId::published()
		);

		$this->assertSame($this->model, $version->model());
	}

	/**
	 * @covers ::read
	 */
	public function testRead(): void
	{
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
	 * @covers ::update
	 */
	public function testUpdate(): void
	{
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
