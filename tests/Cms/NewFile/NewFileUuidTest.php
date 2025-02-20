<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class NewFileUuidTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewFileUuid';

	public function testPermalink()
	{
		$page = new Page(['slug' => 'test' ]);
		$file = new File([
			'filename' => 'test.pdf',
			'content'  => ['uuid' => 'my-file-uuid'],
			'parent'   => $page
		]);

		$this->assertSame('//@/file/my-file-uuid', $file->permalink());
	}
}
