<?php

namespace Kirby\Content;

use Kirby\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Kirby\Content\VersionTemplate
 */
class VersionTemplateTest extends TestCase
{
	/**
	 * @covers ::__construct
	 * @covers ::__toString
	 * @covers ::type
	 */
	public function testMethods()
	{
		$versionTemplate = new VersionTemplate('published');

		$this->assertSame('published', (string)$versionTemplate);
		$this->assertSame('published', $versionTemplate->type());
	}

	/**
	 * @covers ::changes
	 * @covers ::published
	 */
	public function testShorthands()
	{
		$this->assertSame('changes', VersionTemplate::changes()->type());
		$this->assertSame('published', VersionTemplate::published()->type());
	}

	/**
	 * @covers ::__construct
	 */
	public function testTypeInvalid()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid version type "foobar"');

		new VersionTemplate('foobar');
	}
}
