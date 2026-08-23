<?php

namespace Kirby\Form\Field;

use Kirby\Form\Mixin;
use Kirby\Form\Validations;
use Kirby\Sane\Sane;

/**
 * Base class for fields backed by the ProseMirror document model
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
abstract class ProseMirrorField extends StringField
{
	use Mixin\Icon;

	/**
	 * Sets the allowed HTML formats. Available formats: `bold`, `italic`, `underline`, `strike`, `code`, `link`, `email`. Activate/deactivate them all by passing `true`/`false`. Default marks are `bold`, `italic`, `underline`, `strike`, `link`, `email`
	 */
	protected array|bool|null $marks;

	/**
	 * Sets the allowed nodes. Available nodes: `paragraph`, `heading`, `bulletList`, `orderedList`, `quote`. Activate/deactivate them all by passing `true`/`false`. Default nodes are `paragraph`, `heading`, `bulletList`, `orderedList`.
	 */
	protected array|bool|null $nodes;

	/**
	 * Toolbar options, incl. `marks` (to narrow down which marks should have toolbar buttons), `nodes` (to narrow down which nodes should have toolbar dropdown entries) and `inline` to set the position of the toolbar (false = sticking on top of the field)
	 */
	protected array|bool|null $toolbar;

	public function __construct(
		string|null $icon = null,
		array|bool|null $marks = null,
		array|bool|null $nodes = null,
		array|bool|null $toolbar = null,
		mixed ...$args
	) {
		parent::__construct(...$args);

		$this->icon    = $icon;
		$this->marks   = $marks;
		$this->nodes   = $nodes;
		$this->toolbar = $toolbar;
	}

	public function fill(mixed $value): static
	{
		$value = trim($value ?? '');
		$value = Sane::sanitizeProseMirrorFields($value);
		return parent::fill($value);
	}

	public function marks(): array|bool|null
	{
		return $this->marks;
	}

	public function nodes(): array|bool|null
	{
		return $this->nodes;
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'icon'    => $this->icon(),
			'marks'   => $this->marks(),
			'nodes'   => $this->nodes(),
			'toolbar' => $this->toolbar(),
		];
	}

	public function toolbar(): array|bool|null
	{
		return $this->toolbar;
	}

	protected function validations(): array
	{
		return [
			'minlength' => fn ($value) => Validations::minlength($this, strip_tags($value)),
			'maxlength' => fn ($value) => Validations::maxlength($this, strip_tags($value)),
		];
	}
}
