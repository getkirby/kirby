<?php

namespace Kirby\Cms;

use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class FileSaveTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.FileSave';

	public function testSave(): void
	{
		$page = new Page(['slug' => 'test']);
		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $page,
		]);

		// create an empty dummy file
		F::write($file->root(), '');

		$this->assertFileExists($file->root());
		$this->assertFileDoesNotExist($file->version('latest')->contentFile('default'));

		// old test: with our new memory storage architecture, clone won't work
		// here because the file is not stored in the storage once we use the content
		// setter to inject the content. This is correct, but the test fails because of that.

		// $file = $file->clone(['content' => ['caption' => 'save']])->save();

		// new test: use save directly to save the content
		$file = $file->save(['caption' => 'save']);

		$this->assertFileExists($file->version('latest')->contentFile('default'));
	}
}
