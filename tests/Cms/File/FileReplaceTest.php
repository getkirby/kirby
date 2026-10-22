<?php

namespace Kirby\Cms;

use Kirby\Filesystem\F;
use Kirby\Filesystem\File as BaseFile;
use Kirby\Image\Image;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class FileReplaceTest extends ModelTestCase
{
	public const string FIXTURES = __DIR__ . '/fixtures/files';
	public const string TMP = KIRBY_TMP_DIR . '/Cms.FileReplace';

	public function testReplace(): void
	{
		$parent      = new Page(['slug' => 'test']);
		$original    = static::TMP . '/original.md';
		$replacement = static::TMP . '/replacement.md';

		// create the dummy files
		F::write($original, '# Original');
		F::write($replacement, '# Replacement');

		$originalFile = File::create([
			'filename' => 'test.md',
			'source'   => $original,
			'parent'   => $parent
		]);

		$this->assertFileExists($original);
		$this->assertSame(F::read($original), F::read($originalFile->root()));
		$this->assertInstanceOf(BaseFile::class, $originalFile->asset());

		$replacedFile = $originalFile->replace($replacement);

		$this->assertFileExists($original);
		$this->assertFileExists($replacement);
		$this->assertSame(F::read($replacement), F::read($replacedFile->root()));
		$this->assertInstanceOf(BaseFile::class, $replacedFile->asset());
	}

	public function testReplaceMove(): void
	{
		$parent      = new Page(['slug' => 'test']);
		$original    = static::TMP . '/original.md';
		$replacement = static::TMP . '/replacement.md';

		// create the dummy files
		F::write($original, '# Original');
		F::write($replacement, '# Replacement');

		$originalFile = File::create([
			'filename' => 'test.md',
			'source'   => $original,
			'parent'   => $parent
		]);

		$this->assertFileExists($original);
		$this->assertSame(F::read($original), F::read($originalFile->root()));
		$this->assertInstanceOf(BaseFile::class, $originalFile->asset());

		$replacedFile = $originalFile->replace($replacement, true);

		$this->assertFileExists($original);
		$this->assertFileDoesNotExist($replacement);
		$this->assertSame('# Replacement', F::read($replacedFile->root()));
		$this->assertInstanceOf(BaseFile::class, $replacedFile->asset());
	}

	public function testReplaceImage(): void
	{
		$parent      = new Page(['slug' => 'test']);
		$original    = static::FIXTURES . '/test.jpg';
		$replacement = static::FIXTURES . '/cat.jpg';

		$originalFile = File::create([
			'filename' => 'test.jpg',
			'source'   => $original,
			'parent'   => $parent
		]);

		$this->assertSame(F::read($original), F::read($originalFile->root()));
		$this->assertInstanceOf(Image::class, $originalFile->asset());

		$replacedFile = $originalFile->replace($replacement);

		$this->assertSame(F::read($replacement), F::read($replacedFile->root()));
		$this->assertInstanceOf(Image::class, $replacedFile->asset());
	}

	public function testReplaceManipulateNonImage(): void
	{
		$parent      = new Page(['slug' => 'test']);
		$original    = static::FIXTURES . '/test.pdf';
		$replacement = static::FIXTURES . '/doc.pdf';

		$originalFile = File::create([
			'filename' => 'test.pdf',
			'source'   => $original,
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

		$this->assertFileEquals($original, $originalFile->root());

		$replacedFile = $originalFile->replace($replacement);
		$this->assertFileEquals($replacement, $replacedFile->root());
		$this->assertSame('pdf', $replacedFile->extension());
	}

	public function testReplaceHooks(): void
	{
		$parent  = new Page(['slug' => 'test']);
		$calls   = 0;
		$phpunit = $this;

		$this->app = $this->app->clone([
			'hooks' => [
				'file.replace:before' => function (File $file, BaseFile $upload) use ($phpunit, &$calls) {
					$phpunit->assertIsFile($file);
					$phpunit->assertInstanceOf(BaseFile::class, $upload);
					$phpunit->assertSame('site.csv', $file->filename());
					$phpunit->assertSame('replace.csv', $upload->filename());
					$phpunit->assertFileDoesNotExist($file->root());
					$calls++;
				},
				'file.replace:after' => function (File $newFile, File $oldFile) use ($phpunit, &$calls) {
					$phpunit->assertIsFile($newFile);
					$phpunit->assertIsFile($oldFile);
					$phpunit->assertSame('site.csv', $newFile->filename());
					$phpunit->assertSame('Replace', F::read($newFile->root()));
					$phpunit->assertSame('site.csv', $oldFile->filename());
					$calls++;
				},
			]
		]);

		$this->app->impersonate('kirby');

		// create the dummy source
		F::write($source = static::TMP . '/replace.csv', 'Replace');

		File::create([
			'filename' => 'replace.csv',
			'source'   => $source,
			'parent'   => $parent
		]);

		$file = new File([
			'filename' => 'site.csv',
			'parent'   => $this->app->site()
		]);

		$file->replace($source);

		$this->assertSame(2, $calls);
	}
}
