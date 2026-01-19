<?php

namespace Kirby\Form\Field;

use Kirby\Form\Fields;
use Kirby\Form\Mixin;
use Kirby\Reflection\Constructor;
use Kirby\Toolkit\HasStringTemplate;

/**
 * Base class for any field type
 *
 * @package   Kirby Field
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
abstract class BaseField
{
	use HasStringTemplate;
	use Mixin\Api;
	use Mixin\DefaultValue;
	use Mixin\Model;
	use Mixin\Name;
	use Mixin\Siblings;
	use Mixin\Translatable;
	use Mixin\When;
	use Mixin\Width;

	public function __construct(
		string|null $name = null,
		array|null $when = null,
		string|null $width = null
	) {
		$this->name  = $name;
		$this->when  = $when;
		$this->width = $width;
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

	/**
	 * Creates a new field instance from a $props array
	 * @since 6.0.0
	 */
	public static function factory(
		array $props,
		Fields|null $siblings = null
	): static {
		$constructor = new Constructor(static::class);
		$args        = $constructor->getAcceptedArguments($props);

		$field = new static(...$args);
		$field->setSiblings($siblings);

		if (array_key_exists('model', $props) === true) {
			$field->setModel($props['model']);
		}

		if (
			array_key_exists('value', $props) === true &&
			method_exists($field, 'fill') === true
		) {
			$field->fill($props['value']);
		}

		return $field;
	}

	/**
	 * Checks if the field has a value
	 */
	public function hasValue(): bool
	{
		return property_exists($this, 'value') === true;
	}

	/**
	 * @see `self::name()`
	 */
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
			'hidden'   => $this->isHidden(),
			'name'     => $this->name(),
			'saveable' => $this->hasValue(),
			'type'     => $this->type(),
			'when'     => $this->when(),
			'width'    => $this->width()
		];
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
		return strtolower(basename(str_replace(['\\', 'Field'], ['/', ''], static::class)));
	}
}
