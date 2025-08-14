<?php

namespace Kirby\Content;

use Kirby\Cms\Language;
use Kirby\Exception\LogicException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\Dir;
use PHPUnit\Framework\Attributes\CoversClass;

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

#[CoversClass(VersionRules::class)]
class VersionRulesTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Content.VersionRules';

	public function testCreateWhenTheVersionAlreadyExists(): void
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

	public function testDeleteWhenTheVersionIsLocked(): void
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

	public function testMoveWhenTheSourceVersionIsLocked(): void
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

	public function testMoveWhenTheTargetVersionIsLocked(): void
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

	public function testPublishTheLatestVersion(): void
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

	public function testPublishWhenTheVersionIsLocked(): void
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

	public function testReadWhenMissing(): void
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

	public function testReplaceWhenTheVersionIsLocked(): void
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

	public function testTouchWhenMissing(): void
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

	public function testUpdateWhenTheVersionIsLocked(): void
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
