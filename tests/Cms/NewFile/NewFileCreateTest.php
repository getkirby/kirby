<?php

namespace Kirby\Cms;

use Kirby\Cms\NewFile as File;
use Kirby\Cms\NewPage as Page;
use Kirby\Filesystem\F;
use Kirby\Filesystem\File as BaseFile;
use Kirby\Image\Image;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class NewFileCreateTest extends NewModelTestCase
{
	public const FIXTURES = __DIR__ . '/fixtures/files';
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewFileCreate';

	public function testCreate(): void
	{
		$parent = new Page(['slug' => 'test']);
		$source = static::TMP . '/source.md';

		// create the dummy source
		F::write($source, '# Test');

		$result = File::create([
			'filename' => 'test.md',
			'source'   => $source,
			'parent'   => $parent
		]);

		$this->assertFileExists($source);
		$this->assertFileExists($result->root());
		$this->assertFileExists($parent->root() . '/test.md');
		$this->assertInstanceOf(BaseFile::class, $result->asset());

		// make sure file received UUID right away
		$this->assertIsString($result->content()->get('uuid')->value());
	}

	public function testCreateDuplicate(): void
	{
		$parent = new Page(['slug' => 'test']);
		$source = static::TMP . '/source.md';

		// create the dummy source
		F::write($source, '# Test');

		$result = File::create([
			'filename' => 'test.md',
			'source'   => $source,
			'parent'   => $parent
		]);

		$uuid = $result->content()->get('uuid')->value();

		$duplicate = File::create([
			'filename' => 'test.md',
			'source'   => $source,
			'parent'   => $parent
		]);

		$this->assertSame($uuid, $duplicate->content()->get('uuid')->value());
	}

	public function testCreateMove(): void
	{
		$parent = new Page(['slug' => 'test']);
		$source = static::TMP . '/source.md';

		// create the dummy source
		F::write($source, '# Test');

		$result = File::create([
			'filename' => 'test.md',
			'source'   => $source,
			'parent'   => $parent
		], true);

		$this->assertFileDoesNotExist($source);
		$this->assertFileExists($result->root());
		$this->assertFileExists($parent->root() . '/test.md');
		$this->assertInstanceOf(BaseFile::class, $result->asset());

		// make sure file received UUID right away
		$this->assertIsString($result->content()->get('uuid')->value());
	}

	public function testCreateWithDefaults(): void
	{
		$parent = new Page(['slug' => 'test']);
		$source = static::TMP . '/source.md';

		// create the dummy source
		F::write($source, '# Test');

		$result = File::create([
			'filename' => 'test.md',
			'source'   => $source,
			'parent'   => $parent,
			'blueprint' => [
				'name' => 'test',
				'fields' => [
					'a'  => [
						'type'    => 'text',
						'default' => 'A'
					],
					'b' => [
						'type'    => 'textarea',
						'default' => 'B'
					]
				]
			]
		]);

		$this->assertSame('A', $result->a()->value());
		$this->assertSame('B', $result->b()->value());
	}

	public function testCreateWithDefaultsAndContent(): void
	{
		$parent = new Page(['slug' => 'test']);
		$source = static::TMP . '/source.md';

		// create the dummy source
		F::write($source, '# Test');

		$result = File::create([
			'content' => [
				'a' => 'Custom A'
			],
			'filename' => 'test.md',
			'source'   => $source,
			'parent'   => $parent,
			'blueprint' => [
				'name' => 'test',
				'fields' => [
					'a'  => [
						'type'    => 'text',
						'default' => 'A'
					],
					'b' => [
						'type'    => 'textarea',
						'default' => 'B'
					]
				]
			]
		]);

		$this->assertSame('Custom A', $result->a()->value());
		$this->assertSame('B', $result->b()->value());
	}

	public function testCreateImage(): void
	{
		$parent = new Page(['slug' => 'test']);
		$source = static::FIXTURES . '/test.jpg';

		$result = File::create([
			'filename' => 'test.jpg',
			'source'   => $source,
			'parent'   => $parent
		]);

		$this->assertFileExists($result->root());
		$this->assertFileExists($parent->root() . '/test.jpg');
		$this->assertInstanceOf(Image::class, $result->asset());
	}

	public function testCreateImageAndManipulate(): void
	{
		$parent = new Page(['slug' => 'test']);
		$source = static::FIXTURES . '/test.jpg';

		$result = File::create([
			'filename' => 'test.jpg',
			'source'   => $source,
			'parent'   => $parent,
			'blueprint' => [
				'name' => 'test',
				'create' => [
					'width'  => 100,
					'height' => 100,
					'format' => 'webp'
				]
			]
		]);

		$this->assertFileExists($result->root());
		$this->assertFileExists($parent->root() . '/test.webp');
		$this->assertSame(100, $result->width());
		$this->assertSame(100, $result->height());
		$this->assertSame('webp', $result->extension());
		$this->assertSame('test.webp', $result->filename());
	}

	public function testCreateManipulateNonImage(): void
	{
		$parent = new Page(['slug' => 'test']);
		$source = static::FIXTURES . '/test.pdf';

		$result = File::create([
			'filename'  => 'test.pdf',
			'source'    => $source,
			'parent'    => $parent,
			'blueprint' => [
				'name'   => 'test',
				'create' => [
					'width'  => 100,
					'height' => 100,
					'format' => 'webp'
				]
			]
		]);

		$this->assertFileEquals($source, $result->root());
	}

	public function testCreateHooks(): void
	{
		$parent  = new Page(['slug' => 'test']);
		$phpunit = $this;
		$before  = false;
		$after   = false;

		$this->app = $this->app->clone([
			'hooks' => [
				'file.create:before' => function (File $file, BaseFile $upload) use (&$before) {
					$before = true;
				},
				'file.create:after' => function (File $file) use (&$after, $phpunit) {
					$phpunit->assertTrue($file->siblings(true)->has($file));
					$phpunit->assertTrue($file->parent()->files()->has($file));
					$phpunit->assertSame('test.md', $file->filename());

					$after = true;
				}
			]
		]);

		$this->app->impersonate('kirby');

		// create the dummy source
		F::write($source = static::TMP . '/source.md', '# Test');

		$result = File::create([
			'filename' => 'test.md',
			'source'   => $source,
			'parent'   => $parent
		]);

		$this->assertTrue($before);
		$this->assertTrue($after);
	}
}
