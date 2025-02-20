<?php

namespace Kirby\Cms;

use Kirby\Cms\NewFile as File;
use Kirby\Cms\NewPage as Page;
use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class NewFileSaveTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewFileSave';

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

		$file = $file->clone(['content' => ['caption' => 'save']])->save();

		$this->assertFileExists($file->version('latest')->contentFile('default'));
	}
}
