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
	public function hasNextListed(Collection $collection = null): bool
	{
		return $this->nextListed($collection) !== null;
	}

	/**
	 * Checks if there's a next unlisted
	 * page in the siblings collection
	 */
	public function hasNextUnlisted(Collection $collection = null): bool
	{
		return $this->nextUnlisted($collection) !== null;
	}

	/**
	 * Checks if there's a previous listed
	 * page in the siblings collection
	 */
	public function hasPrevListed(Collection $collection = null): bool
	{
		return $this->prevListed($collection) !== null;
	}

	/**
	 * Checks if there's a previous unlisted
	 * page in the siblings collection
	 */
	public function hasPrevUnlisted(Collection $collection = null): bool
	{
		return $this->prevUnlisted($collection) !== null;
	}

	/**
	 * Returns the next listed page if it exists
	 */
	public function nextListed(Collection $collection = null): Page|null
	{
		return $this->nextAll($collection)->listed()->first();
	}

	/**
	 * Returns the next unlisted page if it exists
	 */
	public function nextUnlisted(Collection $collection = null): Page|null
	{
		return $this->nextAll($collection)->unlisted()->first();
	}

	/**
	 * Returns the previous listed page
	 */
	public function prevListed(Collection $collection = null): Page|null
	{
		return $this->prevAll($collection)->listed()->last();
	}

	/**
	 * Returns the previous unlisted page
	 */
	public function prevUnlisted(Collection $collection = null): Page|null
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
		return $this->siblings($self)->filter('intendedTemplate', $this->intendedTemplate()->name());
	}
}
