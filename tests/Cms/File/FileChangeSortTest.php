<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class FileChangeSortTest extends ModelTestCase
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

	public function testChangeSortWhenChangesExist(): void
	{
		$page = Page::create([
			'slug' => 'test'
		]);

		$file = File::create([
			'filename' => 'doc.pdf',
			'parent'   => $page,
			'source'   => __DIR__ . '/fixtures/files/doc.pdf'
		]);

		$file->version('changes')->save([
			'text' => 'Some additional text'
		]);

		$modified = $file->changeSort(3);

		$this->assertSame(3, $modified->sort()->toInt());

		$changes = $modified->version('changes')->content();

		$this->assertSame(3, $changes->get('sort')->toInt());
		$this->assertSame('Some additional text', $changes->get('text')->value());
	}

	public function testChangeSortInMultiLanguage(): void
	{
		$this->setUpMultiLanguage();
		$this->app->impersonate('kirby');

		$page = Page::create([
			'slug' => 'test'
		]);

		$file1 = File::create([
			'filename' => 'a.pdf',
			'parent'   => $page,
			'source'   => __DIR__ . '/fixtures/files/doc.pdf',
			'content'  => ['sort' => 1]
		]);

		$file2 = File::create([
			'filename' => 'b.pdf',
			'parent'   => $page,
			'source'   => __DIR__ . '/fixtures/files/doc.pdf',
			'content'  => ['sort' => 2]
		]);

		$file3 = File::create([
			'filename' => 'c.pdf',
			'parent'   => $page,
			'source'   => __DIR__ . '/fixtures/files/doc.pdf',
			'content'  => ['sort' => 3]
		]);

		$this->app->setCurrentLanguage('de');

		$files = $page->files();
		$files->changeSort([
			$file3->id(),
			$file1->id(),
			$file2->id()
		]);

		$file1 = $files->find('a.pdf');
		$file2 = $files->find('b.pdf');
		$file3 = $files->find('c.pdf');

		$this->assertSame(2, $file1->sort()->toInt());
		$this->assertSame(3, $file2->sort()->toInt());
		$this->assertSame(1, $file3->sort()->toInt());

		$this->assertSame(2, $file1->content('en')->get('sort')->toInt());
		$this->assertSame(3, $file2->content('en')->get('sort')->toInt());
		$this->assertSame(1, $file3->content('en')->get('sort')->toInt());

		$this->assertFalse($file1->translation('de')->exists());
		$this->assertFalse($file2->translation('de')->exists());
		$this->assertFalse($file3->translation('de')->exists());
	}
}
