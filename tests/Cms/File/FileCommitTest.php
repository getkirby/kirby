<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClass;

#[CoversClass(File::class)]
class FileCommitTest extends ModelTestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.FileCommit';

	public function testCommit(): void
	{
		$phpunit = $this;
		$page    = new Page(['slug' => 'text']);

		$this->app = $this->app->clone([
			'hooks' => [
				'file.changeSort:before' => [
					function (File $file, int $position) use ($phpunit, $page) {
						$phpunit->assertSame(99, $position);
						$phpunit->assertSame(1, $file->sort()->value());
						// altering $file which will be passed
						// to subsequent hook
						return new File([
							'filename' => 'test.jpg',
							'parent'   => $page,
							'content'  => ['sort' => 2]
						]);
					},
					function (File $file, int $position) use ($phpunit, $page) {
						$phpunit->assertSame(99, $position);
						// altered $file from previous hook
						$phpunit->assertSame(2, $file->sort()->value());
						// altering $file which will be used
						// in the commit callback closure
						return new File([
							'filename' => 'test.jpg',
							'parent'   => $page,
							'content'  => ['sort' => 3]
						]);
					}
				],
				'file.changeSort:after' => [
					function (File $newFile, File $oldFile) use ($phpunit, $page) {
						$phpunit->assertSame(1, $oldFile->sort()->value());
						// modified $file from the commit callback closure
						$phpunit->assertSame(99, $newFile->sort()->value());
						// altering $newFile which will be passed
						// to subsequent hook
						return new File([
							'filename' => 'test.jpg',
							'parent'   => $page,
							'content'  => ['sort' => 4]
						]);
					},
					function (File $newFile, File $oldFile) use ($phpunit, $page) {
						$phpunit->assertSame(1, $oldFile->sort()->value());
						// altered $newFile from previous hook
						$phpunit->assertSame(4, $newFile->sort()->value());
						// altering $newFile which will be the final result
						return new File([
							'filename' => 'test.jpg',
							'parent'   => $page,
							'content'  => ['sort' => 5]
						]);
					}
				]
			]
		]);

		$this->app->impersonate('kirby');

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $page,
			'content'  => ['sort' => 1]
		]);

		$class  = new ReflectionClass($file);
		$commit = $class->getMethod('commit');
		$result = $commit->invokeArgs($file, [
			'changeSort',
			['file' => $file, 'position' => 99],
			function (File $file, int $position) use ($phpunit, $page) {
				$phpunit->assertSame(99, $position);
				// altered $page from before hooks
				$phpunit->assertSame(3, $file->sort()->value());
				return new File([
					'filename' => 'test.jpg',
					'parent'   => $page,
					'content'  => ['sort' => $position]
				]);
			}
		]);

		// altered result from last after hook
		$this->assertSame(5, $result->sort()->value());
	}
}
