<?php

namespace Kirby\Blueprint;

use Exception;
use Kirby\Cms\App;
use Kirby\Cms\ModelWithContent;
use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\F;
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

	protected AcceptRules $acceptRules;
	protected array $fields = [];
	protected ModelWithContent $model;
	protected array $props;
	protected array $sections = [];
	protected array $tabs = [];

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
		$props = static::preset($props);

		// normalize the name
		$props['name'] ??= 'default';

		// normalize and translate the title
		$props['title'] ??= Str::label($props['name']);
		$props['title']   = $this->i18n($props['title']);

		// convert all shortcuts
		$props = FieldsProps::convertToSections('main-fields', $props);
		$props = SectionsProps::convertToColumns($props);
		$props = ColumnsProps::convertToTabs('main', $props);

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
		return $this->acceptRules()->acceptedFileTemplates($inSection);
	}

	/**
	 * Gathers custom config for Panel view buttons
	 */
	public function buttons(): array|false|null
	{
		return $this->props['buttons'] ?? null;
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
	 * @deprecated 6.0.0 Use `\Kirby\Blueprint\FieldProps::forFieldError()` instead
	 */
	public static function fieldError(string $name, string $message): array
	{
		return FieldProps::forFieldError($name, $message);
	}

	/**
	 * @deprecated 6.0.0 Use `\Kirby\Blueprint\FieldProps::normalize()` instead
	 */
	public static function fieldProps(array|string $props): array
	{
		return FieldProps::normalize($props);
	}

	/**
	 * @deprecated 6.0.0 Use `\Kirby\Blueprint\FieldsProps::normalize()` instead
	 */
	public static function fieldsProps($fields): array
	{
		return FieldsProps::normalize($fields);
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
		$columns = ColumnsProps::normalize($tabName, $columns);

		foreach ($columns as $columnKey => $columnProps) {

			$columnProps['sections'] = $this->normalizeSections(
				$tabName,
				$columnProps['sections'] ?? []
			);

			$columns[$columnKey] = $columnProps;
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
	 * @deprecated 6.0.0 Use `\Kirby\Blueprint\OptionsProps::normalize()` instead
	 */
	protected function normalizeOptions(
		array|string|bool|null $options,
		array $defaults,
		array $aliases = []
	): array {
		return OptionsProps::normalize($options, $defaults, $aliases);
	}

	/**
	 * Normalizes all required keys in sections
	 */
	protected function normalizeSections(
		string $tabName,
		array $sections
	): array {
		$sections = SectionsProps::normalize($sections);

		foreach ($sections as $sectionName => $sectionProps) {
			if ($sectionProps['type'] === 'fields') {
				$fields = $sectionProps['fields'];

				foreach ($fields as $fieldName => $fieldProps) {
					if (isset($this->fields[$fieldName]) === true) {
						$this->fields[$fieldName] = $fields[$fieldName] = FieldProps::forExistingFieldError($fieldName, $fieldProps['label'] ?? null);
					} else {
						$this->fields[$fieldName] = $fieldProps;
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
		$tabs = TabsProps::normalize($tabs);

		foreach ($tabs as $tabName => $tabProps) {
			$tabs[$tabName] = [
				...$tabProps,
				'columns' => $this->normalizeColumns($tabName, $tabProps['columns']),
				'label'   => $this->i18n($tabProps['label']),
				'link'    => $this->model->panel()->url(true) . '/?tab=' . $tabName,
			];
		}

		return $this->tabs = $tabs;
	}

	/**
	 * Injects a blueprint preset
	 */
	public static function preset(array $props): array
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
