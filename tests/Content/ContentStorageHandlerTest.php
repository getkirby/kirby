<?php

namespace Kirby\Content;

use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use Kirby\TestCase;

/**
 * @coversDefaultClass Kirby\Content\ContentStorageHandler
 * @covers ::__construct
 */
class ContentStorageHandlerTest extends TestCase
{
	/**
	 * @covers ::dynamicVersions
	 */
	public function testDynamicVersionsForFile()
	{
		$handler = new TestContentStorageHandler(
			new File([
				'filename' => 'test.jpg',
				'parent'   => new Page(['slug' => 'test'])
			])
		);

		$versions = $handler->dynamicVersions();

		$this->assertCount(2, $versions);
		$this->assertSame(VersionId::CHANGES, $versions[0]->value());
		$this->assertSame(VersionId::PUBLISHED, $versions[1]->value());
	}

	/**
	 * @covers ::dynamicVersions
	 */
	public function testDynamicVersionsForPage()
	{
		$handler = new TestContentStorageHandler(
			new Page(['slug' => 'test', 'isDraft' => false])
		);

		$versions = $handler->dynamicVersions();

		$this->assertCount(2, $versions);
		$this->assertSame(VersionId::CHANGES, $versions[0]->value());
		$this->assertSame(VersionId::PUBLISHED, $versions[1]->value());
	}

	/**
	 * @covers ::dynamicVersions
	 */
	public function testDynamicVersionsForPageDraft()
	{
		$handler = new TestContentStorageHandler(
			new Page(['slug' => 'test', 'isDraft' => true])
		);

		$versions = $handler->dynamicVersions();

		$this->assertCount(1, $versions);
		$this->assertSame(VersionId::CHANGES, $versions[0]->value());
	}

	/**
	 * @covers ::dynamicVersions
	 */
	public function testDynamicVersionsForSite()
	{
		$handler = new TestContentStorageHandler(
			new Site()
		);

		$versions = $handler->dynamicVersions();

		$this->assertCount(2, $versions);
		$this->assertSame(VersionId::CHANGES, $versions[0]->value());
		$this->assertSame(VersionId::PUBLISHED, $versions[1]->value());
	}

	/**
	 * @covers ::dynamicVersions
	 */
	public function testDynamicVersionsForUser()
	{
		$handler = new TestContentStorageHandler(
			new User(['email' => 'test@getkirby.com'])
		);

		$versions = $handler->dynamicVersions();

		$this->assertCount(2, $versions);
		$this->assertSame(VersionId::CHANGES, $versions[0]->value());
		$this->assertSame(VersionId::PUBLISHED, $versions[1]->value());
	}
}
