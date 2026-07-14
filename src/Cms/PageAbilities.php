<?php

namespace Kirby\Cms;

/**
 * Page Abilities
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class PageAbilities extends ModelAbilities
{
	public function __construct(
		protected Page $page
	) {
	}

	public function changeSlug(): bool
	{
		return $this->page->isHomeOrErrorPage() !== true;
	}

	public function changeStatus(): bool
	{
		return $this->page->isErrorPage() !== true;
	}

	public function changeTemplate(): bool
	{
		if ($this->page->isErrorPage() === true) {
			return false;
		}

		if (count($this->page->blueprints()) <= 1) {
			return false;
		}

		return true;
	}

	public function delete(): bool
	{
		return $this->page->isHomeOrErrorPage() !== true;
	}

	public function move(): bool
	{
		return $this->page->isHomeOrErrorPage() !== true;
	}

	public function sort(): bool
	{
		if ($this->page->isErrorPage() === true) {
			return false;
		}

		if ($this->page->isListed() !== true) {
			return false;
		}

		if ($this->page->blueprint()->num() !== 'default') {
			return false;
		}

		return true;
	}
}
