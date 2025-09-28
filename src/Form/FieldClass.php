<?php

namespace Kirby\Form;

use Kirby\Cms\HasSiblings;
use Kirby\Cms\ModelWithContent;
use Kirby\Exception\NotFoundException;
use Kirby\Toolkit\HasI18n;

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
	use HasI18n;
	use HasSiblings;
	use Mixin\After;
	use Mixin\Api;
	use Mixin\Autofocus;
	use Mixin\Before;
	use Mixin\DefaultValue;
	use Mixin\Disabled;
	use Mixin\Help;
	use Mixin\Icon;
	use Mixin\Label;
	use Mixin\Model;
	use Mixin\Name;
	use Mixin\Placeholder;
	use Mixin\Required;
	use Mixin\Translatable;
	use Mixin\Validation;
	use Mixin\Value;
	use Mixin\When;
	use Mixin\Width;

	public function __construct(
		array|string|null $after = null,
		bool $autofocus = false,
		array|string|null $before = null,
		mixed $default = null,
		bool|null $disabled = null,
		array|string|null $help = null,
		string|null $icon = null,
		array|string|null $label = null,
		ModelWithContent|null $model = null,
		string|null $name = null,
		array|string|null $placeholder = null,
		bool|null $required = null,
		protected Fields|null $siblings = null,
		bool|null $translate = null,
		$value = null,
		array|null $when = null,
		string|null $width = null
	) {
		$this->setAfter($after);
		$this->setAutofocus($autofocus);
		$this->setBefore($before);
		$this->setDefault($default);
		$this->setDisabled($disabled);
		$this->setHelp($help);
		$this->setIcon($icon);
		$this->setLabel($label);
		$this->setModel($model);
		$this->setName($name);
		$this->setPlaceholder($placeholder);
		$this->setRequired($required);
		$this->setSiblings($siblings);
		$this->setTranslate($translate);
		$this->setWhen($when);
		$this->setWidth($width);

		if ($value !== null) {
			$this->fill($value);
		}
	}

	public function __call(string $param, array $args): mixed
	{
		if (property_exists($this, $param) === true) {
			return $this->$param;
		}

		throw new NotFoundException(message: 'Method or option "' . $param . '" does not exist for field type "' . $this->type() . '"');
	}

	/**
	 * Returns optional dialog routes for the field
	 */
	public function dialogs(): array
	{
		return [];
	}

	/**
	 * Returns optional drawer routes for the field
	 */
	public function drawers(): array
	{
		return [];
	}

	public function id(): string
	{
		return $this->name();
	}

	public function isHidden(): bool
	{
		return false;
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
			return $this->model()->toString($string);
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
