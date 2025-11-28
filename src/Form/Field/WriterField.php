<?php

namespace Kirby\Form\Field;

use Kirby\Form\Mixin;
use Kirby\Form\Validations;
use Kirby\Sane\Sane;

/**
 * Writer Field
 *
 * @package   Kirby Field
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class WriterField extends StringField
{
	use Mixin\Icon;

	/**
	 * Available heading levels
	 */
	protected array|null $headings;

	/**
	 * Enables inline mode, which will not wrap new lines in paragraphs and creates hard breaks instead.
	 */
	protected bool|null $inline;

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
		bool|null $autofocus = null,
		bool|null $counter = null,
		mixed $default = null,
		bool|null $disabled = null,
		array|null $headings = null,
		array|string|null $help = null,
		string|null $icon = null,
		bool|null $inline = null,
		array|string|null $label = null,
		array|bool|null $marks = null,
		int|null $maxlength = null,
		int|null $minlength = null,
		string|null $name = null,
		array|bool|null $nodes = null,
		array|string|null $placeholder = null,
		bool|null $required = null,
		bool|null $spellcheck = null,
		array|bool|null $toolbar = null,
		bool|null $translate = null,
		array|null $when = null,
		string|null $width = null
	) {
		parent::__construct(
			autofocus: $autofocus,
			counter: $counter,
			default: $default,
			disabled: $disabled,
			help: $help,
			label: $label,
			maxlength: $maxlength,
			minlength: $minlength,
			name: $name,
			placeholder: $placeholder,
			required: $required,
			spellcheck: $spellcheck,
			translate: $translate,
			when: $when,
			width: $width
		);

		$this->headings = $headings;
		$this->icon     = $icon;
		$this->inline   = $inline;
		$this->marks    = $marks;
		$this->nodes    = $nodes;
		$this->toolbar  = $toolbar;
	}

	/**
	 * @psalm-suppress MethodSignatureMismatch
	 * @todo Remove psalm suppress after https://github.com/vimeo/psalm/issues/8673 is fixed
	 */
	public function fill(mixed $value): static
	{
		$value = trim($value ?? '');
		$value = Sane::sanitize($value, 'html');

		// convert non-breaking spaces to HTML entity
		// as that's how ProseMirror handles it internally;
		// will allow comparing saved and current content
		$this->value = str_replace(' ', '&nbsp;', $value);
		return $this;
	}

	public function headings(): array
	{
		return array_intersect($this->headings ?? range(1, 6), range(1, 6));
	}

	public function inline(): bool
	{
		return $this->inline ?? false;
	}

	public function marks(): array|bool|null
	{
		return $this->marks;
	}

	public function nodes(): array|bool|null
	{
		return $this->marks;
	}

	public function toolbar(): array|bool|null
	{
		return $this->toolbar;
	}

	public function props(): array
	{
		$props = parent::props();

		unset(
			$props['autocomplete'],
			$props['font']
		);

		return [
			...$props,
			'headings' => $this->headings(),
			'icon'     => $this->icon(),
			'inline'   => $this->inline(),
			'marks'    => $this->marks(),
			'nodes'    => $this->nodes(),
			'toolbar'  => $this->toolbar(),
		];
	}

	protected function validations(): array
	{
		return [
			'minlength' => fn ($value) => Validations::minlength($this, strip_tags($value)),
			'maxlength' => fn ($value) => Validations::maxlength($this, strip_tags($value)),
		];
	}
}
