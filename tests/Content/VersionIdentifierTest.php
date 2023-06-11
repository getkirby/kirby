<?php

namespace Kirby\Content;

use Kirby\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Kirby\Content\VersionIdentifier
 */
class VersionIdentifierTest extends TestCase
{
	/**
	 * @covers ::__construct
	 * @covers ::__toString
	 * @covers ::type
	 */
	public function testMethods()
	{
		$versionIdentifier = new VersionIdentifier('published');

		$this->assertSame('published', (string)$versionIdentifier);
		$this->assertSame('published', $versionIdentifier->type());
	}

	/**
	 * @covers ::changes
	 * @covers ::published
	 */
	public function testShorthands()
	{
		$this->assertSame('changes', VersionIdentifier::changes()->type());
		$this->assertSame('published', VersionIdentifier::published()->type());
	}

	/**
	 * @covers ::__construct
	 */
	public function testTypeInvalid()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid version type "foobar"');

		new VersionIdentifier('foobar');
	}
}
