<?php

namespace Kirby\Uuid;

use Generator;
use Kirby\Cms\Site;

/**
 * @coversDefaultClass \Kirby\Uuid\SiteUuid
 */
class SiteUuidTest extends TestCase
{
	/**
	 * @covers ::index
	 */
	public function testIndex()
	{
		$index = SiteUuid::index();
		$this->assertInstanceOf(Generator::class, $index);
		$this->assertInstanceOf(Site::class, $index->current());
		$this->assertSame(1, iterator_count($index));
	}

	/**
	 * @covers ::model
	 */
	public function testModel()
	{
		$site = $this->app->site();
		$this->assertSame($site, Uuid::for('site://')->model());
	}

	/**
	 * @covers ::populate
	 */
	public function testPopulate()
	{
		$uuid = $this->app->site()->uuid();
		$this->assertSame(true, $uuid->populate());
	}

	/**
	 * @covers ::toString
	 * @covers ::__toString
	 */
	public function testToString()
	{
		$uuid = $this->app->site()->uuid();
		$this->assertSame('site://', $uuid->toString());
		$this->assertSame('site://', (string)$uuid);
	}
}
