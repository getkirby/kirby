<?php

namespace Kirby\Content;

use Kirby\TestCase;

/**
 * @coversDefaultClass Kirby\Content\VersionId
 * @covers ::__construct
 */
class VersionIdTest extends TestCase
{
	/**
	 * @covers ::changes
	 * @covers ::value
	 */
	public function testChanges()
	{
		$version = VersionId::changes();

		$this->assertSame('changes', $version->value());
	}

	/**
	 * @covers ::from
	 * @covers ::value
	 */
	public function testFrom()
	{
		$version = VersionId::from('published');

		$this->assertSame('published', $version->value());
	}

	/**
	 * @covers ::is
	 */
	public function testIs()
	{
		$version = VersionId::published();

		$this->assertTrue($version->is('published'));
		$this->assertTrue($version->is(VersionId::PUBLISHED));
		$this->assertFalse($version->is('something-else'));
		$this->assertFalse($version->is(VersionId::CHANGES));
	}

	/**
	 * @covers ::published
	 * @covers ::value
	 */
	public function testPublished()
	{
		$version = VersionId::published();

		$this->assertSame('published', $version->value());
	}
}
