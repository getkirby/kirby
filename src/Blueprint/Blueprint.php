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
use Kirby\Toolkit\A;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

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

	protected array $fields = [];
	protected ModelWithContent $model;
	protected array $props;
	protected array $sections = [];
	protected array $tabs = [];

	protected array|null $fileTemplates = null;

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

		// convert all shortcuts
		$props = $this->convertFieldsToSections('main', $props);
		$props = $this->convertSectionsToColumns('main', $props);
		$props = $this->convertColumnsToTabs('main', $props);

		// normalize all tabs
		$props['tabs'] = $this->normalizeTabs($props['tabs'] ?? []);

		$this->props = $props;
	}

	/**
	 * Magic getter/caller for any blueprint prop
	 */
	public function __call(string $key, array|null $arguments = null): mixed
	{
		return $this->props[$key] ?? null;
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
	 * this model based on the blueprint
	 */
	public function acceptedFileTemplates(string|null $inSection = null): array
	{
		// get cached results for the current file model
		// (except when collecting for a specific section)
		if ($inSection === null && $this->fileTemplates !== null) {
			return $this->fileTemplates; // @codeCoverageIgnore
		}

		$templates = [];

		// collect all allowed file templates from blueprint…
		foreach ($this->sections() as $section) {
			// if collecting for a specific section, skip all others
			if ($inSection !== null && $section->name() !== $inSection) {
				continue;
			}

			$templates = match ($section->type()) {
				'files'  => [...$templates, $section->template() ?? 'default'],
				'fields' => [
					...$templates,
					...$this->acceptedFileTemplatesFromFields($section->fields())
				],
				default  => $templates
			};
		}

		// no caching for when collecting for specific section
		if ($inSection !== null) {
			return $templates; // @codeCoverageIgnore
		}

		return $this->fileTemplates = $templates;
	}

	/**
	 * Gathers the allowed file templates from model's fields
	 */
	protected function acceptedFileTemplatesFromFields(array $fields): array
	{
		$templates = [];

		foreach ($fields as $field) {
			// fields with uploads settings
			if (isset($field['uploads']) === true && is_array($field['uploads']) === true) {
				$templates = [
					...$templates,
					...$this->acceptedFileTemplatesFromFieldUploads($field['uploads'])
				];
				continue;
			}

			// structure and object fields
			if (isset($field['fields']) === true && is_array($field['fields']) === true) {
				$templates = [
					...$templates,
					...$this->acceptedFileTemplatesFromFields($field['fields']),
				];
				continue;
			}

			// layout and blocks fields
			if (isset($field['fieldsets']) === true && is_array($field['fieldsets']) === true) {
				$templates = [
					...$templates,
					...$this->acceptedFileTemplatesFromFieldsets($field['fieldsets'])
				];
				continue;
			}
		}

		return $templates;
	}

	/**
	 * Gathers the allowed file templates from fieldsets
	 */
	protected function acceptedFileTemplatesFromFieldsets(array $fieldsets): array
	{
		$templates = [];

		foreach ($fieldsets as $fieldset) {
			foreach (($fieldset['tabs'] ?? []) as $tab) {
				$templates = [
					...$templates,
					...$this->acceptedFileTemplatesFromFields($tab['fields'] ?? [])
				];
			}
		}

		return $templates;
	}

	/**
	 * Extracts templates from field uploads settings
	 */
	protected function acceptedFileTemplatesFromFieldUploads(array $uploads): array
	{
		// only if the `uploads` parent is this model
		if ($target = $uploads['parent'] ?? null) {
			if ($this->model->id() !== $target) {
				return [];
			}
		}

		return [($uploads['template'] ?? 'default')];
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

		// wrap all fields in a section
		$props['sections'] = [
			$tabName . '-fields' => [
				'type'   => 'fields',
				'fields' => $props['fields']
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
		return $this->fields[$name] ?? null;
	}

	/**
	 * @deprecated 6.0.0 Use \Kirby\Blueprint\Fields::fieldError() instead
	 */
	public static function fieldError(string $name, string $message): array
	{
		return Fields::fieldError($name, $message);
	}

	/**
	 * @deprecated 6.0.0 Use \Kirby\Blueprint\Fields::normalizeFieldProps() instead
	 */
	public static function fieldProps(array|string $props): array
	{
		return Fields::normalizeFieldProps($props);
	}

	/**
	 * @deprecated 6.0.0 Use \Kirby\Blueprint\Fields::normalizeFieldsProps() instead
	 */
	public static function fieldsProps($fields): array
	{
		return Fields::normalizeFieldsProps($fields);
	}

	/**
	 * Returns all field definitions
	 */
	public function fields(): array
	{
		return $this->fields;
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

	public static function helpList(array $items): string
	{
		$md = [];

		foreach ($items as $item) {
			$md[] = '- *' . $item . '*';
		}

		return PHP_EOL . implode(PHP_EOL, $md);
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
		$sections = Sections::normalizeSectionsProps($sections);

		foreach ($sections as $sectionName => $sectionProps) {

			if ($sectionProps['type'] === 'fields') {
				$fields = Fields::normalizeFieldsProps($sectionProps['fields'] ?? []);

				// inject guide fields guide
				if ($fields === []) {
					$fields = [
						$tabName . '-info' => Fields::missingFieldsError($tabName . '-info')
					];
				} else {
					foreach ($fields as $fieldName => $fieldProps) {
						if (isset($this->fields[$fieldName]) === true) {
							$this->fields[$fieldName] = $fields[$fieldName] = Fields::existingFieldError($fieldName, $fieldProps['label'] ?? null);
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
	 * Returns a single section by name
	 */
	public function section(string $name): Section|null
	{
		if (empty($this->sections[$name]) === true) {
			return null;
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
