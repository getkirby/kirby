<?php

namespace Kirby\Cms;

use Kirby\TestCase;

class FileMethodsTest extends TestCase
{
	public function setUp(): void
	{
		$this->app = new App([
			'fileMethods' => [
				'test' => fn () => 'file method'
			],
			'filesMethods' => [
				'test' => fn () => 'files method'
			],
			'site' => [
				'children' => [
					[
						'slug'  => 'test',
						'files' => [
							[
								'filename' => 'test.jpg'
							]
						]
					]
				]
			]
		]);
	}

	public function testFileMethod()
	{
		$file = $this->app->file('test/test.jpg');
		$this->assertSame('file method', $file->test());
	}

	public function testFilesMethod()
	{
		$files = $this->app->page('test')->files();
		$this->assertSame('files method', $files->test());
	}
}
