<?php

namespace Kirby\Content;

use Kirby\Cms\Language;
use Kirby\Cms\Page;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(VersionMemoryCache::class)]
class VersionMemoryCacheTest extends TestCase
{
	public function setUp(): void
	{
		parent::setUp();
		$this->setUpMultiLanguage();
	}

	public function testNoConflictsWithVersionCache()
	{
		$model    = new Page(['slug' => 'test']);
		$version  = $model->version();
		$language = Language::ensure();

		$this->assertNull(VersionMemoryCache::get($version, $language));
		$this->assertNull(VersionCache::get($version, $language));

		VersionMemoryCache::set($version, $language, $fieldsInMemory = [
			'foo' => 'bar in memory'
		]);

		VersionCache::set($version, $language, $fieldsInMain = [
			'foo' => 'bar'
		]);

		$this->assertSame($fieldsInMemory, VersionMemoryCache::get($version, $language));
		$this->assertSame($fieldsInMain, VersionCache::get($version, $language));

		VersionMemoryCache::remove($version, $language);
		VersionCache::remove($version, $language);

		$this->assertNull(VersionMemoryCache::get($version, $language));
		$this->assertNull(VersionCache::get($version, $language));
	}
}
