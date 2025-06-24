<?php

namespace Kirby\Content;

use Kirby\Cms\Language;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MemoryStorage::class)]
class MemoryStorageTest extends TestCase
{
	protected MemoryStorage $storage;

	public function assertCreateAndDelete(VersionId $versionId, Language $language): void
	{
		$this->storage->create($versionId, $language, []);

		$this->assertTrue($this->storage->exists($versionId, $language));

		$this->storage->delete($versionId, $language);

		$this->assertFalse($this->storage->exists($versionId, $language));
	}

	public function assertCreateAndRead(VersionId $versionId, Language $language): void
	{
		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		$this->storage->create($versionId, $language, $fields);

		$this->assertTrue($this->storage->exists($versionId, $language));
		$this->assertSame($fields, $this->storage->read($versionId, $language));
	}

	public function assertCreateAndUpdate(VersionId $versionId, Language $language): void
	{
		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		$this->storage->create($versionId, $language, []);

		$this->assertSame([], $this->storage->read($versionId, $language));

		$this->storage->update($versionId, $language, $fields);

		$this->assertTrue($this->storage->exists($versionId, $language));
		$this->assertSame($fields, $this->storage->read($versionId, $language));
	}

	public function setUpMultiLanguage(
		array|null $site = null
	): void {
		parent::setUpMultiLanguage(site: $site);

		$this->storage = new MemoryStorage($this->model);
	}

	public function setUpSingleLanguage(
		array|null $site = null
	): void {
		parent::setUpSingleLanguage(site: $site);

		$this->storage = new MemoryStorage($this->model);
	}

	public function testCreateAndReadChangesMultiLang(): void
	{
		$this->setUpMultiLanguage();

		$versionId = VersionId::changes();
		$language  = $this->app->language('en');

		$this->assertCreateAndRead($versionId, $language);
	}

	public function testCreateAndReadChangesSingleLang(): void
	{
		$this->setUpSingleLanguage();

		$versionId = VersionId::changes();
		$language  = Language::single();

		$this->assertCreateAndRead($versionId, $language);
	}

	public function testCreateAndReadLatestMultiLang(): void
	{
		$this->setUpMultiLanguage();

		$versionId = VersionId::latest();
		$language  = $this->app->language('en');

		$this->assertCreateAndRead($versionId, $language);
	}

	public function testCreateAndReadLatestSingleLang(): void
	{
		$this->setUpSingleLanguage();

		$versionId = VersionId::latest();
		$language  = Language::single();

		$this->assertCreateAndRead($versionId, $language);
	}

	public function testDeleteNonExisting(): void
	{
		$this->setUpSingleLanguage();

		$versionId = VersionId::latest();
		$language  = Language::single();

		$this->assertFalse($this->storage->exists($versionId, $language));

		// test idempotency
		$this->storage->delete($versionId, $language);

		$this->assertFalse($this->storage->exists($versionId, $language));
	}

	public function testDeleteChangesMultiLang(): void
	{
		$this->setUpMultiLanguage();

		$versionId = VersionId::changes();
		$language  = $this->app->language('en');

		$this->assertCreateAndDelete($versionId, $language);
	}

	public function testDeleteChangesSingleLang(): void
	{
		$this->setUpSingleLanguage();

		$versionId = VersionId::changes();
		$language  = Language::single();

		$this->assertCreateAndDelete($versionId, $language);
	}

	public function testDeleteLatestMultiLang(): void
	{
		$this->setUpMultiLanguage();

		$versionId = VersionId::latest();
		$language  = $this->app->language('en');

		$this->assertCreateAndDelete($versionId, $language);
	}

	public function testDeleteLatestSingleLang(): void
	{
		$this->setUpSingleLanguage();

		$versionId = VersionId::latest();
		$language  = Language::single();

		$this->assertCreateAndDelete($versionId, $language);
	}

	public function testExistsMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$versionId = VersionId::latest();

		$this->assertFalse($this->storage->exists($versionId, $this->app->language('en')));
		$this->assertFalse($this->storage->exists($versionId, $this->app->language('de')));

		$this->storage->create($versionId, $this->app->language('en'), []);
		$this->storage->create($versionId, $this->app->language('de'), []);

