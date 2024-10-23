<?php

namespace Kirby\Content;

use Kirby\Cms\Language;
use Kirby\Exception\LogicException;
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
 * @coversDefaultClass Kirby\Content\VersionRules
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

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('The version is locked and cannot be deleted');

		VersionRules::delete($version, Language::ensure());
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

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('The source version is locked and cannot be moved');

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

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('The target version is locked and cannot be overwritten');

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

	public function testPublishWhenTheVersionIsLocked()
	{
		$this->setUpSingleLanguage();

		$version = new LockedVersion(
			model: $this->model,
			id: VersionId::changes(),
		);

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('The version is locked and cannot be published');

		VersionRules::publish($version, Language::ensure());
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

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('The version is locked and cannot be replaced');

		VersionRules::replace($version, [], Language::ensure());
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

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('The version is locked and cannot be updated');

		VersionRules::update($version, [], Language::ensure());
	}
}
