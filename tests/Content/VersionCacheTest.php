<?php

namespace Kirby\Content;

use Kirby\Cms\File;
use Kirby\Cms\Language;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\User;

/**
 * @coversDefaultClass \Kirby\Content\VersionCache
 */
class VersionCacheTest extends TestCase
{
	public function setUp(): void
	{
		parent::setUp();
		$this->setUpMultiLanguage();
	}

	/**
	 * @covers ::key
	 */
	public function testKeyForPage()
	{
		$model = new Page(['slug' => 'test']);

		$this->assertSame('page://test?version=latest&language=en', VersionCache::key($model->version(), Language::ensure()));
		$this->assertSame('page://test?version=latest&language=de', VersionCache::key($model->version(), Language::ensure('de')));
		$this->assertSame('page://test?version=changes&language=en', VersionCache::key($model->version('changes'), Language::ensure()));
	}

	/**
	 * @covers ::key
	 */
	public function testKeyForPageFile()
	{
		$parent = new Page(['slug' => 'test']);
		$model  = new File(['filename' => 'test.jpg', 'parent' => $parent]);

		$this->assertSame('page://test/test.jpg?version=latest&language=en', VersionCache::key($model->version(), Language::ensure()));
		$this->assertSame('page://test/test.jpg?version=latest&language=de', VersionCache::key($model->version(), Language::ensure('de')));
		$this->assertSame('page://test/test.jpg?version=changes&language=en', VersionCache::key($model->version('changes'), Language::ensure()));
	}

	/**
	 * @covers ::key
	 */
	public function testKeyForSite()
	{
		$model = new Site();
		$key   = VersionCache::key($model->version(), Language::ensure());

		$this->assertSame('site://?version=latest&language=en', VersionCache::key($model->version(), Language::ensure()));
		$this->assertSame('site://?version=latest&language=de', VersionCache::key($model->version(), Language::ensure('de')));
		$this->assertSame('site://?version=changes&language=en', VersionCache::key($model->version('changes'), Language::ensure()));
	}

	/**
	 * @covers ::key
	 */
	public function testKeyForSiteFile()
	{
		$parent = new Site();
		$model  = new File(['filename' => 'test.jpg', 'parent' => $parent]);
		$key    = VersionCache::key($model->version(), Language::ensure());

		$this->assertSame('site://test.jpg?version=latest&language=en', VersionCache::key($model->version(), Language::ensure()));
		$this->assertSame('site://test.jpg?version=latest&language=de', VersionCache::key($model->version(), Language::ensure('de')));
		$this->assertSame('site://test.jpg?version=changes&language=en', VersionCache::key($model->version('changes'), Language::ensure()));
	}

	/**
	 * @covers ::key
	 */
	public function testKeyForUser()
	{
		$model = new User(['id' => 'test']);
		$key   = VersionCache::key($model->version(), Language::ensure());

		$this->assertSame('user://test?version=latest&language=en', VersionCache::key($model->version(), Language::ensure()));
		$this->assertSame('user://test?version=latest&language=de', VersionCache::key($model->version(), Language::ensure('de')));
		$this->assertSame('user://test?version=changes&language=en', VersionCache::key($model->version('changes'), Language::ensure()));
	}

	/**
	 * @covers ::key
	 */
	public function testKeyForUserFile()
	{
		$parent = new User(['id' => 'test']);
		$model  = new File(['filename' => 'test.jpg', 'parent' => $parent]);
		$key    = VersionCache::key($model->version(), Language::ensure());

		$this->assertSame('user://test/test.jpg?version=latest&language=en', VersionCache::key($model->version(), Language::ensure()));
		$this->assertSame('user://test/test.jpg?version=latest&language=de', VersionCache::key($model->version(), Language::ensure('de')));
		$this->assertSame('user://test/test.jpg?version=changes&language=en', VersionCache::key($model->version('changes'), Language::ensure()));
	}

	/**
	 * @covers ::get
	 * @covers ::set
	 * @covers ::remove
	 */
	public function testGetSetAndRemove()
	{
		$model    = new Page(['slug' => 'test']);
		$version  = $model->version();
		$language = Language::ensure();
		$fields   = [
			'foo' => 'bar'
		];

		$this->assertNull(VersionCache::get($version, $language));

		VersionCache::set($version, $language, [
			'foo' => 'bar'
		]);

		$this->assertSame($fields, VersionCache::get($version, $language));

		VersionCache::remove($version, $language);

		$this->assertNull(VersionCache::get($version, $language));
	}
}
