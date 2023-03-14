<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;

/**
 * The StructureObject represents each item
 * in a Structure collection. StructureObjects
 * behave pretty much the same as Pages or Users
 * and have a Content object to access their fields.
 * All fields in a StructureObject are therefore also
 * wrapped in a Field object and can be accessed in
 * the same way as Page fields. They also use the same
 * Field methods.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class StructureObject
{
	use HasSiblings;

	/**
	 * The content
	 */
	protected Content|array $content;
	protected string $id;
	protected ModelWithContent|null $parent;

	/**
	 * The parent Structure collection
	 */
	protected Structure|null $structure;

	/**
	 * Creates a new StructureObject with the given props
	 *
	 * @param array $props
	 */
	public function __construct(array $props)
	{
		if (isset($props['id']) === false) {
			throw new InvalidArgumentException('The property "id" is required');
		}

		$this->id        = $props['id'];
		$this->parent    = $props['parent'] ?? App::instance()->site();
		$this->structure = $props['structure'] ?? null;
		$this->content   = $props['content'] ?? [];
	}

	/**
	 * Modified getter to also return fields
	 * from the object's content
	 */
	public function __call(string $method, array $arguments = []): mixed
	{
		// public property access
		if (isset($this->$method) === true) {
			return $this->$method;
		}

		return $this->content()->get($method);
	}

	/**
	 * Returns the content
	 */
	public function content(): Content
	{
		if ($this->content instanceof Content) {
			return $this->content;
		}

		if (is_array($this->content) !== true) {
			$this->content = [];
		}

		return $this->content = new Content($this->content, $this->parent());
	}

	/**
	 * Returns the required id
	 */
	public function id(): string
	{
		return $this->id;
	}

	/**
	 * Compares the current object with the given structure object
	 */
	public function is(mixed $structure): bool
	{
		if ($structure instanceof self === false) {
			return false;
		}

		return $this === $structure;
	}

	/**
	 * Returns the parent object
	 */
	public function parent(): ModelWithContent|null
	{
		return $this->parent;
	}

	/**
	 * Returns the parent Structure collection as siblings
	 */
	protected function siblingsCollection(): Structure|null
	{
		return $this->structure;
	}

	/**
	 * Converts all fields in the object to a
	 * plain associative array. The id is
	 * injected into the array afterwards
	 * to make sure it's always present and
	 * not overloaded in the content.
	 */
	public function toArray(): array
	{
		$array = $this->content()->toArray();
		$array['id'] = $this->id();

		ksort($array);

		return $array;
	}
}
