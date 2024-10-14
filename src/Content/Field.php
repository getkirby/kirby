<?php

namespace Kirby\Content;

use Closure;
use Kirby\Cms\ModelWithContent;

/**
 * Every field in a Kirby content text file
 * is being converted into such a Field object.
 *
 * Field methods can be registered for those Field
 * objects, which can then be used to transform or
 * convert the field value. This enables our
 * daisy-chaining API for templates and other components
 *
 * ```php
 * // Page field example with lowercase conversion
 * $page->myField()->lower();
 * ```
 *
 * @package   Kirby Content
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Field
{
	/**
	 * Field method aliases
	 */
	public static array $aliases = [];

	/**
	 * The field name
	 */
	protected string $key;

	/**
	 * Registered field methods
	 */
	public static array $methods = [];

	/**
	 * The parent object if available.
	 * This will be the page, site, user or file
	 * to which the content belongs
	 */
	protected ModelWithContent|null $parent;

	/**
	 * The value of the field
	 */
	public mixed $value;

	/**
	 * Creates a new field object
	 */
	public function __construct(
		ModelWithContent|null $parent,
		string $key,
		mixed $value
	) {
		$this->key    = $key;
		$this->value  = $value;
		$this->parent = $parent;
	}

	/**
	 * Magic caller for field methods
	 */
	public function __call(string $method, array $arguments = []): mixed
	{
		$method = strtolower($method);

		if (isset(static::$methods[$method]) === true) {
			return (static::$methods[$method])(clone $this, ...$arguments);
		}

		if (isset(static::$aliases[$method]) === true) {
			$method = strtolower(static::$aliases[$method]);

			if (isset(static::$methods[$method]) === true) {
				return (static::$methods[$method])(clone $this, ...$arguments);
			}
		}

		return $this;
	}

	/**
	 * Simplifies the var_dump result
	 * @codeCoverageIgnore
	 *
	 * @see Field::toArray
	 */
	public function __debugInfo(): array
	{
		return $this->toArray();
	}

	/**
	 * Makes it possible to simply echo
	 * or stringify the entire object
	 *
	 * @see Field::toString
	 */
	public function __toString(): string
	{
		return $this->toString();
	}

	/**
	 * Checks if the field exists in the content data array
	 */
	public function exists(): bool
	{
		return $this->parent->content()->has($this->key);
	}

	/**
	 * Checks if the field content is empty
	 */
	public function isEmpty(): bool
	{
		$value = $this->value;

		if (is_string($value) === true) {
			$value = trim($value);
		}

		return
			$value === null ||
			$value === '' ||
			$value === [] ||
			$value === '[]';
	}

	/**
	 * Checks if the field content is not empty
	 */
	public function isNotEmpty(): bool
	{
		return $this->isEmpty() === false;
	}

	/**
	 * Returns the name of the field
	 */
	public function key(): string
	{
		return $this->key;
	}

	/**
	 * @see Field::parent()
	 */
	public function model(): ModelWithContent|null
	{
		return $this->parent;
	}

	/**
	 * Provides a fallback if the field value is empty
	 *
	 * @return $this|static
	 */
	public function or(mixed $fallback = null): static
	{
		if ($this->isNotEmpty()) {
			return $this;
		}

		if ($fallback instanceof self) {
			return $fallback;
		}

		$field = clone $this;
		$field->value = $fallback;
		return $field;
	}

	/**
	 * Returns the parent object of the field
	 */
	public function parent(): ModelWithContent|null
	{
		return $this->parent;
	}

	/**
	 * Converts the Field object to an array
	 */
	public function toArray(): array
	{
		return [$this->key => $this->value];
	}

	/**
	 * Returns the field value as string
	 */
	public function toString(): string
	{
		return (string)$this->value;
	}

	/**
	 * Returns the field content. If a new value is passed,
	 * the modified field will be returned. Otherwise it
	 * will return the field value.
	 */
	public function value(string|Closure $value = null): mixed
	{
		if ($value === null) {
			return $this->value;
		}

		if ($value instanceof Closure) {
			$value = $value->call($this, $this->value);
		}

		$clone = clone $this;
		$clone->value = (string)$value;

		return $clone;
	}
}
