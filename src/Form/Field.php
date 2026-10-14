<?php

namespace Kirby\Form;

use Kirby\Blueprint\Blueprint;
use Kirby\Cms\HasStringTemplate;
use Kirby\Cms\Language;
use Kirby\Cms\ModelWithContent;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Field\InfoField;
use Kirby\Form\Field\ValueField;
use Kirby\Reflection\Props;
use ReflectionMethod;
use Stringable;
use Throwable;
use TypeError;

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
		mixed ...$args
	) {
		$this->register($this->config());
		$this->register($args);
	}

	public function __toString(): string
	{
		return $this->name();
	}

	/**
	 * This method can be used in field plugins to read in
	 * additional config options for the field from Kirby's config.
	 */
	public function config(): array
	{
		return [];
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
	 * Creates an info field that shows an error message in place
	 * of a field that could not be created or rendered
	 * @since 6.0.0
	 */
	public static function error(
		string $name,
		string $message,
		ModelWithContent|null $model = null,
		Fields|null $siblings = null
	): InfoField {
		return InfoField::factory(
			props: [
				...Blueprint::fieldError($name, $message),
				'model' => $model
			],
			siblings: $siblings
		);
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
		$field = static::instantiate($props);
		$field->setSiblings($siblings);

		if (array_key_exists('model', $props) === true) {
			$field->setModel($props['model']);
		}

		if (
			array_key_exists('value', $props) === true &&
			$field->hasValue() === true
		) {
			$field->fill($props['value']);
		}

		return $field;
	}

	/**
	 * Checks if the field has a value
	 *
	 * @psalm-assert-if-true ValueField $this
	 */
	final public function hasValue(): bool
	{
		return $this instanceof ValueField;
	}

	/**
	 * @see `self::name()`
	 */
	public function id(): string
	{
		return $this->name();
	}

	/**
	 * Creates a new instance from the given props. Field plugins
	 * may define their own constructor without a variadic parameter.
	 * In this case, all props that the constructor does not accept
	 * are registered after the field has been constructed.
	 */
	protected static function instantiate(array $props): static
	{
		$constructor = new ReflectionMethod(static::class, '__construct');

		if ($constructor->isVariadic() === true) {
			return new static(...$props);
		}

		$params = [];

		foreach ($constructor->getParameters() as $param) {
			$params[$param->getName()] = true;
		}

		$accepted = array_intersect_key($props, $params);
		$field    = new static(...$accepted);
		$field->register(array_diff_key($props, $accepted));

		return $field;
	}

	public function isHidden(): bool
	{
		return false;
	}

	/**
	 * Fields without a value are never submitted.
	 * `Kirby\Form\Mixin\Value` overwrites this for all
	 * fields that can actually hold a value.
	 */
	public function isSubmittable(Language $language): bool
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
	 * Register properties and their values. This is mainly used in the constructor.
	 * Only properties marked with `#[Prop]` can be registered. `null` values
	 * are skipped to keep the property defaults.
	 * @see Props::set()
	 *
	 * @throws InvalidArgumentException if a value has the wrong type
	 */
	protected function register(array $args): void
	{
		$props = new Props($this);

		foreach ($args as $key => $value) {
			if (
				$value === null ||
				$props->has($key) === false
			) {
				continue;
			}

			try {
				$props->set($key, $value);
			} catch (TypeError $e) {
				throw new InvalidArgumentException(
					key: 'field.prop.invalid',
					data: [
						'name' => $args['name'] ?? $this->name ?? '-',
						'prop' => $key,
						'type' => $props->type($key)
					],
					previous: $e
				);
			}
		}
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
		// a field that fails to resolve its props must not take
		// down the entire view. It is replaced by an error field.
		try {
			$props = $this->props();
		} catch (Throwable $e) {
			$props = static::error(
				name:    $this->name(),
				message: $e->getMessage(),
				model:   $this->model()
			)->props();
		}

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
