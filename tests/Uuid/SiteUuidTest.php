<?php

namespace Kirby\Uuid;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SiteUuid::class)]
class SiteUuidTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Uuid.SiteUuid';

	public function testId()
	{
		$site = $this->app->site();
		$this->assertSame('', $site->uuid()->id());
	}

	public function testIndex()
	{
		$index = SiteUuid::index();
		$this->assertInstanceOf(Generator::class, $index);
		$this->assertIsSite($index->current());
		$this->assertSame(1, iterator_count($index));
	}

	public function testModel()
	{
		$site = $this->app->site();
		$this->assertIsSite($site, Uuid::for('site://')->model());
	}

	public function testPopulate()
	{
		$uuid = $this->app->site()->uuid();
		$this->assertTrue($uuid->populate());
	}

	public function testToString()
	{
		$uuid = $this->app->site()->uuid();
		$this->assertSame('site://', $uuid->toString());
		$this->assertSame('site://', (string)$uuid);
	}
}
