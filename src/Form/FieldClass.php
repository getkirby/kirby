<?php

namespace Kirby\Form;

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
	use Mixin\Common;
	use Mixin\Endpoints;
	use Mixin\Model;
	use Mixin\Siblings;
	use Mixin\Translatable;
	use Mixin\Validation;
	use Mixin\Value;
	use Mixin\When;

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
		$this->setValidate($params['validate'] ?? []);

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

	/**
	 * Sets a new value for the field
	 */
	public function fill(mixed $value = null): void
	{
		$this->value = $value;
		$this->errors = null;
	}

	/**
	 * Returns all original params for the field
	 */
	public function params(): array
	{
		return $this->params;
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

	protected function setWidth(string|null $width = null): void
	{
		$this->width = $width;
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
}
