<?php

namespace Kirby\Content;

use Kirby\Cms\File;
use Kirby\Cms\Page;
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
	 * @covers ::default
	 */
	public function testDefault()
	{
		$draft   = new Page(['slug' => 'test', 'isDraft' => true]);
		$version = VersionId::default($draft);

		$this->assertTrue($version->is(VersionId::CHANGES));

		$unlisted = new Page(['slug' => 'test', 'isDraft' => false]);
		$version  = VersionId::default($unlisted);

		$this->assertTrue($version->is(VersionId::PUBLISHED));

		$file    = new File(['filename' => 'foo.jpg', 'parent' => $unlisted]);
		$version = VersionId::default($file);

		$this->assertTrue($version->is(VersionId::PUBLISHED));
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

	/**
	 * @covers ::__toString
	 */
	public function testToString()
	{
		$this->assertSame('published', (string)VersionId::published());
		$this->assertSame('changes', (string)VersionId::changes());
	}
}
