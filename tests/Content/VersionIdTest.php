<?php

namespace Kirby\Content;

use Kirby\Exception\InvalidArgumentException;
use Kirby\TestCase;

/**
 * @coversDefaultClass Kirby\Content\VersionId
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
	 * @covers ::__construct
	 */
	public function testConstructWithInvalidId()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid Version ID');

		new VersionId('foo');
	}

	/**
	 * @covers ::from
	 * @covers ::value
	 */
	public function testFromString()
	{
		$version = VersionId::from('published');
		$this->assertSame('published', $version->value());
	}

	/**
	 * @covers ::from
	 * @covers ::value
	 */
	public function testFromInstance()
	{
		$version = VersionId::from(VersionId::published());
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
	 * @covers ::list
	 */
	public function testList()
	{
		$list = VersionId::list();

		$this->assertCount(2, $list);
		$this->assertSame('published', $list[0]->value());
		$this->assertSame('changes', $list[1]->value());
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

	/**
	 * @covers ::__toString
	 */
	public function testToString()
	{
		$this->assertSame('published', (string)VersionId::published());
		$this->assertSame('changes', (string)VersionId::changes());
	}
}
