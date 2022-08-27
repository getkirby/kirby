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
	 * @covers ::populate
	 */
	public function testPopulate()
	{
		$uuid = $this->app->site()->uuid();
		$this->assertSame(true, $uuid->populate());
	}

	/**
	 * @covers ::render
	 * @covers ::__toString
	 */
	public function testRender()
	{
		$uuid = $this->app->site()->uuid();
		$this->assertSame('site://', $uuid->render());
		$this->assertSame('site://', (string)$uuid);
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolve()
	{
		$site = $this->app->site();
		$this->assertSame($site, Uuid::for('site://')->resolve());
	}
}
