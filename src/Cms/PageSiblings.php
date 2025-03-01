<?php

namespace Kirby\Cms;

/**
 * PageSiblings
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait PageSiblings
{
	/**
	 * Checks if there's a next listed
	 * page in the siblings collection
	 */
	public function hasNextListed(Pages|null $collection = null): bool
	{
		return $this->nextListed($collection) !== null;
	}

	/**
	 * Checks if there's a next unlisted
	 * page in the siblings collection
	 */
	public function hasNextUnlisted(Pages|null $collection = null): bool
	{
		return $this->nextUnlisted($collection) !== null;
	}

	/**
	 * Checks if there's a previous listed
	 * page in the siblings collection
	 */
	public function hasPrevListed(Pages|null $collection = null): bool
	{
		return $this->prevListed($collection) !== null;
	}

	/**
	 * Checks if there's a previous unlisted
	 * page in the siblings collection
	 */
	public function hasPrevUnlisted(Pages|null $collection = null): bool
	{
		return $this->prevUnlisted($collection) !== null;
	}

	/**
	 * Returns the next listed page if it exists
	 */
	public function nextListed(Pages|null $collection = null): Page|null
	{
		return $this->nextAll($collection)->listed()->first();
	}

	/**
	 * Returns the next unlisted page if it exists
	 */
	public function nextUnlisted(Pages|null $collection = null): Page|null
	{
		return $this->nextAll($collection)->unlisted()->first();
	}

	/**
	 * Returns the previous listed page
	 */
	public function prevListed(Pages|null $collection = null): Page|null
	{
		return $this->prevAll($collection)->listed()->last();
	}

	/**
	 * Returns the previous unlisted page
	 */
	public function prevUnlisted(Pages|null $collection = null): Page|null
	{
		return $this->prevAll($collection)->unlisted()->last();
	}

	/**
	 * Private siblings collector
	 */
	protected function siblingsCollection(): Pages
	{
		if ($this->isDraft() === true) {
			return $this->parentModel()->drafts();
		}

		return $this->parentModel()->children();
	}

	/**
	 * Returns siblings with the same template
	 */
	public function templateSiblings(bool $self = true): Pages
	{
		return $this->siblings($self)->filter(
			'intendedTemplate',
			$this->intendedTemplate()->name()
		);
	}
}
