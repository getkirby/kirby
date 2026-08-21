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
 * and loose columns in a tab. On the way, it collects the
 * props of all fields in the blueprint.
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
	protected array $fieldDefinitions = [];

	// Collected props of all fields in the blueprint
	protected array $fields = [];

	// Collected blueprint props
	protected array $props;

	public function __construct(array $props)
	{
		$this->props = $this->normalize($props);
	}

	/**
	 * Converts all column definitions, that
	 * are not wrapped in a tab, into a generic tab
	 */
	protected function convertColumnsToTabs(
		string $tabName,
		array $props
	): array {
		if (isset($props['columns']) === false) {
			return $props;
		}

		// wrap everything in a main tab
		$props['tabs'] = [
			$tabName => [
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

		// field references have to be resolved before the merge,
		// otherwise their numeric keys collide with each other
		$fields = $this->resolveFieldReferences($props['fields'] ?? []);

		// sections and fields share a single namespace
		$add = static function (array $fields, $name, $props): array {
			if (isset($fields[$name]) === true) {
				$fields[$name] = static::duplicateFieldError($name, $props);
				return $fields;
			}

			$fields[$name] = $props;
			return $fields;
		};

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
				foreach ($this->unwrapFieldsSection($section) as $key => $field) {
					$fields = $add($fields, $key, $field);
				}

				continue;
			}

			$fields = $add($fields, $name, static::sectionToField($name, $section));
		}

		$props['fields'] = $fields;

		unset($props['sections']);

		return $props;
	}

	/**
	 * Creates an error field for a field name that is already taken
	 */
	protected static function duplicateFieldError(
		string|int $name,
		array|string|bool|null $props = null
	): array {
		return [
			'label' => (is_array($props) === true ? $props['label'] ?? null : null) ?? 'Error',
			'name'  => $name,
			'text'  => 'The field <strong>"' . $name . '"</strong> already exists in your blueprint',
			'theme' => 'negative',
			'type'  => 'info',
		];
	}

	/**
	 * Extracts global field definitions from root level.
	 * When layout elements exist, adds fields to the registry
	 * and removes them from props so they can be referenced.
	 * When no layout exists, fields stay in props for the
	 * existing backwards-compatible behavior.
	 */
	protected function extractFieldReferences(array $props): array
	{
		if (isset($props['fields']) === false) {
			return $props;
		}

		// Check if layout elements exist
		$hasLayout = isset($props['tabs']) === true ||
					 isset($props['sections']) === true ||
					 isset($props['columns']) === true;

		// Only store definitions and remove from props when layout exists
		// (fields will be referenced from layout, not processed inline)
		if ($hasLayout === true) {
			$this->fieldDefinitions = static::normalizeFieldsProps($props['fields']);
			unset($props['fields']);
		}

		return $props;
	}

	/**
	 * Returns the props of all fields in the blueprint
	 *
	 * @return array<string, array>
	 */
	public function fields(): array
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
		$props = $this->convertColumnsToTabs('main', $props);

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
		foreach ($columns as $columnKey => $columnProps) {
			// unset/remove column if its property is not array
			if (is_array($columnProps) === false) {
				unset($columns[$columnKey]);
				continue;
			}

			$columnProps = $this->convertSectionsToFields($columnProps);

			// inject getting started info, if the fields are empty
			if (empty($columnProps['fields']) === true) {
				$columnProps['fields'] = [
					$tabName . '-info-' . $columnKey => [
						'label' => 'Column (' . ($columnProps['width'] ?? '1/1') . ')',
						'type'  => 'info',
						'text'  => 'No fields yet'
					]
				];
			}

			$columns[$columnKey] = [
				...$columnProps,
				'width'  => $columnProps['width'] ?? '1/1',
				'fields' => $this->normalizeFields($columnProps['fields'])
			];
		}

		return $columns;
	}

	/**
	 * Normalize field props for a single field
	 *
	 * @throws InvalidArgumentException If the filed name is missing or the field type is invalid
	 */
	public static function normalizeFieldProps(array|string $props): array
	{
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
			$props['fields'] = static::normalizeFieldsProps($props['fields']);
		}

		// groups don't need all the crap
		if ($type === 'group') {
			$fields = $props['fields'];

			if (isset($props['when']) === true) {
				$fields = array_map(
					fn ($field) => array_replace_recursive(['when' => $props['when']], $field),
					$fields
				);
			}

			return [
				'fields' => $fields,
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
	 * @return array<string, array>
	 */
	protected function normalizeFields(array $fields): array
	{
		// resolve field references before normalizing
		$fields = $this->resolveFieldReferences($fields);
		$fields = static::normalizeFieldsProps($fields);

		foreach ($fields as $name => $props) {
			if (isset($this->fields[$name]) === true) {
				$fields[$name] = static::duplicateFieldError($name, $props);
				continue;
			}

			$this->fields[$name] = $props;
		}

		return $fields;
	}

	/**
	 * Normalizes all fields and adds automatic labels,
	 * types and widths.
	 *
	 * @return array<string, array>
	 */
	public static function normalizeFieldsProps(mixed $fields): array
	{
		if (is_array($fields) === false) {
			$fields = [];
		}

		foreach ($fields as $fieldName => $fieldProps) {
			// extend field from string
			if (is_string($fieldProps) === true) {
				$fieldProps = [
					'extends' => $fieldProps,
					'name'    => $fieldName
				];
			}

			// use the name as type definition
			if ($fieldProps === true) {
				$fieldProps = [];
			}

			// unset / remove field if its property is false
			if ($fieldProps === false) {
				unset($fields[$fieldName]);
				continue;
			}

			// inject the name
			$fieldProps['name'] = $fieldName;

			// create all props
			try {
				$fieldProps = static::normalizeFieldProps($fieldProps);
			} catch (Throwable $e) {
				$fieldProps = Blueprint::fieldError($fieldName, $e->getMessage());
			}

			// resolve field groups
			if ($fieldProps['type'] === 'group') {
				if (
					empty($fieldProps['fields']) === false &&
					is_array($fieldProps['fields']) === true
				) {
					$index  = array_search($fieldName, array_keys($fields));
					$fields = [
						...array_slice($fields, 0, $index),
						...$fieldProps['fields'] ?? [],
						...array_slice($fields, $index + 1)
					];
				} else {
					unset($fields[$fieldName]);
				}
			} else {
				$fields[$fieldName] = $fieldProps;
			}
		}

		return $fields;
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
			$tabs = [];
		}

		foreach ($tabs as $tabName => $tabProps) {
			// unset / remove tab if its property is false
			if ($tabProps === false) {
				unset($tabs[$tabName]);
				continue;
			}

			// inject all tab extensions
			$tabProps = Blueprint::extend($tabProps);

			$tabProps = $this->convertSectionsToFields($tabProps);
			$tabProps = $this->convertFieldsToColumns($tabProps);

			$tabs[$tabName] = [
				...$tabProps,
				'columns' => $this->normalizeColumns($tabName, $tabProps['columns'] ?? []),
				'icon'    => $tabProps['icon']  ?? null,
				'label'   => $this->i18n($tabProps['label'] ?? Str::label($tabName)),
				'name'    => $tabName,
			];
		}

		return $tabs;
	}

	/**
	 * Returns the normalized props of the blueprint
	 */
	public function props(): array
	{
		return $this->props;
	}

	/**
	 * Resolves field references (numeric keys with string values)
	 * to full definitions from the global registry
	 */
	protected function resolveFieldReferences(array $fields): array
	{
		$resolved = [];

		foreach ($fields as $key => $value) {
			// Numeric key with string value = field reference
			if (is_int($key) === true && is_string($value) === true) {
				$fieldName = $value;

				if (isset($this->fieldDefinitions[$fieldName]) === true) {
					$resolved[$fieldName] = $this->fieldDefinitions[$fieldName];
				} else {
					$resolved[$fieldName] = Blueprint::fieldError(
						$fieldName,
						'Referenced field "' . $fieldName . '" is not defined in fields'
					);
				}
			} else {
				// Inline field definition - keep as is
				$resolved[$key] = $value;
			}
		}

		return $resolved;
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
	 * Unwraps a `fields` section into its own fields. A `when`
	 * condition on the section is pushed down onto every field,
	 * the same way it works for field groups.
	 */
	protected function unwrapFieldsSection(array $props): array
	{
		$fields = $props['fields'] ?? [];

		if (is_array($fields) === false) {
			return [];
		}

		// references are resolved first, so that the `when` condition
		// can be pushed down onto them just like onto inline fields
		$fields = $this->resolveFieldReferences($fields);

		if (isset($props['when']) === true) {
			$fields = A::map(
				$fields,
				fn ($field) => is_array($field) === true
					? array_replace_recursive(['when' => $props['when']], $field)
					: $field
			);
		}

		return $fields;
	}
}