		$this->assertTrue($this->storage->exists($versionId, $this->app->language('en')));
		$this->assertTrue($this->storage->exists($versionId, $this->app->language('de')));
	}

	public function testExistsSingleLanguage(): void
	{
		$this->setUpSingleLanguage();

		$versionId = VersionId::latest();
		$language  = Language::single();

		$this->assertFalse($this->storage->exists($versionId, $language));

		$this->storage->create($versionId, $language, []);

		$this->assertTrue($this->storage->exists($versionId, $language));
	}

	public function testExistsNoneExistingMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$this->assertFalse($this->storage->exists(VersionId::changes(), $this->app->language('en')));
		$this->assertFalse($this->storage->exists(VersionId::changes(), $this->app->language('de')));
	}

	public function testExistsNoneExistingSingleLanguage(): void
	{
		$this->setUpSingleLanguage();

		$this->assertFalse($this->storage->exists(VersionId::changes(), Language::single()));
	}

	public function testModifiedNoneExistingMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$this->assertNull($this->storage->modified(VersionId::changes(), $this->app->language('en')));
		$this->assertNull($this->storage->modified(VersionId::latest(), $this->app->language('en')));
	}

	public function testModifiedNoneExistingSingleLanguage(): void
	{
		$this->setUpSingleLanguage();

		$this->assertNull($this->storage->modified(VersionId::changes(), Language::single()));
		$this->assertNull($this->storage->modified(VersionId::latest(), Language::single()));
	}

	public function testModifiedSomeExistingMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$changes  = VersionId::changes();
		$language = $this->app->language('en');

		$this->storage->create($changes, $language, []);

		$this->assertIsInt($this->storage->modified($changes, $language));
		$this->assertNull($this->storage->modified(VersionId::latest(), $language));
	}

	public function testModifiedSomeExistingSingleLanguage(): void
	{
		$this->setUpSingleLanguage();

		$changes  = VersionId::changes();
		$language = Language::single();

		$this->storage->create($changes, $language, []);

		$this->assertIsInt($this->storage->modified($changes, $language));
		$this->assertNull($this->storage->modified(VersionId::latest(), $language));
	}

	public function testMoveToTheSameStorageLocation(): void
	{
		$this->setUpSingleLanguage();

		$content   = ['title' => 'Test'];
		$versionId = VersionId::latest();
		$language  = Language::single();

		// create some content to move
		$this->storage->create($versionId, $language, $content);

		$this->assertTrue($this->storage->exists($versionId, $language));
		$this->assertSame($content, $this->storage->read($versionId, $language));

		$this->storage->move(
			$versionId,
			$language,
			$versionId,
			$language
		);

		$this->assertTrue($this->storage->exists($versionId, $language));
		$this->assertSame($content, $this->storage->read($versionId, $language), 'The content should still be the same');
	}

	public function testMoveToTheSameStorageLocationWithAnotherStorageInstance(): void
	{
		$this->setUpSingleLanguage();

		$content   = ['title' => 'Test'];
		$versionId = VersionId::latest();
		$language  = Language::single();
		$storage   = new MemoryStorage($this->model);

		// create some content to move
		$this->storage->create($versionId, $language, $content);

		$this->assertTrue($this->storage->exists($versionId, $language));
		$this->assertSame($content, $this->storage->read($versionId, $language));

		$this->storage->move(
			$versionId,
			$language,
			$versionId,
			$language,
			$storage
		);

		$this->assertFalse($this->storage->exists($versionId, $language), 'The old storage entry should be gone now');

		$this->assertTrue($storage->exists($versionId, $language));
		$this->assertSame($content, $storage->read($versionId, $language));
	}

	public function testTouchMultiLang(): void
	{
		$this->setUpMultiLanguage();

		$versionId = VersionId::changes();
		$language  = $this->app->language('en');

		$time = time();

		$this->storage->create($versionId, $language, []);
		$this->storage->touch($versionId, $language);

		$this->assertGreaterThanOrEqual($time, $this->storage->modified($versionId, $language));
	}

	public function testTouchSingleLang(): void
	{
		$this->setUpSingleLanguage();

		$versionId = VersionId::changes();
		$language  = Language::single();

		$time = time();

		$this->storage->create($versionId, $language, []);
		$this->storage->touch($versionId, $language);

		$this->assertGreaterThanOrEqual($time, $this->storage->modified($versionId, $language));
	}

	public function testUpdateMultiLang(): void
	{
		$this->setUpMultiLanguage();

		$versionId = VersionId::changes();
		$language  = $this->app->language('en');

		$this->assertCreateAndUpdate($versionId, $language);
	}

	public function testUpdateSingleLang(): void
	{
		$this->setUpSingleLanguage();

		$versionId = VersionId::changes();
		$language  = Language::single();

		$this->assertCreateAndUpdate($versionId, $language);
	}
}
