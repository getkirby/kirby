<?php

namespace Kirby\Form\Field;

/**
 * Slug Field
 *
 * @package   Kirby Field
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class SlugField extends TextField
{
	/**
	 * Set of characters allowed in the slug
	 */
	protected string|null $allow;

	/**
	 * Set prefix for the help text
	 */
	protected string|null $path;

	/**
	 * Name of another field that should be used to
	 * automatically update this field's value
	 */
	protected string|null $sync;

	/**
	 * Set to object with keys `field` and `text` to add
	 * button to generate from another field
	 */
	protected array|bool|null $wizard;

	public function __construct(
		string|null $allow = null,
		array|string|null $after = null,
		string|null $autocomplete = null,
		bool|null $autofocus = null,
		array|string|null $before = null,
		string|null $converter = null,
		bool|null $counter = null,
		mixed $default = null,
		bool|null $disabled = null,
		array|string|null $help = null,
		string|null $font = null,
		string|null $icon = null,
		array|string|null $label = null,
		int|null $maxlength = null,
		int|null $minlength = null,
		string|null $name = null,
		string|null $path = null,
		string|null $pattern = null,
		array|string|null $placeholder = null,
		bool|null $required = null,
		bool|null $spellcheck = null,
		string|null $sync = null,
		bool|null $translate = null,
		array|null $when = null,
		string|null $width = null,
		array|bool|null $wizard = null
	) {
		parent::__construct(
			after: $after,
			autocomplete: $autocomplete,
			autofocus: $autofocus,
			before: $before,
			converter: $converter,
			counter: $counter,
			default: $default,
			disabled: $disabled,
			font: $font,
			help: $help,
			icon: $icon,
			label: $label,
			name: $name,
			maxlength: $maxlength,
			minlength: $minlength,
			pattern: $pattern,
			placeholder: $placeholder,
			required: $required,
			spellcheck: $spellcheck,
			translate: $translate,
			when: $when,
			width: $width
		);

		$this->allow  = $allow;
		$this->path   = $path;
		$this->sync   = $sync;
		$this->wizard = $wizard;
	}

	public function allow(): string|null
	{
		return $this->allow;
	}

	public function counter(): bool
	{
		return $this->counter ?? false;
	}

	public function icon(): string
	{
		return $this->icon ?? 'url';
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

	public function props(): array
	{
		return [
			...parent::props(),
			'allow'  => $this->allow(),
			'path'   => $this->path(),
			'sync'   => $this->sync(),
			'wizard' => $this->wizard(),
		];
	}

	public function sync(): string|null
	{
		return $this->sync;
	}

	public function wizard(): array|bool
	{
		return $this->wizard ?? false;
	}
}
