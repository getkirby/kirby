<?php

namespace Kirby\Blueprint;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Field;
use Kirby\Toolkit\A;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;
use Throwable;

/**
 * The Normalizer takes the raw props of a blueprint and
 * converts them into a proper tab layout. Sections are
 * converted to fields, loose fields are wrapped in a column
 * and loose columns in a tab.
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class Normalizer
{
	/**
	 * Maps the legacy section types onto their field
	 * equivalent. The `fields` section is not part of this
	 * map, because it is unwrapped into its own fields.
	 *
	 * @unstable
	 */
	public static array $sectionFields = [
		'files' => 'filelist',
		'info'  => 'info',
		'pages' => 'pagelist',
		'stats' => 'stats',
	];

	/**
	 * Global field definitions that can be referenced
	 * in tabs, columns and fields.
	 */
	protected FieldsRegistry $definitions;

	// Collected props of all fields in the blueprint
	protected FieldsRegistry $fields;

	// Collected blueprint props
	protected array $props;

	public function __construct(array $props)
	{
		$this->definitions = new FieldsRegistry();
		$this->fields      = new FieldsRegistry();
		$this->props       = $this->normalize($props);
	}

	/**
	 * Converts all column definitions, that
	 * are not wrapped in a tab, into a generic tab
	 */
	protected function convertColumnsToTabs(array $props): array
	{
		if (isset($props['columns']) === false) {
			return $props;
		}

		// wrap everything in a main tab
		$props['tabs'] = [
			'main' => [
				'columns' => $props['columns']
			]
		];

		unset($props['columns']);

		return $props;
	}

	/**
	 * Converts all field definitions, that are not wrapped
	 * in columns, into a single generic column.
	 */
	protected function convertFieldsToColumns(array $props): array
	{
		if (isset($props['fields']) === false) {
			return $props;
		}

		// wrap everything in one big column
		$props['columns'] = [
			[
				'width'  => '1/1',
				'fields' => $props['fields']
			]
		];

		unset($props['fields']);

		return $props;
	}

	/**
	 * Converts all section definitions into field definitions.
	 * Sections only exist as a legacy shortcut in blueprints and
	 * are mapped onto their field equivalent.
	 */
	protected function convertSectionsToFields(array $props): array
	{
		if (isset($props['sections']) === false) {
			return $props;
		}

		$fields = static::resolve($props['fields'] ?? [], $this->definitions);

		foreach ($props['sections'] as $name => $section) {
			// unset / remove section if its property is false
			if ($section === false) {
				continue;
			}

			// fallback to default props when true is passed
			if ($section === true) {
				$section = [];
			}

			// inject all section extensions
			$section = Blueprint::extend($section);

			// a fields section is unwrapped into the parent's own fields
			if (($section['type'] ?? $name) === 'fields') {
				$fields = [...$fields, ...$this->unwrapFieldsSection($section)];
				continue;
			}

			$fields[] = static::sectionToField($name, $section);
		}

		$props['fields'] = $fields;

		unset($props['sections']);

		return $props;
	}

	/**
	 * Expands all field shortcuts into full props and splices the
	 * members of a field group into their siblings. The result is a
	 * list, which cannot lose a field to a name that is already
	 * taken, so the names are only claimed afterwards.
	 *
	 * @return array<int, array>
	 */
	protected static function expand(
		mixed $fields,
		FieldsRegistry|null $definitions = null
	): array {
		$expanded = [];

		foreach (static::resolve($fields, $definitions) as $props) {
			try {
				$props = static::normalizeFieldProps($props, $definitions);
			} catch (Throwable $e) {
				$props = Blueprint::fieldError($props['name'], $e->getMessage());
			}

			// a field group takes the place of its own members,
			// which are already expanded at this point
			if ($props['type'] === 'group') {
				$expanded = [
					...$expanded,
					...array_values($props['fields'] ?? [])
				];

				continue;
			}

			$expanded[] = $props;
		}

		return $expanded;
	}

	/**
	 * Extracts the field definitions at root level, which can be
	 * referenced by name from tabs, columns and fields
	 */
	protected function extractFieldReferences(array $props): array
	{
		if (isset($props['fields']) === false) {
			return $props;
		}

		// without a layout to reference them from, the fields
		// stay in the props and are processed inline
		if (
			isset($props['tabs']) === false &&
			isset($props['sections']) === false &&
			isset($props['columns']) === false
		) {
			return $props;
		}

		$this->definitions = new FieldsRegistry(
			static::normalizeFieldsProps($props['fields'])
		);

		unset($props['fields']);

		return $props;
	}

	/**
	 * Returns the collected fields of the blueprint
	 */
	public function fields(): FieldsRegistry
	{
		return $this->fields;
	}

	/**
	 * Used to translate any label, heading, etc.
	 */
	protected function i18n(mixed $value, mixed $fallback = null): mixed
	{
		return I18n::translate($value, $fallback) ?? $value;
	}

	/**
	 * Normalizes the raw props of the blueprint
	 */
	protected function normalize(array $props): array
	{
		// extend the blueprint in general
		$props = Blueprint::extend($props);

		// normalize the name
		$props['name'] ??= 'default';

		// normalize and translate the title
		$props['title'] ??= Str::label($props['name']);
		$props['title']   = $this->i18n($props['title']);

		// extract global field definitions before normalization
		$props = $this->extractFieldReferences($props);

		// convert all shortcuts
		$props = $this->convertSectionsToFields($props);
		$props = $this->convertFieldsToColumns($props);
		$props = $this->convertColumnsToTabs($props);

		// normalize all tabs
		$props['tabs'] = $this->normalizeTabs($props['tabs'] ?? []);

		return $props;
	}

	/**
	 * Normalizes all required props in a column setup
	 *
	 * @return array<string|int, array>
	 */
	protected function normalizeColumns(string $tabName, array $columns): array
	{
		$normalized = [];

		foreach ($columns as $key => $props) {
			// unset / remove column if its props are not an array
			if (is_array($props) === false) {
				continue;
			}

			$props = $this->convertSectionsToFields($props);

			// inject getting started info, if the fields are empty
			if (empty($props['fields']) === true) {
				$props['fields'] = [
					$tabName . '-info-' . $key => [
						'label' => 'Column (' . ($props['width'] ?? '1/1') . ')',
						'type'  => 'info',
						'text'  => 'No fields yet'
					]
				];
			}

			$normalized[$key] = [
				...$props,
				'width'  => $props['width'] ?? '1/1',
				'fields' => $this->normalizeFields($props['fields'])
			];
		}

		return $normalized;
	}

	/**
	 * Normalize field props for a single field
	 *
	 * @throws InvalidArgumentException If the filed name is missing or the field type is invalid
	 */
	public static function normalizeFieldProps(
		array|string $props,
		FieldsRegistry|null $definitions = null
	): array {
		$props = Blueprint::extend($props);

		if (isset($props['name']) === false) {
			throw new InvalidArgumentException(
				message: 'The field name is missing'
			);
		}

		$name = $props['name'];
		$type = $props['type'] ?? $name;

		if ($type !== 'group' && isset(Field::$types[$type]) === false) {
			throw new InvalidArgumentException(
				message: 'Invalid field type ("' . $type . '")'
			);
		}

		// support for nested fields
		if (isset($props['fields']) === true) {
			$props['fields'] = static::normalizeFieldsProps(
				$props['fields'],
				$definitions
			);
		}

		// a group is nothing but a wrapper for its own fields
		if ($type === 'group') {
			return [
				'fields' => static::when(
					$props['fields'] ?? [],
					$props['when'] ?? null
				),
				'name'   => $name,
				'type'   => $type
			];
		}

		// add some useful defaults
		return [
			...$props,
			'label' => $props['label'] ?? Str::label($name),
			'name'  => $name,
			'type'  => $type,
			'width' => $props['width'] ?? '1/1',
		];
	}

	/**
	 * Normalizes all fields of a column and registers
	 * them in the global field collection
	 *
	 * @return array<string|int, array>
	 */
	protected function normalizeFields(array $fields): array
	{
		// the fields of the entire blueprint share a single
		// namespace, so their names are only claimed here
		return $this->fields->add(static::expand($fields, $this->definitions));
	}

	/**
	 * Normalizes all fields and adds automatic labels,
	 * types and widths.
	 *
	 * @return array<string|int, array>
	 */
	public static function normalizeFieldsProps(
		mixed $fields,
		FieldsRegistry|null $definitions = null
	): array {
		return (new FieldsRegistry())->add(static::expand($fields, $definitions));
	}

	/**
	 * Normalizes blueprint options. This must be used in the
	 * constructor of an extended blueprint class, if you want
	 * to make use of it.
	 */
	public static function normalizeOptions(
		array|string|bool|null $options,
		array $defaults,
		array $aliases = []
	): array {
		// return defaults when options are not defined or set to true
		if ($options === true) {
			return $defaults;
		}

		// set all options to false
		if ($options === false) {
			return array_fill_keys(array_keys($defaults), false);
		}

		// extend options if possible
		$options = Blueprint::extend($options);

		foreach ($options as $key => $value) {
			$alias = $aliases[$key] ?? null;

			if ($alias !== null) {
				$options[$alias] ??= $value;
				unset($options[$key]);
			}
		}

		return [...$defaults, ...$options];
	}

	/**
	 * Normalizes all required keys in tabs
	 *
	 * @return array<string, array>
	 */
	protected function normalizeTabs(mixed $tabs): array
	{
		if (is_array($tabs) === false) {
			return [];
		}

		$normalized = [];

		foreach ($tabs as $name => $props) {
			// unset / remove tab if its props are false
			if ($props === false) {
				continue;
			}

			// inject all tab extensions
			$props = Blueprint::extend($props);
			$props = $this->convertSectionsToFields($props);
			$props = $this->convertFieldsToColumns($props);

			$normalized[$name] = [
				...$props,
				'columns' => $this->normalizeColumns($name, $props['columns'] ?? []),
				'icon'    => $props['icon'] ?? null,
				'label'   => $this->i18n($props['label'] ?? Str::label($name)),
				'name'    => $name,
			];
		}

		return $normalized;
	}

	/**
	 * Returns the normalized props of the blueprint
	 */
	public function props(): array
	{
		return $this->props;
	}

	/**
	 * Resolves every field shortcut (an `extends` string, `true`
	 * or a reference to a field definition) into plain props and
	 * returns them as a list in which each entry carries its own name.
	 *
	 * @return array<int, array>
	 */
	protected static function resolve(
		mixed $fields,
		FieldsRegistry|null $definitions = null
	): array {
		if (is_array($fields) === false) {
			return [];
		}

		$list = [];

		foreach ($fields as $key => $props) {
			// a numeric key with a string value references
			// one of the field definitions by name
			if (is_int($key) === true && is_string($props) === true) {
				$list[] = $definitions?->get($props) ?? Blueprint::fieldError(
					$props,
					'Referenced field "' . $props . '" is not defined in fields'
				);

				continue;
			}

			// extend field from string
			if (is_string($props) === true) {
				$props = ['extends' => $props];
			}

			// use the name as type definition
			if ($props === true) {
				$props = [];
			}

			// unset / remove field if its props are false
			if (is_array($props) === false) {
				continue;
			}

			// inject the name, unless the entry carries it already
			$props['name'] = match (is_int($key)) {
				true  => $props['name'] ?? $key,
				false => $key
			};

			$list[] = $props;
		}

		return $list;
	}

	/**
	 * Creates an error field for an invalid section type
	 */
	protected static function sectionError(string $name, string $label): array
	{
		$types = ['fields', ...array_keys(static::$sectionFields)];

		return [
			'label' => $label,
			'name'  => $name,
			'text'  => 'The following section types are available: ' . Blueprint::helpList($types),
			'theme' => 'negative',
			'type'  => 'info',
		];
	}

	/**
	 * Converts a single section definition into a field definition
	 * by mapping the section type onto its field equivalent
	 */
	protected static function sectionToField(string $name, array $props): array
	{
		$type = $props['type'] ?? $name;

		// `headline` is the deprecated version of `label`
		if (isset($props['headline']) === true) {
			$props['label'] ??= $props['headline'];
			unset($props['headline']);
		}

		unset($props['type']);

		if (empty($type) === true || is_string($type) === false) {
			return static::sectionError(
				$name,
				'Invalid section type for section "' . $name . '"'
			);
		}

		if ($field = static::$sectionFields[$type] ?? null) {
			return [
				...$props,
				'name' => $name,
				'type' => $field
			];
		}

		return static::sectionError(
			$name,
			'Invalid section type ("' . $type . '")'
		);
	}

	/**
	 * Returns the normalized tabs of the blueprint
	 *
	 * @return array<string, array>
	 */
	public function tabs(): array
	{
		return $this->props['tabs'] ?? [];
	}

	/**
	 * Unwraps a `fields` section into a list of its own fields
	 *
	 * @return array<int, array>
	 */
	protected function unwrapFieldsSection(array $props): array
	{
		return static::when(
			static::resolve($props['fields'] ?? [], $this->definitions),
			$props['when'] ?? null
		);
	}

	/**
	 * Pushes the `when` condition of a wrapper (a fields group or
	 * a `fields` section) down onto every field it wraps
	 */
	protected static function when(array $fields, mixed $when): array
	{
		if ($when === null) {
			return $fields;
		}

		return A::map(
			$fields,
			fn ($field) => array_replace_recursive(['when' => $when], $field)
		);
	}
}
