<?php

namespace Kirby\Form\Field;

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
		string|null $path = null,
		string|null $sync = null,
		array|bool|null $wizard = null,
		mixed ...$args
	) {
		parent::__construct(...$args);

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
