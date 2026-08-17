<?php

namespace Kirby\Blueprint;

use Kirby\Cms\ModelWithContent;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Field;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;
use Throwable;

/**
 * The Normalizer takes the raw props of a blueprint and
 * converts them into a proper tab layout. Loose fields are
 * wrapped in a section, loose sections in a column and loose
 * columns in a tab. On the way, it collects the props of all
 * sections and fields in the blueprint.
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class Normalizer
{
	/**
	 * Global field definitions that can be referenced
	 * in tabs, columns and sections.
	 */
	protected array $fieldDefinitions = [];

	// Collected props of all fields in the blueprint
	protected array $fields = [];

	// Collected blueprint props
	protected array $props;

	// Collected props of all sections in the blueprint
	protected array $sections = [];

	public function __construct(
		protected ModelWithContent $model,
		array $props
	) {
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
	 * Converts all field definitions, that are not
	 * wrapped in a fields section into a generic
	 * fields section.
	 */
	protected function convertFieldsToSections(
		string $tabName,
		array $props
	): array {
		if (isset($props['fields']) === false) {
			return $props;
		}

		// Resolve field references and extract inline definitions
		$fields = $this->resolveFieldReferences($props['fields']);

		// wrap all fields in a section
		$props['sections'] = [
			$tabName . '-fields' => [
				'type'   => 'fields',
				'fields' => $fields
			]
		];

		unset($props['fields']);

		return $props;
	}

	/**
	 * Converts all sections that are not wrapped in
	 * columns, into a single generic column.
	 */
	protected function convertSectionsToColumns(
		string $tabName,
		array $props
	): array {
		if (isset($props['sections']) === false) {
			return $props;
		}

		// wrap everything in one big column
		$props['columns'] = [
			[
				'width'    => '1/1',
				'sections' => $props['sections']
			]
		];

		unset($props['sections']);

		return $props;
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
		$props = $this->convertFieldsToSections('main', $props);
		$props = $this->convertSectionsToColumns('main', $props);
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

			$columnProps = $this->convertFieldsToSections(
				$tabName . '-col-' . $columnKey,
				$columnProps
			);

			// inject getting started info, if the sections are empty
			if (empty($columnProps['sections']) === true) {
				$columnProps['sections'] = [
					$tabName . '-info-' . $columnKey => [
						'label' => 'Column (' . ($columnProps['width'] ?? '1/1') . ')',
						'type'  => 'info',
						'text'  => 'No sections yet'
					]
				];
			}

			$columns[$columnKey] = [
				...$columnProps,
				'width'    => $columnProps['width'] ?? '1/1',
				'sections' => $this->normalizeSections(
					$tabName,
					$columnProps['sections'] ?? []
				)
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
	 * Normalizes all required keys in sections
	 *
	 * @return array<string, array>
	 */
	protected function normalizeSections(
		string $tabName,
		array $sections
	): array {
		foreach ($sections as $sectionName => $sectionProps) {
			// unset / remove section if its property is false
			if ($sectionProps === false) {
				unset($sections[$sectionName]);
				continue;
			}

			// fallback to default props when true is passed
			if ($sectionProps === true) {
				$sectionProps = [];
			}

			// inject all section extensions
			$sectionProps = Blueprint::extend($sectionProps);

			$sections[$sectionName] = $sectionProps = [
				...$sectionProps,
				'name' => $sectionName,
				'type' => $type = $sectionProps['type'] ?? $sectionName
			];

			if (empty($type) === true || is_string($type) === false) {
				$sections[$sectionName] = [
					'name'  => $sectionName,
					'label' => 'Invalid section type for section "' . $sectionName . '"',
					'type'  => 'info',
					'text'  => 'The following section types are available: ' . Blueprint::helpList(array_keys(Section::$types))
				];
			} elseif (isset(Section::$types[$type]) === false) {
				$sections[$sectionName] = [
					'name'  => $sectionName,
					'label' => 'Invalid section type ("' . $type . '")',
					'type'  => 'info',
					'text'  => 'The following section types are available: ' . Blueprint::helpList(array_keys(Section::$types))
				];
			}

			if ($sectionProps['type'] === 'fields') {
				// Resolve field references before normalizing
				$sectionFields = $this->resolveFieldReferences($sectionProps['fields'] ?? []);
				$fields = static::normalizeFieldsProps($sectionFields);

				// inject guide fields guide
				if ($fields === []) {
					$fields = [
						$tabName . '-info' => [
							'label' => 'Fields',
							'text'  => 'No fields yet',
							'type'  => 'info'
						]
					];
				} else {
					foreach ($fields as $fieldName => $fieldProps) {
						if (isset($this->fields[$fieldName]) === true) {
							$this->fields[$fieldName] = $fields[$fieldName] = [
								'type'  => 'info',
								'label' => $fieldProps['label'] ?? 'Error',
								'text'  => 'The field <strong>"' . $fieldName . '"</strong> already exists in your blueprint',
								'theme' => 'negative'
							];
						} else {
							$this->fields[$fieldName] = $fieldProps;
						}
					}
				}

				$sections[$sectionName]['fields'] = $fields;
			}
		}

		// store all normalized sections
		$this->sections = [...$this->sections, ...$sections];

		return $sections;
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

			$tabProps = $this->convertFieldsToSections($tabName, $tabProps);
			$tabProps = $this->convertSectionsToColumns($tabName, $tabProps);

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
	 * Returns the props of all sections in the blueprint
	 *
	 * @return array<string, array>
	 */
	public function sections(): array
	{
		return $this->sections;
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
}
