<?php

namespace Kirby\Cms;

use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class FileCopyTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.FileCopy';

	public function testCopyRenewUuid(): void
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
