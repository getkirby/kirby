<?php

namespace Kirby\Cms;

use Kirby\Content\Content;

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
class StructureObject extends Item
{
	use HasMethods;

	public const ITEMS_CLASS = Structure::class;

	protected Content $content;

	/**
	 * Creates a new StructureObject with the given props
	 */
	public function __construct(array $params = [])
	{
		parent::__construct($params);

		$this->content = new Content(
			$params['content'] ?? $params['params'] ?? [],
			$this->parent
		);
	}

	/**
	 * Modified getter to also return fields
	 * from the object's content
	 */
	public function __call(string $method, array $args = []): mixed
	{
		// structure object methods
		if ($this->hasMethod($method) === true) {
			return $this->callMethod($method, $args);
		}

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
		return $this->content;
	}

	/**
	 * Converts all fields in the object to a
	 * plain associative array. The id is
	 * injected from the parent into the array
	 * to make sure it's always present and
	 * not overloaded by the content.
	 */
	public function toArray(): array
	{
		return array_merge(
			$this->content()->toArray(),
			parent::toArray()
		);
	}
}
