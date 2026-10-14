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
	use Mixin\Type;
	use Mixin\When;
	use Mixin\Width;

	/**
	 * Registry for all field types
	 */
	public static array $types = [];

	/**
	 * Custom constructors need to pass
	 * `...$args` on to `parent::__construct()`
	 */
	public function __construct(
		ModelWithContent|null $model = null,
		Fields|null $siblings = null,
		mixed ...$args
	) {
		$this->model    = $model;
		$this->siblings = $siblings;

		$this->register($this->config());
		$this->register($args);

		if (
			array_key_exists('value', $args) === true &&
			$this->hasValue() === true
		) {
			$this->fill($args['value']);
		}
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
		return new InfoField(...[
			...Blueprint::fieldError($name, $message),
			'model'    => $model,
			'siblings' => $siblings
		]);
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
	 * Returns the props for the Vue component,
	 * incl. all exported `#[Prop]` properties
	 */
	public function props(): array
	{
		return [
			...(new Props($this))->values(),
			'hidden'   => $this->isHidden(),
			'saveable' => $this->hasValue(),
		];
	}

	/**
	 * Sets `#[Prop]` properties, skipping
	 * `null` values to keep their defaults
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
	 * @throws InvalidArgumentException if the type is unknown, not a field class or its constructor does not accept all props
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

		// all props from the blueprint are passed to the constructor
		if ((new ReflectionMethod($class, '__construct'))->isVariadic() === false) {
			throw new InvalidArgumentException(
				message: 'The constructor of "' . $class . '" must accept ' .
				'all other props as `mixed ...$args` and pass them on ' .
				'to `parent::__construct()`'
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
}
