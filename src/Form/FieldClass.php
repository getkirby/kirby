<?php

namespace Kirby\Form;

use Kirby\Cms\HasSiblings;
use Kirby\Toolkit\I18n;

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
 *
 * @use \Kirby\Cms\HasSiblings<\Kirby\Form\Fields>
 */
abstract class FieldClass
{
	use HasSiblings;
	use Mixin\After;
	use Mixin\Api;
	use Mixin\Autofocus;
	use Mixin\Before;
	use Mixin\Help;
	use Mixin\Icon;
	use Mixin\Label;
	use Mixin\Model;
	use Mixin\Placeholder;
	use Mixin\Translatable;
	use Mixin\Validation;
	use Mixin\Value;
	use Mixin\When;
	use Mixin\Width;

	protected bool $disabled;
	protected string|null $name;
	protected Fields $siblings;

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

	protected function i18n(string|array|null $param = null): string|null
	{
		return empty($param) === false ? I18n::translate($param, $param) : null;
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
			'saveable'    => $this->hasValue(),
			'translate'   => $this->translate(),
			'type'        => $this->type(),
			'when'        => $this->when(),
			'width'       => $this->width(),
		];
	}

	/**
	 * @since 5.2.0
	 * @todo Move to `Value` mixin once array-based fields are unsupported
	 */
	public function reset(): static
	{
		$this->value = $this->emptyValue();
		return $this;
	}

	protected function setDisabled(bool $disabled = false): void
	{
		$this->disabled = $disabled;
	}

	protected function setName(string|null $name = null): void
	{
		$this->name = strtolower($name ?? $this->type());
	}

	protected function setSiblings(Fields|null $siblings = null): void
	{
		$this->siblings = $siblings ?? new Fields([$this]);
	}

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
}
