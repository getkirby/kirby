<?php

namespace Kirby\Cms;

use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class NewFileCopyTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewFileCopy';

	public function testCopyRenewUuid()
	{
		// create dumy file
		F::write($source = static::TMP . '/original.md', '# Foo');

		$file = File::create([
			'filename' => 'test.md',
			'source'   => $source,
			'parent'   => new Page(['slug' => 'test'])
		]);

		$oldUuid = $file->content()->get('uuid')->value();
		$this->assertIsString($oldUuid);

		$target = new Page(['slug' => 'newly']);
		$copy   = $file->copy($target);

		$newUuid = $copy->content()->get('uuid')->value();
		$this->assertIsString($newUuid);
		$this->assertNotSame($oldUuid, $newUuid);
	}
}
