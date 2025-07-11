<?php

namespace Kirby\Content;

use Kirby\Cms\Blueprint;
use Kirby\Cms\File;
use Kirby\Cms\ModelWithContent;
use Kirby\Form\Form;

/**
 * The Content class handles all fields
 * for content from pages, the site and users
 *
 * @package   Kirby Content
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Content
{
	/**
	 * The raw data array
	 */
	protected array $data = [];

	/**
	 * Cached field objects
	 * Once a field is being fetched
	 * it is added to this array for
	 * later reuse
	 */
	protected array $fields = [];

	/**
	 * A potential parent object.
	 * Not necessarily needed. Especially
	 * for testing, but field methods might
	 * need it.
	 */
	protected ModelWithContent|null $parent;

	/**
	 * Magic getter for content fields
	 */
	public function __call(string $name, array $arguments = []): Field
	{
		return $this->get($name);
	}

	/**
	 * Creates a new Content object
	 *
	 * @param bool $normalize Set to `false` if the input field keys are already lowercase
	 */
	public function __construct(
		array $data = [],
		ModelWithContent|null $parent = null,
		bool $normalize = true
	) {
		if ($normalize === true) {
			$data = array_change_key_case($data, CASE_LOWER);
		}

		$this->data   = $data;
		$this->parent = $parent;
	}

	/**
	 * Same as `self::data()` to improve
	 * `var_dump` output
	 * @codeCoverageIgnore
	 *
	 * @see self::data()
	 */
	public function __debugInfo(): array
	{
		return $this->toArray();
	}

	/**
	 * Converts the content to a new blueprint
	 */
	public function convertTo(string $to): array
	{
		// prepare data
		$data    = [];
		$content = $this;

		// blueprints
		$old       = $this->parent->blueprint();
		$subfolder = dirname($old->name());
		$new       = Blueprint::factory(
			$subfolder . '/' . $to,
			$subfolder . '/default',
			$this->parent
		);

		// forms
		$oldForm = new Form(
			fields: $old->fields(),
			model: $this->parent
		);

		$newForm = new Form(
			fields: $new->fields(),
			model: $this->parent
		);

		// fields
		$oldFields = $oldForm->fields();
		$newFields = $newForm->fields();

		// go through all fields of new template
		foreach ($newFields as $newField) {
			$name     = $newField->name();
			$oldField = $oldFields->get($name);

			// field name and type matches with old template
			if ($oldField?->type() === $newField->type()) {
				$data[$name] = $content->get($name)->value();
			} else {
				$data[$name] = $newField->default();
			}
		}

		// if the parent is a file, overwrite the template
		// with the new template name
		if ($this->parent instanceof File) {
			$data['template'] = $to;
		}

		// preserve existing fields
		return [...$this->data, ...$data];
	}

	/**
	 * Returns the raw data array
	 */
	public function data(): array
	{
		return $this->data;
	}

	/**
	 * Returns all registered field objects
	 */
	public function fields(): array
	{
		foreach ($this->data as $key => $value) {
			$this->get($key);
		}
		return $this->fields;
	}

	/**
	 * Returns either a single field object
	 * or all registered fields
	 */
	public function get(string|null $key = null): Field|array
	{
		if ($key === null) {
			return $this->fields();
		}

		$key = strtolower($key);

		return $this->fields[$key] ??= new Field(
			$this->parent,
			$key,
			$this->data()[$key] ?? null
		);
	}

	/**
	 * Checks if a content field is set
	 */
	public function has(string $key): bool
	{
		return isset($this->data[strtolower($key)]) === true;
	}

	/**
	 * Returns all field keys
	 */
	public function keys(): array
	{
		return array_keys($this->data());
	}

	/**
	 * Returns a clone of the content object
	 * without the fields, specified by the
	 * passed key(s)
	 */
	public function not(string ...$keys): static
	{
		$copy = clone $this;
		$copy->fields = [];

		foreach ($keys as $key) {
			unset($copy->data[strtolower($key)]);
		}

		return $copy;
	}

	/**
	 * Returns the parent
	 * Site, Page, File or User object
	 */
	public function parent(): ModelWithContent|null
	{
		return $this->parent;
	}

	/**
	 * Set the parent model
	 *
	 * @return $this
	 */
	public function setParent(ModelWithContent $parent): static
	{
		$this->parent = $parent;
		return $this;
	}

	/**
	 * Returns the raw data array
	 *
	 * @see self::data()
	 */
	public function toArray(): array
	{
		return $this->data();
	}

	/**
	 * Updates the content in memory.
	 */
	public function update(
		array|null $content = null,
		bool $overwrite = false
	): static {
		$content = array_change_key_case((array)$content, CASE_LOWER);
		$this->data = $overwrite === true ? $content : array_merge($this->data, $content);

		// clear cache of Field objects
		$this->fields = [];

		return $this;
	}
}
