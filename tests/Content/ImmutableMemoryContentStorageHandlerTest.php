<?php

namespace Kirby\Content;

use Kirby\Cms\Language;
use Kirby\Exception\LogicException;

/**
 * @coversDefaultClass \Kirby\Content\ImmutableMemoryContentStorageHandler
 * @covers ::__construct
 */
class ImmutableMemoryContentStorageHandlerTest extends TestCase
{
	protected $storage;

	public function setUp(): void
	{
		parent::setUp();
		parent::setUpSingleLanguage();

		$this->storage = new ImmutableMemoryContentStorageHandler($this->model);
	}

	/**
	 * @covers ::delete
	 * @covers ::preventMutation
	 */
	public function testDelete()
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Storage for the page is immutable and cannot be deleted. Make sure to use the last alteration of the object.');

		$this->storage->delete(VersionId::published(), Language::ensure());
	}

	/**
	 * @covers ::move
	 * @covers ::preventMutation
	 */
	public function testMove()
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Storage for the page is immutable and cannot be deleted. Make sure to use the last alteration of the object.');

		$this->storage->move(
			fromVersionId: VersionId::published(),
			fromLanguage: Language::ensure(),
			toVersionId: VersionId::changes(),
			toLanguage: Language::ensure()
		);
	}

	/**
	 * @covers ::touch
	 * @covers ::preventMutation
	 */
	public function testTouch()
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Storage for the page is immutable and cannot be deleted. Make sure to use the last alteration of the object.');

		$this->storage->touch(VersionId::published(), Language::ensure());
	}

	/**
	 * @covers ::update
	 * @covers ::preventMutation
	 */
	public function testUpdate()
	{
		$this->storage->create(VersionId::published(), Language::ensure(), []);

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Storage for the page is immutable and cannot be deleted. Make sure to use the last alteration of the object.');

		$this->storage->update(VersionId::published(), Language::ensure(), []);
	}
}
