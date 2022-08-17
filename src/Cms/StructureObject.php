<?php

namespace Kirby\Cms;

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
class StructureObject extends Model
{
	use HasSiblings;

	/**
	 * The content
	 *
	 * @var \Kirby\Cms\Content|null
	 */
	protected $content;

	protected Field|null $field;

	/**
	 * @var string
	 */
	protected $id;

	/**
	 * @var \Kirby\Cms\ModelWithContent|null
	 */
	protected $parent;

	/**
	 * The parent Structure collection
	 *
	 * @var \Kirby\Cms\Structure|null
	 */
	protected $structure;

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
	 * Creates a new StructureObject with the given props
	 */
	public function __construct(array $props)
	{
		$this->field = $props['field'] ?? null;
		$this->setProperties($props);
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

	public function field(): Field|null
	{
		return $this->field;
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

		if ($this === $structure) {
			return true;
		}

		return $this->id() === $structure->id();
	}

	/**
	 * Returns the parent object
	 */
	public function parent(): ModelWithContent|null
	{
		return $this->parent;
	}

	/**
	 * Sets the Content object with the given parent
	 *
	 * @return $this
	 */
	protected function setContent(array|null $content = null): static
	{
		$this->content = $content;
		return $this;
	}

	/**
	 * Sets the id of the object.
	 * The id is required. The structure
	 * class will use the index, if no id is
	 * specified.
	 *
	 * @return $this
	 */
	protected function setId(string $id): static
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * Sets the parent Model
	 *
	 * @return $this
	 */
	protected function setParent(ModelWithContent|null $parent = null): static
	{
		$this->parent = $parent;
		return $this;
	}

	/**
	 * Sets the parent Structure collection
	 *
	 * @return $this
	 */
	protected function setStructure(Structure|null $structure = null): static
	{
		$this->structure = $structure;
		return $this;
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
