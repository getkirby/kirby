<?php

namespace Kirby\Cms;



use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class FileChangeSortTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.FileChangeSort';

	public function testChangeSortHooks(): void
	{
		$calls = 0;
		$phpunit = $this;

		$this->app = $this->app->clone([
			'hooks' => [
				'file.changeSort:before' => function (File $file, $position) use ($phpunit, &$calls) {
					$phpunit->assertIsFile($file);
					$phpunit->assertSame(3, $position);
					$phpunit->assertSame(1, $file->sort()->value());
					$calls++;
				},
				'file.changeSort:after' => function (File $newFile, File $oldFile) use ($phpunit, &$calls) {
					$phpunit->assertIsFile($newFile);
					$phpunit->assertIsFile($oldFile);
					$phpunit->assertSame(3, $newFile->sort()->value());
					$phpunit->assertSame(1, $oldFile->sort()->value());
					$calls++;
				},
			]
		]);

		$page  = new Page(['slug' => 'test']);
		$file1 = new File([
			'filename' => 'site-1.csv',
			'parent'   => $page,
			'content'  => ['sort' => 1]
		]);
		$file2 = new File([
			'filename' => 'site-2.csv',
			'parent'   => $page,
			'content'  => ['sort' => 2]
		]);
		$file3 = new File([
			'filename' => 'site-3.csv',
			'parent'   => $page,
			'content'  => ['sort' => 3]
		]);

		$this->app->impersonate('kirby');

		$file1->changeSort(1);
		$this->assertSame(0, $calls);

		$file1->changeSort(3);
		$this->assertSame(2, $calls);
	}
}
