<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class FileUpdateTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.FileUpdate';

	public function testUpdate(): void
	{
		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $this->app->site()
		]);

		$this->assertNull($file->caption()->value());
		$this->assertNull($file->template());

		$file = $file->update([
			'caption' => $caption = 'test',
			'template' => $template = 'test'
		]);

		$this->assertSame($caption, $file->caption()->value());
		$this->assertSame($template, $file->template());
	}

	public function testUpdateHooks(): void
	{
		$calls   = 0;
		$phpunit = $this;
		$input   = [
			'title' => 'Test'
		];

		$this->app = $this->app->clone([
			'hooks' => [
				'file.update:before' => function (File $file, $values, $strings) use ($phpunit, $input, &$calls) {
					$phpunit->assertIsFile($file);
					$phpunit->assertNull($file->title()->value());
					$phpunit->assertSame($input, $values);
					$phpunit->assertSame($input, $strings);
					$calls++;
				},
				'file.update:after' => function (File $newFile, File $oldFile) use ($phpunit, &$calls) {
					$phpunit->assertIsFile($newFile);
					$phpunit->assertIsFile($oldFile);
					$phpunit->assertSame('Test', $newFile->title()->value());
					$phpunit->assertNull($oldFile->title()->value());
					$calls++;
				},
			]
		]);

		$this->app->impersonate('kirby');

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $this->app->site()
		]);

		$file->update($input);

		$this->assertSame(2, $calls);
	}
}
