<?php

namespace Kirby\Blueprint;

use Exception;
use Kirby\Cms\App;
use Kirby\Cms\ModelWithContent;
use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\F;
use Kirby\Form\Field;
use Kirby\Form\Field\SectionField;
use Kirby\Toolkit\A;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;
use Throwable;

/**
 * The Blueprint class normalizes an array from a
 * blueprint file and converts sections, columns, fields
 * etc. into a correct tab layout.
 *
 * @package   Kirby Blueprint
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Blueprint
{
	public static array $presets = [];
	public static array $loaded = [];

	protected AcceptRules $acceptRules;

	/**
	 * Global field definitions that can be referenced
	 * in tabs, columns and sections.
	 */
	protected array $fieldDefinitions = [];

	// Cache for collected field props
	protected array $fields = [];
	protected ModelWithContent $model;
	protected array $props;
	protected array $sections = [];
	protected array $tabs = [];

	/**
	 * Magic getter/caller for any blueprint prop
	 */
	public function __call(string $key, array|null $arguments = null): mixed
	{
		return $this->props[$key] ?? null;
	}

	/**
	 * Creates a new blueprint object with the given props
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the blueprint model is missing
	 */
	public function __construct(array $props)
	{
		if (empty($props['model']) === true) {
			throw new InvalidArgumentException(
				message: 'A blueprint model is required'
			);
		}

		if ($props['model'] instanceof ModelWithContent === false) {
			throw new InvalidArgumentException(
				message: 'Invalid blueprint model'
			);
		}

		$this->model = $props['model'];

		// the model should not be included in the props array
		unset($props['model']);

		// extend the blueprint in general
		$props = static::extend($props);

		// apply any blueprint preset
		$props = $this->preset($props);

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

		$this->props = $props;
	}

	/**
	 * Improved `var_dump` output
	 *
	 * @codeCoverageIgnore
	 */
	public function __debugInfo(): array
	{
		return $this->props;
	}

	/**
	 * Gathers what file templates are allowed in
	 * a model based on the blueprint
	 * @since 6.0.0
	 */
	public function acceptRules(): AcceptRules
	{
		return $this->acceptRules ??= new AcceptRules($this);
	}

	/**
	 * Gathers what file templates are allowed in
	 * this model based on the blueprint
	 */
	public function acceptedFileTemplates(string|null $inSection = null): array
	{
		return $this->acceptRules()->fileTemplates($inSection);
	}

	/**
	 * Gathers custom config for Panel view buttons
	 */
	public function buttons(): array|false|null
	{
		return $this->props['buttons'] ?? null;
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
	 * Extends the props with props from a given
	 * mixin, when an extends key is set or the
	 * props is just a string
	 *
	 * @param array|string $props
	 */
	public static function extend($props): array
	{
		if (is_string($props) === true) {
			$props = [
				'extends' => $props
			];
		}

		if ($extends = $props['extends'] ?? null) {
			foreach (A::wrap($extends) as $extend) {
				try {
					$mixin = static::find($extend);
					$mixin = static::extend($mixin);
					$props = A::merge($mixin, $props, A::MERGE_REPLACE);
				} catch (Exception) {
					// keep the props unextended if the snippet wasn't found
				}
			}

			// remove the extends flag
			unset($props['extends']);
		}

		return $props;
	}

	/**
	 * Extracts global field definitions from root level.
	 * When layout elements exist, adds fields to the registry
	 * and removes them from props so they can be referenced.
	 * When no layout exists, fields stay in props for the
	 * existing backwards-compatible behavior.
	 *
	 * @since 6.0.0
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
			$this->fieldDefinitions = static::fieldsProps($props['fields']);
			unset($props['fields']);
		}

		return $props;
	}

	/**
	 * Create a new blueprint for a model
	 */
	public static function factory(
		string $name,
		string|null $fallback,
		ModelWithContent $model
	): static|null {
		try {
			$props = static::load($name);
		} catch (Exception) {
			$props = $fallback !== null ? static::load($fallback) : null;
		}

		if ($props === null) {
			return null;
		}

		// inject the parent model
		$props['model'] = $model;

		return new static($props);
	}

	/**
	 * Returns a single field definition by name
	 */
	public function field(string $name): array|null
	{
		return $this->fields()[$name] ?? null;
	}

	/**
	 * Creates an error field with the given error message
	 */
	public static function fieldError(string $name, string $message): array
	{
		return [
			'label' => 'Error',
			'name'  => $name,
			'text'  => strip_tags($message),
			'theme' => 'negative',
			'type'  => 'info',
		];
	}

	/**
	 * Normalize field props for a single field
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the filed name is missing or the field type is invalid
	 */
	public static function fieldProps(array|string $props): array
	{
		$props = static::extend($props);

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
			$props['fields'] = static::fieldsProps($props['fields']);
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
	 * Returns all field definitions
	 */
	public function fields(): array
	{
		return $this->fields;
	}

	/**
	 * Normalizes all fields and adds automatic labels,
	 * types and widths.
	 */
	public static function fieldsProps($fields): array
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
				$fieldProps = static::fieldProps($fieldProps);
			} catch (Throwable $e) {
				$fieldProps = static::fieldError($fieldName, $e->getMessage());
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
	 * Find a blueprint by name
	 *
	 * @throws \Kirby\Exception\NotFoundException If the blueprint cannot be found
	 */
	public static function find(string $name): array
	{
		if (isset(static::$loaded[$name]) === true) {
			return static::$loaded[$name];
		}

		$kirby = App::instance();
		$root  = $kirby->root('blueprints');
		$file  = $root . '/' . $name . '.yml';

		// first try to find the blueprint in the `site/blueprints` root,
		// then check in the plugin extensions which includes some default
		// core blueprints (e.g. page, file, site and block defaults)
		// as well as blueprints provided by plugins
		if (F::exists($file, $root) !== true) {
			$file = $kirby->extension('blueprints', $name);
		}

		// callback option can be return array or blueprint file path
		if (is_callable($file) === true) {
			$file = $file($kirby);
		}

		// now ensure that we always return the data array
		if (is_string($file) === true && F::exists($file) === true) {
			return static::$loaded[$name] = Data::read($file);
		}

		if (is_array($file) === true) {
			return static::$loaded[$name] = $file;
		}

		// neither a valid file nor array data
		throw new NotFoundException(
			key: 'blueprint.notFound',
			data: ['name' => $name]
		);
	}

	public static function helpList(array $items): string
	{
		$md = [];

		foreach ($items as $item) {
			$md[] = '- *' . $item . '*';
		}

		return PHP_EOL . implode(PHP_EOL, $md);
	}

	/**
	 * Used to translate any label, heading, etc.
	 */
	protected function i18n(mixed $value, mixed $fallback = null): mixed
	{
		return I18n::translate($value, $fallback) ?? $value;
	}

	/**
	 * Checks if this is the default blueprint
	 */
	public function isDefault(): bool
	{
		return $this->name() === 'default';
	}

	/**
	 * Loads a blueprint from file or array
	 */
	public static function load(string $name): array
	{
		$props = static::find($name);

		// inject the filename as name if no name is set
		$props['name'] ??= $name;

		// normalize the title
		$title = $props['title'] ?? Str::label($props['name']);

		// translate the title
		$props['title'] = I18n::translate($title) ?? $title;

		return $props;
	}

	/**
	 * Returns the parent model
	 */
	public function model(): ModelWithContent
	{
		return $this->model;
	}

	/**
	 * Returns the blueprint name
	 */
	public function name(): string
	{
		return $this->props['name'];
	}

	/**
	 * Normalizes all required props in a column setup
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
	 * Normalizes blueprint options. This must be used in the
	 * constructor of an extended class, if you want to make use of it.
	 */
	protected function normalizeOptions(
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
			return array_map(fn () => false, $defaults);
		}

		// extend options if possible
		$options = static::extend($options);

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
			$sectionProps = static::extend($sectionProps);

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
					'text'  => 'The following section types are available: ' . static::helpList(array_keys(Section::$types))
				];
			} elseif (isset(Section::$types[$type]) === false) {
				$sections[$sectionName] = [
					'name'  => $sectionName,
					'label' => 'Invalid section type ("' . $type . '")',
					'type'  => 'info',
					'text'  => 'The following section types are available: ' . static::helpList(array_keys(Section::$types))
				];
			}

			if ($sectionProps['type'] === 'fields') {
				// Resolve field references before normalizing
				$sectionFields = $this->resolveFieldReferences($sectionProps['fields'] ?? []);
				$fields = Blueprint::fieldsProps($sectionFields);

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
	 */
	protected function normalizeTabs($tabs): array
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
			$tabProps = static::extend($tabProps);

			// inject a preset if available
			$tabProps = $this->preset($tabProps);

			$tabProps = $this->convertFieldsToSections($tabName, $tabProps);
			$tabProps = $this->convertSectionsToColumns($tabName, $tabProps);

			$tabs[$tabName] = [
				...$tabProps,
				'columns' => $this->normalizeColumns($tabName, $tabProps['columns'] ?? []),
				'icon'    => $tabProps['icon']  ?? null,
				'label'   => $this->i18n($tabProps['label'] ?? Str::label($tabName)),
				'link'    => $this->model->panel()->url(true) . '/?tab=' . $tabName,
				'name'    => $tabName,
			];
		}

		return $this->tabs = $tabs;
	}

	/**
	 * Injects a blueprint preset
	 */
	protected function preset(array $props): array
	{
		if (isset($props['preset']) === false) {
			return $props;
		}

		if (isset(static::$presets[$props['preset']]) === false) {
			return $props;
		}

		$preset = static::$presets[$props['preset']];

		if (is_string($preset) === true) {
			$preset = F::load($preset, allowOutput: false);
		}

		return $preset($props);
	}

	/**
	 * Resolves field references (numeric keys with string values)
	 * to full definitions from the global registry
	 *
	 * @since 6.0.0
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
					$resolved[$fieldName] = static::fieldError(
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
	 * Returns a single section by name
	 */
	public function section(string $name): Section|null
	{
		if (empty($this->sections[$name]) === true) {
			return $this->sectionFromField($name);
		}

		if ($this->sections[$name] instanceof Section) {
			return $this->sections[$name]; //@codeCoverageIgnore
		}

		// get all props
		$props = $this->sections[$name];

		// inject the blueprint model
		$props['model'] = $this->model();

		// create a new section object
		return $this->sections[$name] = new Section($props['type'], $props);
	}

	protected function sectionFromField(string $name): Section|null
	{
		if ($fieldProps = $this->field($name)) {
			if ($fieldProps['type'] !== 'section') {
				return null;
			}

			unset($fieldProps['type']);

			$field = new SectionField(...$fieldProps);
			$field->setModel($this->model());

			return $field->section();
		}

		return null;
	}

	/**
	 * Returns all sections
	 */
	public function sections(): array
	{
		return A::map(
			$this->sections,
			fn ($section) => match (true) {
				$section instanceof Section => $section,
				default                     => $this->section($section['name'])
			}
		);
	}

	/**
	 * Returns a single tab by name
	 */
	public function tab(string|null $name = null): array|null
	{
		if ($name === null) {
			return A::first($this->tabs);
		}

		return $this->tabs[$name] ?? null;
	}

	/**
	 * Returns all tabs
	 */
	public function tabs(): array
	{
		return array_values($this->tabs);
	}

	/**
	 * Returns the blueprint title
	 */
	public function title(): string
	{
		return $this->props['title'];
	}

	/**
	 * Converts the blueprint object to a plain array
	 */
	public function toArray(): array
	{
		return $this->props;
	}
}
