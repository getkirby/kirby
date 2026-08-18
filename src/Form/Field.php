<?php

namespace Kirby\Form;

use Kirby\Cms\HasStringTemplate;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Reflection\Constructor;
use Stringable;

/**
 * Base class for any field type
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
abstract class Field implements Stringable
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

	/**
	 * Registry for all field types
	 */
	public static array $types = [];

	public function __construct(
		string|null $name = null,
		array|null $when = null,
		string|null $width = null
	) {
		$this->name  = $name;
		$this->when  = $when;
		$this->width = $width;
	}

	public function __toString(): string
	{
		return $this->name();
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
	 * Fields without a value never have errors.
	 * `Kirby\Form\Mixin\Validation` overwrites this
	 * for all fields that can actually be validated.
	 */
	public function errors(): array
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
	 * Returns the field's label, if it has one
	 */
	public function label(): string|null
	{
		return null;
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
	 * Resolves a field type to its class name
	 * @since 6.0.0
	 *
	 * @return class-string<static>
	 * @throws InvalidArgumentException if the type is unknown or not a field class
	 */
	public static function resolve(
		string $type,
		string|null $name = null
	): string {
		$class = static::$types[$type] ?? null;

		if ($class === null) {
			throw new InvalidArgumentException(
				key: 'field.type.missing',
				data: [
					'name' => $name ?? '-',
					'type' => $type
				]
			);
		}

		if (is_string($class) === false) {
			throw new InvalidArgumentException(
				message: 'The field type "' . $type . '" is registered as ' .
				get_debug_type($class) . '. Array-based field definitions ' .
				'have been removed in Kirby 6. Please register the name of ' .
				'a class that extends ' . self::class . ' instead.'
			);
		}

		if (is_subclass_of($class, self::class) === false) {
			throw new InvalidArgumentException(
				message: 'The field type "' . $type . '" is registered as "' .
				$class . '", which does not extend ' . self::class
			);
		}

		return $class;
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
