<?php

namespace Kirby\Blueprint;

use Kirby\Toolkit\Str;

/**
 * Collects the props of all fields in a blueprint
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class FieldsRegistry
{
	// Props of all fields, keyed by their declared name
	protected array $fields = [];

	// Declared names of all fields, keyed by their lowercase name
	protected array $names = [];

	/**
	 * Claims the given names without touching their props.
	 * The props can still be any of the shortcuts a blueprint
	 * allows, as they are only normalized later.
	 */
	public function __construct(array $fields = [])
	{
		foreach ($fields as $name => $props) {
			$this->fields[$name] = $props;
			$this->names[Str::lower((string)$name)] = $name;
		}
	}

	/**
	 * Adds the props of the given fields under names that are
	 * not taken yet and returns them, keyed by the names they
	 * ended up with. A list is keyed by the name in the props.
	 */
	public function add(array $fields): array
	{
		$added = [];

		foreach ($fields as $name => $props) {
			if (is_int($name) === true) {
				$name = $props['name'];
			}

			if ($this->has($name) === true) {
				$props = $this->conflict($name, $props);
				$name  = $props['name'];
			}

			$this->names[Str::lower((string)$name)] = $name;
			$added[$name] = $this->fields[$name] = $props;
		}

		return $added;
	}

	/**
	 * Creates an error field for a name that is already taken.
	 * The error gets a unique name of its own, so that the field
	 * which claimed the name first can stay intact.
	 */
	protected function conflict(string|int $name, array $props): array
	{
		$count = 1;

		while ($this->has($name . '-duplicate-' . $count) === true) {
			$count++;
		}

		return [
			'label' => $props['label'] ?? 'Error',
			'name'  => $name . '-duplicate-' . $count,
			'text'  => 'The field <strong>"' . $name . '"</strong> already exists in your blueprint',
			'theme' => 'negative',
			'type'  => 'info',
		];
	}

	/**
	 * Returns the props of a single field. The lookup is
	 * case-insensitive, just like the namespace.
	 */
	public function get(string|int $name): array|null
	{
		$name = $this->names[Str::lower((string)$name)] ?? null;

		return $name !== null ? $this->fields[$name] : null;
	}

	/**
	 * Checks if a name is already taken
	 */
	public function has(string|int $name): bool
	{
		return isset($this->names[Str::lower((string)$name)]) === true;
	}

	/**
	 * Returns the props of all fields,
	 * keyed by their declared name
	 */
	public function toArray(): array
	{
		return $this->fields;
	}
}
