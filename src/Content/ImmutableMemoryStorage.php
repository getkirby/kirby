<?php

namespace Kirby\Content;

use Kirby\Cms\Language;
use Kirby\Cms\ModelWithContent;
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
	public function __construct(
		protected ModelWithContent $model,
		protected ModelWithContent|null $nextModel = null
	) {
		parent::__construct($model);
	}

	/**
	 * Immutable storage entries cannot be deleted
	 *
	 * @throws \Kirby\Exception\LogicException
	 */
	public function delete(VersionId $versionId, Language $language): void
	{
		$this->preventMutation('deleted');
	}

	/**
	 * Immutable storage entries cannot be moved
	 *
	 * @throws \Kirby\Exception\LogicException
	 */
	public function move(
		VersionId $fromVersionId,
		Language $fromLanguage,
		VersionId|null $toVersionId = null,
		Language|null $toLanguage = null,
		Storage|null $toStorage = null
	): void {
		$this->preventMutation('moved');
	}

	/**
	 * Returns the next state of the model if the
	 * reference is given
	 */
	public function nextModel(): ModelWithContent|null
	{
		return $this->nextModel;
	}

	/**
	 * Throws an exception to avoid the mutation of storage data
	 *
	 * @throws \Kirby\Exception\LogicException
	 */
	protected function preventMutation(string $mutation): void
	{
		throw new LogicException(
			message: 'Storage for the ' . $this->model::CLASS_ALIAS . ' is immutable and cannot be ' . $mutation . '. Make sure to use the last alteration of the object.'
		);
	}

	/**
	 * Immutable storage entries cannot be touched
	 *
	 * @throws \Kirby\Exception\LogicException
	 */
	public function touch(VersionId $versionId, Language $language): void
	{
		$this->preventMutation('touched');
	}

	/**
	 * Immutable storage entries cannot be updated
	 *
	 * @throws \Kirby\Exception\LogicException
	 */
	public function update(VersionId $versionId, Language $language, array $fields): void
	{
		$this->preventMutation('updated');
	}
}
