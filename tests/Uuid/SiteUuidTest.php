<?php

namespace Kirby\Uuid;

use Generator;

/**
 * @coversDefaultClass \Kirby\Uuid\SiteUuid
 */
class SiteUuidTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Uuid.SiteUuid';

	/**
	 * @covers ::id
	 */
	public function testId()
	{
		$site = $this->app->site();
		$this->assertSame('', $site->uuid()->id());
	}

	/**
	 * @covers ::index
	 */
	public function testIndex()
	{
		$index = SiteUuid::index();
		$this->assertInstanceOf(Generator::class, $index);
		$this->assertIsSite($index->current());
		$this->assertSame(1, iterator_count($index));
	}

	/**
	 * @covers ::model
	 */
	public function testModel()
	{
		$site = $this->app->site();
		$this->assertIsSite($site, Uuid::for('site://')->model());
	}

	/**
	 * @covers ::populate
	 */
	public function testPopulate()
	{
		$uuid = $this->app->site()->uuid();
		$this->assertTrue($uuid->populate());
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
