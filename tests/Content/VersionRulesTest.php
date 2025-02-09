<?php

namespace Kirby\Content;

use Kirby\Cms\Language;
use Kirby\Exception\LogicException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\Dir;

class ExistingVersion extends Version
{
	public function exists(Language|string $language = 'default'): bool
	{
		return true;
	}
}

class LockedVersion extends Version
{
	public function isLocked(Language|string $language = 'default'): bool
	{
		return true;
	}
}

/**
 * @coversDefaultClass \Kirby\Content\VersionRules
 */
class VersionRulesTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Content.VersionRules';

	/**
	 * @covers ::create
	 */
	public function testCreateWhenTheVersionAlreadyExists()
	{
		$this->setUpSingleLanguage();

		$version = new ExistingVersion(
			model: $this->model,
			id: VersionId::latest(),
		);

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('The version already exists');

		VersionRules::create($version, [], Language::ensure());
	}

	/**
	 * @covers ::create
	 */
	public function testCreateWhenLatestVersionDoesNotExist()
	{
		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::changes(),
		);

		// remove the model root to simulate a missing latest version
		Dir::remove($this->model->root());

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('A matching latest version for the changes does not exist');

		VersionRules::create($version, [], Language::ensure());
	}

	/**
	 * @covers ::delete
	 */
	public function testDeleteWhenTheVersionIsLocked()
	{
		$this->setUpSingleLanguage();

		$version = new LockedVersion(
			model: $this->model,
			id: VersionId::changes(),
		);

		$this->expectException(LockedContentException::class);
		$this->expectExceptionCode('error.content.lock.delete');

		VersionRules::delete($version, Language::ensure());
	}

	/**
	 * @covers ::ensure
	 */
	public function testEnsureMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::latest()
		);

		$this->createContentMultiLanguage();

		$this->assertNull(VersionRules::ensure($version, Language::ensure('en')));
		$this->assertNull(VersionRules::ensure($version, Language::ensure('de')));
	}

	/**
	 * @covers ::ensure
	 */
	public function testEnsureSingleLanguage(): void
	{
		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::latest()
		);

		$this->createContentSingleLanguage();

		$this->assertNull(VersionRules::ensure($version, Language::ensure()));
	}

	/**
	 * @covers ::ensure
	 */
	public function testEnsureWhenMissingMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::changes()
		);

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('Version "changes (de)" does not already exist');

		VersionRules::ensure($version, Language::ensure('de'));
	}

	/**
	 * @covers ::ensure
	 */
	public function testEnsureWhenMissingSingleLanguage(): void
	{
		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::changes()
		);

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('Version "changes" does not already exist');

		VersionRules::ensure($version, Language::ensure());
	}

	/**
	 * @covers ::ensure
	 */
	public function testEnsureWithInvalidLanguage(): void
	{
		$this->setUpMultiLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::latest()
		);

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('Invalid language: fr');

		VersionRules::ensure($version, Language::ensure('fr'));
	}

	/**
	 * @covers ::move
	 */
	public function testMoveWhenTheSourceVersionIsLocked()
	{
		$this->setUpSingleLanguage();

		$source = new LockedVersion(
			model: $this->model,
			id: VersionId::changes(),
		);

		$target = new Version(
			model: $this->model,
			id: VersionId::changes(),
		);

		$source->save([]);
		$target->save([]);

		$this->expectException(LockedContentException::class);
		$this->expectExceptionCode('error.content.lock.move');

		VersionRules::move($source, Language::ensure(), $target, Language::ensure());
	}

	/**
	 * @covers ::move
	 */
	public function testMoveWhenTheTargetVersionIsLocked()
	{
		$this->setUpSingleLanguage();

		$source = new Version(
			model: $this->model,
			id: VersionId::changes(),
		);

		$target = new LockedVersion(
			model: $this->model,
			id: VersionId::changes(),
		);

		// the source needs to exist to jump to the
		// next logic issue
		$source->save([]);

		$this->expectException(LockedContentException::class);
		$this->expectExceptionCode('error.content.lock.update');

		VersionRules::move($source, Language::ensure(), $target, Language::ensure());
	}

	/**
	 * @covers ::publish
	 */
	public function testPublishTheLatestVersion()
	{
		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::latest(),
		);

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('This version is already published');

		VersionRules::publish($version, Language::ensure());
	}

	/**
	 * @covers ::publish
	 */
	public function testPublishWhenTheVersionIsLocked()
	{
		$this->setUpSingleLanguage();

		$version = new LockedVersion(
			model: $this->model,
			id: VersionId::changes(),
		);

		$version->save([]);

		$this->expectException(LockedContentException::class);
		$this->expectExceptionCode('error.content.lock.publish');

		VersionRules::publish($version, Language::ensure());
	}

	/**
	 * @covers ::read
	 */
	public function testReadWhenMissing()
	{
		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::latest(),
		);

		// make sure that the version does not exist
		// just because the model root exists
		Dir::remove($this->model->root());

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('Version "latest" does not already exist');

		VersionRules::read($version, Language::ensure());
	}

	/**
	 * @covers ::replace
	 */
	public function testReplaceWhenTheVersionIsLocked()
	{
		$this->setUpSingleLanguage();

		$version = new LockedVersion(
			model: $this->model,
			id: VersionId::changes(),
		);

		$version->save([]);

		$this->expectException(LockedContentException::class);
		$this->expectExceptionCode('error.content.lock.replace');

		VersionRules::replace($version, [], Language::ensure());
	}

	/**
	 * @covers ::touch
	 */
	public function testTouchWhenMissing()
	{
		$this->setUpSingleLanguage();

		$version = new Version(
			model: $this->model,
			id: VersionId::latest(),
		);

		// make sure that the version does not exist
		// just because the model root exists
		Dir::remove($this->model->root());

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('Version "latest" does not already exist');

		VersionRules::touch($version, Language::ensure());
	}

	/**
	 * @covers ::update
	 */
	public function testUpdateWhenTheVersionIsLocked()
	{
		$this->setUpSingleLanguage();

		$version = new LockedVersion(
			model: $this->model,
			id: VersionId::changes(),
		);

		$version->save([]);

		$this->expectException(LockedContentException::class);
		$this->expectExceptionCode('error.content.lock.update');

		VersionRules::update($version, [], Language::ensure());
	}
}
