<?php

namespace Kirby\Form\Field;

use Kirby\Reflection\Attributes\Prop;

/**
 * Slug Field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class SlugField extends TextField
{
	/**
	 * Set of characters allowed in the slug
	 */
	#[Prop]
	protected string|null $allow = null;

	#[Prop]
	protected bool $counter = false;

	#[Prop]
	protected string|null $icon = 'url';

	/**
	 * Set prefix for the help text
	 */
	#[Prop]
	protected string|null $path = null;

	/**
	 * Name of another field that should be used to
	 * automatically update this field's value
	 */
	#[Prop]
	protected string|null $sync = null;

	/**
	 * Set to object with keys `field` and `text` to add
	 * button to generate from another field
	 */
	#[Prop]
	protected array|bool $wizard = false;

	public function allow(): string|null
	{
		return $this->allow;
	}

	public function label(): string
	{
		if ($this->label === null || $this->label === []) {
			return $this->i18n('slug');
		}

		return parent::label();
	}

	public function path(): string|null
	{
		return $this->path;
	}

	public function sync(): string|null
	{
		return $this->sync;
	}

	public function wizard(): array|bool
	{
		return $this->wizard;
	}
}
