<?php

namespace Kirby\Cms;

use Kirby\Cms\NewFile as File;
use Kirby\Cms\NewPage as Page;
use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class NewFileDeleteTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewFileDelete';

	public function testDelete(): void
	{
		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $this->app->site(),
		]);

		// create an empty dummy file
		F::write($file->root(), '');
		// ...and an empty content file for it
		F::write($file->version('latest')->contentFile('default'), '');

		$this->assertFileExists($file->root());
		$this->assertFileExists($file->version('latest')->contentFile('default'));

		$result = $file->delete();

		$this->assertTrue($result);

		$this->assertFileDoesNotExist($file->root());
		$this->assertFileDoesNotExist($file->version('latest')->contentFile('default'));
	}

	public function testDeleteHooks(): void
	{
		$parent  = new Page(['slug' => 'test']);
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

		// create the dummy source
		F::write($source = static::TMP . '/source.md', '# Test');

		$file = File::create([
			'filename' => 'test.md',
			'source'   => $source,
			'parent'   => $parent
		]);

		$file->delete();

		$this->assertSame(2, $calls);
	}
}
