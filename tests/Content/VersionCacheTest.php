<?php

namespace Kirby\Content;

use Kirby\Cms\File;
use Kirby\Cms\Language;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(VersionCache::class)]
class VersionCacheTest extends TestCase
{
	public function setUp(): void
	{
		parent::setUp();
		$this->setUpMultiLanguage();
	}

	public function testKeyForFile()
	{
		$parent = new Page(['slug' => 'test']);
		$model  = new File(['filename' => 'test.jpg', 'parent' => $parent]);
		$hash   = spl_object_hash($model->storage());

		$this->assertSame('file:test/test.jpg:latest:en:' . $hash, VersionCache::key($model->version(), Language::ensure()));
		$this->assertSame('file:test/test.jpg:latest:de:' . $hash, VersionCache::key($model->version(), Language::ensure('de')));
		$this->assertSame('file:test/test.jpg:changes:en:' . $hash, VersionCache::key($model->version('changes'), Language::ensure()));
	}

	public function testKeyForPage()
	{
		$model = new Page(['slug' => 'test']);
		$hash  = spl_object_hash($model->storage());

		$this->assertSame('page:test:latest:en:' . $hash, VersionCache::key($model->version(), Language::ensure()));
		$this->assertSame('page:test:latest:de:' . $hash, VersionCache::key($model->version(), Language::ensure('de')));
		$this->assertSame('page:test:changes:en:' . $hash, VersionCache::key($model->version('changes'), Language::ensure()));
	}

	public function testKeyForSite()
	{
		$model = new Site();
		$hash  = spl_object_hash($model->storage());

		$this->assertSame('site::latest:en:' . $hash, VersionCache::key($model->version(), Language::ensure()));
		$this->assertSame('site::latest:de:' . $hash, VersionCache::key($model->version(), Language::ensure('de')));
		$this->assertSame('site::changes:en:' . $hash, VersionCache::key($model->version('changes'), Language::ensure()));
	}

	public function testKeyForUser()
	{
		$model = new User(['id' => 'test']);
		$hash  = spl_object_hash($model->storage());

		$this->assertSame('user:test:latest:en:' . $hash, VersionCache::key($model->version(), Language::ensure()));
		$this->assertSame('user:test:latest:de:' . $hash, VersionCache::key($model->version(), Language::ensure('de')));
		$this->assertSame('user:test:changes:en:' . $hash, VersionCache::key($model->version('changes'), Language::ensure()));
	}

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
