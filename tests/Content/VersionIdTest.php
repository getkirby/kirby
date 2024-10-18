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
	 * @covers ::all
	 */
	public function testAll()
	{
		$list = VersionId::all();

		$this->assertCount(2, $list);
		$this->assertSame('latest', $list[0]->value());
		$this->assertSame('changes', $list[1]->value());
	}

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
		$version = VersionId::from('latest');
		$this->assertSame('latest', $version->value());
	}

	/**
	 * @covers ::from
	 * @covers ::value
	 */
	public function testFromInstance()
	{
		$version = VersionId::from(VersionId::latest());
		$this->assertSame('latest', $version->value());
	}

	/**
	 * @covers ::is
	 */
	public function testIs()
	{
		$version = VersionId::latest();

		$this->assertTrue($version->is('latest'));
		$this->assertTrue($version->is(VersionId::LATEST));
		$this->assertFalse($version->is('something-else'));
		$this->assertFalse($version->is(VersionId::CHANGES));
	}

	/**
	 * @covers ::latest
	 * @covers ::value
	 */
	public function testLatest()
	{
		$version = VersionId::latest();

		$this->assertSame('latest', $version->value());
	}

	/**
	 * @covers ::__toString
	 */
	public function testToString()
	{
		$this->assertSame('latest', (string)VersionId::latest());
		$this->assertSame('changes', (string)VersionId::changes());
	}
}
