<?php

namespace Kirby\Content;

use Closure;
use Kirby\Cms\App;
use Kirby\Cms\ModelWithContent;
use Stringable;

/**
 * Every field in a Kirby content text file
 * is being converted into such a Field object.
 *
 * The FieldMethods trait is used for methods that enable our
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
class Field implements Stringable
{
	use FieldMethods;

	/**
	 * Creates a new field object
	 *
	 * @param \Kirby\Cms\ModelWithContent|null $parent Parent object if available. This will be the page, site, user or file to which the content belongs
	 * @param string $key The field name
	 */
	public function __construct(
		protected ModelWithContent|null $parent,
		protected string $key,
		public mixed $value
	) {
	}

	/**
	 * Simplifies the var_dump result
	 * @codeCoverageIgnore
	 *
	 * @see self::toArray()
	 */
	public function __debugInfo(): array
	{
		return $this->toArray();
	}

	/**
	 * Makes it possible to simply echo
	 * or stringify the entire object
	 *
	 * @see self::toString()
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
	 * Returns the name of the field
	 */
	public function key(): string
	{
		return $this->key;
	}

	/**
	 * Returns the Kirby instance
	 * @since 5.1.0
	 */
	public function kirby(): App
	{
		return $this->parent?->kirby() ?? App::instance();
	}

	/**
	 * @see self::parent()
	 */
	public function model(): ModelWithContent|null
	{
		return $this->parent;
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
	public function value(string|Closure|null $value = null): mixed
	{
		if ($value === null) {
			return $this->value;
		}

		$clone = clone $this;

		if ($value instanceof Closure) {
			$value = $value->call($clone, $clone->value);
		}

		$clone->value = (string)$value;

		return $clone;
	}
}
