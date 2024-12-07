<?php

namespace Kirby\Content;

use Kirby\Cms\Language;
use Kirby\Exception\LogicException;

/**
 * @package   Kirby Content
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class ImmutableMemoryStorage extends MemoryStorage
{
	public function delete(VersionId $versionId, Language $language): void
	{
		$this->preventMutation();
	}

	public function move(
		VersionId $fromVersionId,
		Language $fromLanguage,
		VersionId|null $toVersionId = null,
		Language|null $toLanguage = null,
		Storage|null $toStorage = null
	): void {
		$this->preventMutation();
	}

	/**
	 * Throws an exception to avoid the mutation of storage data
	 *
	 * @throws \Kirby\Exception\LogicException
	 */
	protected function preventMutation(): void
	{
		throw new LogicException(
			message: 'Storage for the ' . $this->model::CLASS_ALIAS . ' is immutable and cannot be deleted. Make sure to use the last alteration of the object.'
		);
	}

	public function touch(VersionId $versionId, Language $language): void
	{
		$this->preventMutation();
	}

	public function update(VersionId $versionId, Language $language, array $fields): void
	{
		$this->preventMutation();
	}
}
