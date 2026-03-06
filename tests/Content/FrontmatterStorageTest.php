<?php

namespace Kirby\Content;

use Kirby\Cms\File;
use Kirby\Cms\Language;
use Kirby\Data\Frontmatter;
use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FrontmatterStorage::class)]
class FrontmatterStorageTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Content.FrontmatterStorage';

	protected $storage;

	public function setUpMultiLanguage(
		array|null $site = null
	): void {
		parent::setUpMultiLanguage(site: $site);

		$this->storage = new FrontmatterStorage($this->model);
	}

	public function setUpSingleLanguage(
		array|null $site = null
	): void {
		parent::setUpSingleLanguage(site: $site);

		$this->storage = new FrontmatterStorage($this->model);
	}

	public function testCreateChangesMultiLang(): void
	{
		$this->setUpMultiLanguage();

		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		$this->storage->create(VersionId::changes(), $this->app->language('en'), $fields);

		$contentFile = $this->model->root() . '/_changes/article.en.txt';
		$this->assertFileExists($contentFile);
		$this->assertSame($fields, Frontmatter::decode(F::read($contentFile)));
		$this->assertStringContainsString('---', F::read($contentFile));
	}

	public function testCreateChangesSingleLang(): void
	{
		$this->setUpSingleLanguage();

		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		$this->storage->create(VersionId::changes(), Language::single(), $fields);

		$contentFile = $this->model->root() . '/_changes/article.txt';
		$this->assertFileExists($contentFile);
		$this->assertSame($fields, Frontmatter::decode(F::read($contentFile)));
		$this->assertStringContainsString('---', F::read($contentFile));
	}

	public function testCreateLatestMultiLang(): void
	{
		$this->setUpMultiLanguage();

		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		$this->storage->create(VersionId::latest(), $this->app->language('en'), $fields);

		$contentFile = $this->model->root() . '/article.en.txt';
		$this->assertFileExists($contentFile);
		$this->assertSame($fields, Frontmatter::decode(F::read($contentFile)));
		$this->assertStringContainsString('---', F::read($contentFile));
	}

	public function testCreateLatestSingleLang(): void
	{
		$this->setUpSingleLanguage();

		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		$this->storage->create(VersionId::latest(), Language::single(), $fields);

		$contentFile = $this->model->root() . '/article.txt';
		$this->assertFileExists($contentFile);
		$this->assertSame($fields, Frontmatter::decode(F::read($contentFile)));
		$this->assertStringContainsString('---', F::read($contentFile));
	}

	public function testRead(): void
	{
		$this->setUpSingleLanguage();

		$fields      = ['title' => 'Foo', 'text' => 'Bar'];
		$contentFile = $this->model->root() . '/article.txt';

		F::write($contentFile, Frontmatter::encode($fields));

		$this->assertSame($fields, $this->storage->read(VersionId::latest(), Language::single()));
	}

	public function testReadMissingFile(): void
	{
		$this->setUpSingleLanguage();

		$this->assertSame([], $this->storage->read(VersionId::latest(), Language::single()));
	}

	public function testUpdateForFileWithMetaData(): void
	{
		$this->setUpSingleLanguage();

		$file = new File([
			'parent'   => $this->app->site(),
			'filename' => 'image.jpg'
		]);

		$storage = new FrontmatterStorage($file);
		$content = ['alt' => 'Test'];

		$storage->update(VersionId::latest(), Language::single(), $content);

		$contentFile = $file->parent()->root() . '/image.jpg.txt';
		$this->assertFileExists($contentFile);
		$this->assertSame($content, Frontmatter::decode(F::read($contentFile)));
	}

	public function testUpdateForFileWithoutMetaData(): void
	{
		$this->setUpSingleLanguage();

		$file = new File([
			'parent'   => $this->app->site(),
			'filename' => 'image.jpg'
		]);

		$storage = new FrontmatterStorage($file);
		$storage->update(VersionId::latest(), Language::single(), []);

		$this->assertFileDoesNotExist($file->parent()->root() . '/image.jpg.txt');
	}
}
