<?php

namespace Kirby\Form;

use Kirby\Cms\HasSiblings;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

/**
 * Abstract field class to be used instead
 * of functional field components for more
 * control.
 *
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
abstract class FieldClass
{
	/**
	 * @use \Kirby\Cms\HasSiblings<\Kirby\Form\Fields>
	 */
	use HasSiblings;
	use Mixin\Api;
	use Mixin\Model;
	use Mixin\Translatable;
	use Mixin\Validation;
	use Mixin\Value;
	use Mixin\When;

	protected string|null $after;
	protected bool $autofocus;
	protected string|null $before;
	protected mixed $default;
	protected bool $disabled;
	protected string|null $help;
	protected string|null $icon;
	protected string|null $label;
	protected string|null $name;
	protected string|null $placeholder;
	protected bool $required;
	protected Fields $siblings;
	protected mixed $value = null;
	protected string|null $width;

	public function __construct(
		protected array $params = []
	) {
		$this->setAfter($params['after'] ?? null);
		$this->setAutofocus($params['autofocus'] ?? false);
		$this->setBefore($params['before'] ?? null);
		$this->setDefault($params['default'] ?? null);
		$this->setDisabled($params['disabled'] ?? false);
		$this->setHelp($params['help'] ?? null);
		$this->setIcon($params['icon'] ?? null);
		$this->setLabel($params['label'] ?? null);
		$this->setModel($params['model'] ?? null);
		$this->setName($params['name'] ?? null);
		$this->setPlaceholder($params['placeholder'] ?? null);
		$this->setRequired($params['required'] ?? false);
		$this->setSiblings($params['siblings'] ?? null);
		$this->setTranslate($params['translate'] ?? true);
		$this->setWhen($params['when'] ?? null);
		$this->setWidth($params['width'] ?? null);

		if (array_key_exists('value', $params) === true) {
			$this->fill($params['value']);
		}
	}

	public function __call(string $param, array $args): mixed
	{
		if (isset($this->$param) === true) {
			return $this->$param;
		}

		return $this->params[$param] ?? null;
	}

	public function after(): string|null
	{
		return $this->stringTemplate($this->after);
	}

	public function autofocus(): bool
	{
		return $this->autofocus;
	}

	public function before(): string|null
	{
		return $this->stringTemplate($this->before);
	}

	/**
	 * Returns optional dialog routes for the field
	 */
	public function dialogs(): array
	{
		return [];
	}

	/**
	 * If `true`, the field is no longer editable and will not be saved
	 */
	public function disabled(): bool
	{
		return $this->disabled;
	}

	/**
	 * Returns optional drawer routes for the field
	 */
	public function drawers(): array
	{
		return [];
	}

	/**
	 * Sets a new value for the field
	 */
	public function fill(mixed $value = null): void
	{
		$this->value = $value;
		$this->errors = null;
	}

	/**
	 * Optional help text below the field
	 */
	public function help(): string|null
	{
		if (empty($this->help) === false) {
			$help = $this->stringTemplate($this->help);
			$help = $this->kirby()->kirbytext($help);
			return $help;
		}

		return null;
	}

	protected function i18n(string|array|null $param = null): string|null
	{
		return empty($param) === false ? I18n::translate($param, $param) : null;
	}

	/**
	 * Optional icon that will be shown at the end of the field
	 */
	public function icon(): string|null
	{
		return $this->icon;
	}

	public function id(): string
	{
		return $this->name();
	}

	public function isDisabled(): bool
	{
		return $this->disabled;
	}

	public function isHidden(): bool
	{
		return false;
	}

	public function isRequired(): bool
	{
		return $this->required;
	}

	public function isSaveable(): bool
	{
		return true;
	}

	/**
	 * The field label can be set as string or associative array with translations
	 */
	public function label(): string
	{
		return $this->stringTemplate(
			$this->label ?? Str::ucfirst($this->name())
		);
	}

	/**
	 * Returns the field name
	 */
	public function name(): string
	{
		return $this->name ?? $this->type();
	}

	/**
	 * Returns all original params for the field
	 */
	public function params(): array
	{
		return $this->params;
	}

	/**
	 * Optional placeholder value that will be shown when the field is empty
	 */
	public function placeholder(): string|null
	{
		return $this->stringTemplate($this->placeholder);
	}

	/**
	 * Define the props that will be sent to
	 * the Vue component
	 */
	public function props(): array
	{
		return [
			'after'       => $this->after(),
			'autofocus'   => $this->autofocus(),
			'before'      => $this->before(),
			'default'     => $this->default(),
			'disabled'    => $this->isDisabled(),
			'help'        => $this->help(),
			'hidden'      => $this->isHidden(),
			'icon'        => $this->icon(),
			'label'       => $this->label(),
			'name'        => $this->name(),
			'placeholder' => $this->placeholder(),
			'required'    => $this->isRequired(),
			'saveable'    => $this->isSaveable(),
			'translate'   => $this->translate(),
			'type'        => $this->type(),
			'when'        => $this->when(),
			'width'       => $this->width(),
		];
	}

	/**
	 * If `true`, the field has to be filled in correctly to be saved.
	 */
	public function required(): bool
	{
		return $this->required;
	}

	/**
	 * Checks if the field is saveable
	 * @deprecated 5.0.0 Use `::isSaveable()` instead
	 */
	public function save(): bool
	{
		return $this->isSaveable();
	}

	protected function setAfter(array|string|null $after = null): void
	{
		$this->after = $this->i18n($after);
	}

	protected function setAutofocus(bool $autofocus = false): void
	{
		$this->autofocus = $autofocus;
	}

	protected function setBefore(array|string|null $before = null): void
	{
		$this->before = $this->i18n($before);
	}

	protected function setDefault(mixed $default = null): void
	{
		$this->default = $default;
	}

	protected function setDisabled(bool $disabled = false): void
	{
		$this->disabled = $disabled;
	}

	protected function setHelp(array|string|null $help = null): void
	{
		$this->help = $this->i18n($help);
	}

	protected function setIcon(string|null $icon = null): void
	{
		$this->icon = $icon;
	}

	protected function setLabel(array|string|null $label = null): void
	{
		$this->label = $this->i18n($label);
	}

	protected function setName(string|null $name = null): void
	{
		$this->name = $name;
	}

	protected function setPlaceholder(array|string|null $placeholder = null): void
	{
		$this->placeholder = $this->i18n($placeholder);
	}

	protected function setRequired(bool $required = false): void
	{
		$this->required = $required;
	}

	protected function setSiblings(Fields|null $siblings = null): void
	{
		$this->siblings = $siblings ?? new Fields([$this]);
	}

	/**
	 * Setter for the field width
	 */
	protected function setWidth(string|null $width = null): void
	{
		$this->width = $width;
	}

	/**
	 * Returns all sibling fields for the HasSiblings trait
	 */
	protected function siblingsCollection(): Fields
	{
		return $this->siblings;
	}

	/**
	 * Parses a string template in the given value
	 */
	protected function stringTemplate(string|null $string = null): string|null
	{
		if ($string !== null) {
			return $this->model->toString($string);
		}

		return null;
	}

	/**
	 * Converts the field to a plain array
	 */
	public function toArray(): array
	{
		$props = $this->props();

		ksort($props);

		return array_filter($props, fn ($item) => $item !== null);
	}

	/**
	 * Returns the field type
	 */
	public function type(): string
	{
		return lcfirst(basename(str_replace(['\\', 'Field'], ['/', ''], static::class)));
	}

	/**
	 * Returns the width of the field in
	 * the Panel grid
	 */
	public function width(): string
	{
		return $this->width ?? '1/1';
	}
}
