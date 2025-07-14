<?php

namespace Kirby\Cms;

use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class FileDeleteTest extends ModelTestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.FileDelete';

	protected function createDummyFile(): File
	{
		// create the dummy source
		F::write($source = static::TMP . '/source.md', '# Test');

		return File::create([
			'filename' => 'test.md',
			'parent'   => new Page(['slug' => 'test']),
			'source'   => $source
		]);
	}

	public function testDelete(): void
	{
		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $this->app->site(),
		]);

		$contentFile = $file->version('latest')->contentFile('default');

		// create an empty dummy file
		F::write($file->root(), '');
		// ...and an empty content file for it
		F::write($contentFile, '');

		$this->assertFileExists($file->root());
		$this->assertFileExists($contentFile);

		$result = $file->delete();

		$this->assertTrue($result);

		$this->assertFileDoesNotExist($file->root());
		$this->assertFileDoesNotExist($contentFile);
	}

	public function testDeleteHooks(): void
	{
		$calls   = 0;
		$phpunit = $this;

		$this->app = $this->app->clone([
			'hooks' => [
				'file.delete:before' => function (File $file) use ($phpunit, &$calls) {
					$phpunit->assertFileExists($file->root());
					$phpunit->assertSame('test.md', $file->filename());
					$calls++;
				},
				'file.delete:after' => function ($status, File $file) use ($phpunit, &$calls) {
					$phpunit->assertTrue($status);
					$phpunit->assertFileDoesNotExist($file->root());
					$phpunit->assertSame('test.md', $file->filename());
					$calls++;
				}
			]
		]);

		$this->app->impersonate('kirby');

		$file = $this->createDummyFile();
		$file->delete();

		$this->assertSame(2, $calls);
	}

	public function testDeleteHookWithUUIDAccess(): void
	{
		$phpunit = $this;
		$uuid    = null;

		$this->app = $this->app->clone([
			'hooks' => [
				'file.delete:after' => function ($status, File $file) use ($phpunit, &$uuid) {
					$phpunit->assertSame($uuid, $file->uuid()->id());
				}
			]
		]);

		$this->app->impersonate('kirby');

		$file        = $this->createDummyFile();
		$uuid        = $file->uuid()->id();
		$contentFile = $file->root() . '.txt';

		$this->assertFileExists($contentFile);

		$file->delete();

		$this->assertFileDoesNotExist($contentFile);
	}
}
