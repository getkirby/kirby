<?php

namespace Kirby\Content;

use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\User;

/**
 * @coversDefaultClass Kirby\Content\ContentStorageHandler
 * @covers ::__construct
 */
class ContentStorageHandlerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Content.ContentStorageHandler';

	/**
	 * @covers ::all
	 */
	public function testAllMultiLanguageForFile()
	{
		$this->setUpMultiLanguage();

		$handler = new TestContentStorageHandler(
			new File([
				'filename' => 'test.jpg',
				'parent'   => new Page(['slug' => 'test'])
			])
		);

		$versions = iterator_to_array($handler->all(), false);

		// The TestContentStorage handler always returns true
		// for every version and language. Thus there should be
		// 2 versions for every language.
		//
		// article.en.txt
		// article.de.txt
		// _changes/article.en.txt
		// _changes/article.de.txt
		$this->assertCount(4, $versions);
	}

	/**
	 * @covers ::all
	 */
	public function testAllSingleLanguageForFile()
	{
		$this->setUpSingleLanguage();

		$handler = new TestContentStorageHandler(
			new File([
				'filename' => 'test.jpg',
				'parent'   => new Page(['slug' => 'test'])
			])
		);

		$versions = iterator_to_array($handler->all(), false);

		// The TestContentStorage handler always returns true
		// for every version and language. Thus there should be
		// 2 versions in a single language installation.
		//
		// article.txt
		// _changes/article.txt
		$this->assertCount(2, $versions);
	}

	/**
	 * @covers ::all
	 */
	public function testAllMultiLanguageForPage()
	{
		$this->setUpMultiLanguage();

		$handler = new TestContentStorageHandler(
			new Page(['slug' => 'test', 'isDraft' => false])
		);

		$versions = iterator_to_array($handler->all(), false);

		// A page that's not in draft mode can have published and changes versions
		// and thus should have changes and published for every language
		$this->assertCount(4, $versions);
	}

	/**
	 * @covers ::all
	 */
	public function testAllMultiLanguageForPageDraft()
	{
		$this->setUpMultiLanguage();

		$handler = new TestContentStorageHandler(
			new Page(['slug' => 'test', 'isDraft' => true])
		);

		$versions = iterator_to_array($handler->all(), false);

		// A draft page has only changes and thus should only have
		// a changes for every language, but no published versions
		$this->assertCount(2, $versions);
	}

	/**
	 * @covers ::all
	 */
	public function testAllSingleLanguageForPage()
	{
		$this->setUpSingleLanguage();

		$handler = new TestContentStorageHandler(
			new Page(['slug' => 'test', 'isDraft' => false])
		);

		$versions = iterator_to_array($handler->all(), false);

		// A page that's not in draft mode can have published and changes versions
		$this->assertCount(2, $versions);
	}

	/**
	 * @covers ::all
	 */
	public function testAllSingleLanguageForPageDraft()
	{
		$this->setUpSingleLanguage();

		$handler = new TestContentStorageHandler(
			new Page(['slug' => 'test', 'isDraft' => true])
		);

		$versions = iterator_to_array($handler->all(), false);

		// A draft page has only changes and thus should only have
		// a single version in a single language installation
		$this->assertCount(1, $versions);
	}

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
