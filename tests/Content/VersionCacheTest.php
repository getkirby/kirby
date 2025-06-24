<?php

namespace Kirby\Content;

use Kirby\Cms\File;
use Kirby\Cms\Language;
use Kirby\Cms\Page;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(VersionCache::class)]
class VersionCacheTest extends TestCase
{
	public function setUp(): void
	{
		parent::setUp();
		$this->setUpMultiLanguage();
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

		VersionCache::set($version, $language, $fields = [
			'foo' => 'baz'
		]);

		$this->assertSame($fields, VersionCache::get($version, $language));

		VersionCache::remove($version, $language);

		$this->assertNull(VersionCache::get($version, $language));
	}

	public function testGetSetAndRemoveWithDifferentModels()
	{
		$page = new Page(['slug' => 'test1']);
		$file = new File(['filename' => 'test2.jpg', 'parent' => $page]);

		$versionPage = $page->version();
		$versionFile = $file->version();

		$language = Language::ensure();

		$this->assertNull(VersionCache::get($versionPage, $language));
		$this->assertNull(VersionCache::get($versionFile, $language));

		VersionCache::set($versionPage, $language, $fieldsPage = [
			'foo' => 'bar'
		]);

		VersionCache::set($versionFile, $language, $fieldsFile = [
			'foo' => 'baz'
		]);

		$this->assertSame($fieldsPage, VersionCache::get($versionPage, $language));
		$this->assertSame($fieldsFile, VersionCache::get($versionFile, $language));

		VersionCache::remove($versionPage, $language);

		$this->assertNull(VersionCache::get($versionPage, $language));
		$this->assertSame($fieldsFile, VersionCache::get($versionFile, $language));

		VersionCache::remove($versionFile, $language);

		$this->assertNull(VersionCache::get($versionPage, $language));
		$this->assertNull(VersionCache::get($versionFile, $language));
	}

	public function testGetSetAndRemoveWithDifferentStorage()
	{
		$model    = new Page(['slug' => 'test']);
		$version  = $model->version();
		$language = Language::ensure();
		$fields   = [
			'foo' => 'bar'
		];

		$this->assertNull(VersionCache::get($version, $language));

		VersionCache::set($version, $language, $fields);

		$this->assertSame($fields, VersionCache::get($version, $language));

		$model->changeStorage(MemoryStorage::class);

		$this->assertSame($fields, VersionCache::get($version, $language));

		VersionCache::remove($version, $language);

		$this->assertNull(VersionCache::get($version, $language));
	}

	public function testGetSetAndRemoveWithClonedModel()
	{
		$model    = new Page(['slug' => 'test']);
		$version  = $model->version();
		$language = Language::ensure();
		$fields   = [
			'foo' => 'bar'
		];

		$this->assertNull(VersionCache::get($version, $language));

		VersionCache::set($version, $language, $fields);

		$this->assertSame($fields, VersionCache::get($version, $language));

		$clonedModel = $model->clone();
		$clonedVersion = $clonedModel->version();

		$this->assertNull(VersionCache::get($clonedVersion, $language));
		$this->assertSame($fields, VersionCache::get($version, $language));

		VersionCache::remove($version, $language);

		$this->assertNull(VersionCache::get($version, $language));

		VersionCache::remove($clonedVersion, $language);

		$this->assertNull(VersionCache::get($clonedVersion, $language));
	}

	public function testGetSetAndRemoveWithDifferentLanguages()
	{
		$model    = new Page(['slug' => 'test']);
		$version  = $model->version();
		$en       = Language::ensure('en');
		$de       = Language::ensure('de');
		$fieldsEn = [
			'foo' => 'bar'
		];
		$fieldsDe = [
			'foo' => 'baz'
		];

		$this->assertNull(VersionCache::get($version, $en));
		$this->assertNull(VersionCache::get($version, $de));

		VersionCache::set($version, $en, $fieldsEn);
		VersionCache::set($version, $de, $fieldsDe);

		$this->assertSame($fieldsEn, VersionCache::get($version, $en));
		$this->assertSame($fieldsDe, VersionCache::get($version, $de));

		VersionCache::remove($version, $en);

		$this->assertNull(VersionCache::get($version, $en));
		$this->assertSame($fieldsDe, VersionCache::get($version, $de));

		VersionCache::remove($version, $de);

		$this->assertNull(VersionCache::get($version, $en));
		$this->assertNull(VersionCache::get($version, $de));
	}

	public function testGetSetAndRemoveWithDifferentVersions()
	{
		$model        = new Page(['slug' => 'test']);
		$latest       = $model->version('latest');
		$changes      = $model->version('changes');
		$language     = Language::ensure();
		$fieldsLatest = [
			'foo' => 'bar'
		];
		$fieldsChanges = [
			'foo' => 'baz'
		];

		$this->assertNull(VersionCache::get($latest, $language));
		$this->assertNull(VersionCache::get($changes, $language));

		VersionCache::set($latest, $language, $fieldsLatest);
		VersionCache::set($changes, $language, $fieldsChanges);

		$this->assertSame($fieldsLatest, VersionCache::get($latest, $language));
		$this->assertSame($fieldsChanges, VersionCache::get($changes, $language));

		VersionCache::remove($latest, $language);

		$this->assertNull(VersionCache::get($latest, $language));
		$this->assertSame($fieldsChanges, VersionCache::get($changes, $language));

		VersionCache::remove($changes, $language);

		$this->assertNull(VersionCache::get($latest, $language));
		$this->assertNull(VersionCache::get($changes, $language));
	}

	public function testReset()
	{
		$model    = new Page(['slug' => 'test']);
		$version  = $model->version();
		$language = Language::ensure();
		$fields   = [
			'foo' => 'bar'
		];

		VersionCache::set($version, $language, $fields);

		$this->assertSame($fields, VersionCache::get($version, $language));

		VersionCache::reset();

		$this->assertNull(VersionCache::get($version, $language));
	}

}
