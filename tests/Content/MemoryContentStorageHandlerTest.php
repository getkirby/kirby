<?php

namespace Kirby\Content;

use Kirby\Cms\Language;

/**
 * @coversDefaultClass Kirby\Content\MemoryContentStorageHandler
 * @covers ::__construct
 */
class MemoryContentStorageHandlerTest extends TestCase
{
	protected $storage;

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

	public function setUpMultiLanguage(): void
	{
		parent::setUpMultiLanguage();

		$this->storage = new MemoryContentStorageHandler($this->model);
	}

	public function setUpSingleLanguage(): void
	{
		parent::setUpSingleLanguage();

		$this->storage = new MemoryContentStorageHandler($this->model);
	}

	/**
	 * @covers ::create
	 * @covers ::read
	 */
	public function testCreateAndReadChangesMultiLang()
	{
		$this->setUpMultiLanguage();

		$versionId = VersionId::changes();
		$language  = $this->app->language('en');

		$this->assertCreateAndRead($versionId, $language);
	}

	/**
	 * @covers ::create
	 * @covers ::read
	 */
	public function testCreateAndReadChangesSingleLang()
	{
		$this->setUpSingleLanguage();

		$versionId = VersionId::changes();
		$language  = Language::single();

		$this->assertCreateAndRead($versionId, $language);
	}

	/**
	 * @covers ::create
	 * @covers ::read
	 */
	public function testCreateAndReadPublishedMultiLang()
	{
		$this->setUpMultiLanguage();

		$versionId = VersionId::published();
		$language  = $this->app->language('en');

		$this->assertCreateAndRead($versionId, $language);
	}

	/**
	 * @covers ::create
	 * @covers ::read
	 */
	public function testCreateAndReadPublishedSingleLang()
	{
		$this->setUpSingleLanguage();

		$versionId = VersionId::published();
		$language  = Language::single();

		$this->assertCreateAndRead($versionId, $language);
	}

	/**
	 * @covers ::delete
	 */
	public function testDeleteNonExisting()
	{
		$this->setUpSingleLanguage();

		$versionId = VersionId::published();
		$language  = Language::single();

		$this->assertFalse($this->storage->exists($versionId, $language));

		// test idempotency
		$this->storage->delete($versionId, $language);

		$this->assertFalse($this->storage->exists($versionId, $language));
	}

	/**
	 * @covers ::delete
	 */
	public function testDeleteChangesMultiLang()
	{
		$this->setUpMultiLanguage();

		$versionId = VersionId::changes();
		$language  = $this->app->language('en');

		$this->assertCreateAndDelete($versionId, $language);
	}

	/**
	 * @covers ::delete
	 */
	public function testDeleteChangesSingleLang()
	{
		$this->setUpSingleLanguage();

		$versionId = VersionId::changes();
		$language  = Language::single();

		$this->assertCreateAndDelete($versionId, $language);
	}

	/**
	 * @covers ::delete
	 */
	public function testDeletePublishedMultiLang()
	{
		$this->setUpMultiLanguage();

		$versionId = VersionId::published();
		$language  = $this->app->language('en');

		$this->assertCreateAndDelete($versionId, $language);
	}

	/**
	 * @covers ::delete
	 */
	public function testDeletePublishedSingleLang()
	{
		$this->setUpSingleLanguage();

		$versionId = VersionId::published();
		$language  = Language::single();

		$this->assertCreateAndDelete($versionId, $language);
	}

	/**
	 * @covers ::exists
	 */
	public function testExistsNoneExistingMultiLanguage()
	{
		$this->setUpMultiLanguage();

		$this->assertFalse($this->storage->exists(VersionId::changes(), $this->app->language('en')));
		$this->assertFalse($this->storage->exists(VersionId::changes(), $this->app->language('de')));
	}

	/**
	 * @covers ::exists
	 */
	public function testExistsNoneExistingSingleLanguage()
	{
		$this->setUpSingleLanguage();

		$this->assertFalse($this->storage->exists(VersionId::changes(), Language::single()));
	}

	/**
	 * @covers ::modified
	 */
	public function testModifiedNoneExistingMultiLanguage()
	{
		$this->setUpMultiLanguage();

		$this->assertNull($this->storage->modified(VersionId::changes(), $this->app->language('en')));
		$this->assertNull($this->storage->modified(VersionId::published(), $this->app->language('en')));
	}

	/**
	 * @covers ::modified
	 */
	public function testModifiedNoneExistingSingleLanguage()
	{
		$this->setUpSingleLanguage();

		$this->assertNull($this->storage->modified(VersionId::changes(), Language::single()));
		$this->assertNull($this->storage->modified(VersionId::published(), Language::single()));
	}

	/**
	 * @covers ::modified
	 */
	public function testModifiedSomeExistingMultiLanguage()
	{
		$this->setUpMultiLanguage();

		$changes  = VersionId::changes();
		$language = $this->app->language('en');

		$this->storage->create($changes, $language, []);

		$this->assertIsInt($this->storage->modified($changes, $language));
		$this->assertNull($this->storage->modified(VersionId::published(), $language));
	}

	/**
	 * @covers ::modified
	 */
	public function testModifiedSomeExistingSingleLanguage()
	{
		$this->setUpSingleLanguage();

		$changes  = VersionId::changes();
		$language = Language::single();

		$this->storage->create($changes, $language, []);

		$this->assertIsInt($this->storage->modified($changes, $language));
		$this->assertNull($this->storage->modified(VersionId::published(), $language));
	}

	/**
	 * @covers ::touch
	 */
	public function testTouchMultiLang()
	{
		$this->setUpMultiLanguage();

		$versionId = VersionId::changes();
		$language  = $this->app->language('en');

		$time = time();

		$this->storage->create($versionId, $language, []);
		$this->storage->touch($versionId, $language);

		$this->assertGreaterThanOrEqual($time, $this->storage->modified($versionId, $language));
	}

	/**
	 * @covers ::touch
	 */
	public function testTouchSingleLang()
	{
		$this->setUpSingleLanguage();

		$versionId = VersionId::changes();
		$language  = Language::single();

		$time = time();

		$this->storage->create($versionId, $language, []);
		$this->storage->touch($versionId, $language);

		$this->assertGreaterThanOrEqual($time, $this->storage->modified($versionId, $language));
	}
}
