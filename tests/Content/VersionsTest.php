<?php

namespace Kirby\Content;

use Kirby\Cms\Page;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Versions::class)]
class VersionsTest extends TestCase
{
	public function testLoad(): void
	{
		$model    = new Page(['slug' => 'test']);
		$versions = Versions::load($model);

		$this->assertCount(2, $versions);
		$this->assertSame('changes', (string)$versions->first()->id());
		$this->assertSame('latest', (string)$versions->last()->id());
	}
}
