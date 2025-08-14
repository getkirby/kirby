<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class FileChangeNameTest extends ModelTestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.FileChangeName';

	public function testChangeName(): void
	{
		$file = new File([
			'filename' => 'test.pdf',
			'parent'   => $this->app->site()
		]);

		// create an empty dummy file
		$root = $file->root();
		F::write($root, '');
		// ...and an empty content file for it
		$content = $file->version('latest')->contentFile('default');

		// We need to create a file with fields here
		// otherwise, our plain text storage handler will
		// remove the file on save
		Data::write($content, [
			'alt' => 'Test'
		]);

		$this->assertFileExists($root);
		$this->assertFileExists($content);

		$result = $file->changeName('foo');

		$this->assertNotSame($root, $result->root());
		$this->assertSame('foo.pdf', $result->filename());
		$this->assertFileDoesNotExist($root);
		$this->assertFileDoesNotExist($content);
		$this->assertFileExists($result->root());
		$this->assertFileExists($result->version('latest')->contentFile('default'));
	}

	public function testChangeNameMultiLang(): void
	{
		$this->setUpMultiLanguage();
		$this->app->impersonate('kirby');

		$file = new File([
			'filename' => 'test.pdf',
			'parent'   => $this->app->site()
		]);

		// create an empty dummy file
		$root = $file->root();
		F::write($root, '');
		// ...and empty content files for it
		$contentEn = $file->version('latest')->contentFile('en');
		$contentDe = $file->version('latest')->contentFile('de');

		// We need to create files with fields here
		// otherwise, our plain text storage handler will
		// remove the file on save
		Data::write($contentEn, [
			'alt' => 'Test EN'
		]);

		Data::write($contentDe, [
			'alt' => 'Test DE'
		]);

		$this->assertFileExists($file->root());
		$this->assertFileExists($contentEn);
		$this->assertFileExists($contentDe);

		$result = $file->changeName('foo');

		$this->assertNotEquals($file->root(), $result->root());
		$this->assertSame('foo.pdf', $result->filename());
		$this->assertFileExists($result->root());
		$this->assertFileExists($result->version('latest')->contentFile('en'));
		$this->assertFileExists($result->version('latest')->contentFile('de'));
	}

	public function testChangeNameHooks(): void
	{
		$calls = 0;
		$phpunit = $this;

		$this->app = $this->app->clone([
			'hooks' => [
				'file.changeName:before' => function (File $file, $name) use ($phpunit, &$calls) {
					$phpunit->assertIsFile($file);
					$phpunit->assertSame('foo', $name);
					$phpunit->assertSame('test.pdf', $file->filename());
					$calls++;
				},
				'file.changeName:after' => function (File $newFile, File $oldFile) use ($phpunit, &$calls) {
					$phpunit->assertIsFile($newFile);
					$phpunit->assertIsFile($oldFile);
					$phpunit->assertSame('foo.pdf', $newFile->filename());
					$phpunit->assertSame('test.pdf', $oldFile->filename());
					$calls++;
				},
			]
		]);

		$this->app->impersonate('kirby');

		$file = new File([
			'filename' => 'test.pdf',
			'parent'   => $this->app->site(),
		]);

		$file->changeName('foo');

		$this->assertSame(2, $calls);
	}

	public function testChangeNameWithoutChanges(): void
	{
		$file = new File([
			'filename' => 'test.pdf',
			'parent'   => $this->app->site()
		]);

		$result = $file->changeName('test');

		$this->assertSame($file, $result);
	}
}
