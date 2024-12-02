<?php

namespace Kirby\Form\Mixin;

use Kirby\Cms\HasSiblings;
use Kirby\Form\Fields;

/**
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
trait Siblings
{
	/**
	 * @use \Kirby\Cms\HasSiblings<\Kirby\Form\Fields>
	 */
	use HasSiblings;

	/**
	 * Parent collection with all fields of the current form
	 */
	public Fields $siblings;

	/**
	 * @deprecated 5.0.0 Use `::siblings() instead
	 */
	public function formFields(): Fields
	{
		return $this->siblings;
	}

	protected function setSiblings(Fields|null $siblings = null): void
	{
		$this->siblings = $siblings ?? new Fields([$this]);
	}

	/**
	 * Returns all sibling fields for the HasSiblings trait
	 */
	protected function siblingsCollection(): Fields
	{
		return $this->siblings;
	}
}
