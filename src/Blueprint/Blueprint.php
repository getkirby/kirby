<?php

namespace Kirby\Blueprint;

use Exception;
use Kirby\Cms\App;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\User;
use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\F;
use Kirby\Toolkit\A;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

/**
 * The Blueprint class gives access to all settings
 * of a blueprint file. The raw props are converted
 * into a proper tab layout by the `Normalizer`.
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Blueprint
{
	public static array $loaded = [];

	protected AcceptRules|null $acceptRules = null;

	protected array $fields = [];
	protected array|null $fieldsLower = null;
	protected ModelWithContent $model;
	protected array $props;
	protected Tabs|null $tabs = null;

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
	 * @throws InvalidArgumentException If the blueprint model is missing
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

		// convert all shortcuts and normalize the props
		$normalizer = new Normalizer(
			props: $props
		);

		$this->fields = $normalizer->fields();
		$this->props  = $normalizer->props();
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
	public function acceptedFileTemplates(string|null $inField = null): array
	{
		return $this->acceptRules()->fileTemplates($inField);
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
		if (isset($this->fields[$name]) === true) {
			return $this->fields[$name];
		}

		// field objects use normalized lowercase keys
		$this->fieldsLower ??= array_change_key_case($this->fields);
		return $this->fieldsLower[Str::lower($name)] ?? null;
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
	 * Normalize field props for a single field.
	 * Facade for `Normalizer::normalizeFieldProps()`
	 *
	 * @throws InvalidArgumentException If the filed name is missing or the field type is invalid
	 */
	public static function fieldProps(array|string $props): array
	{
		return Normalizer::normalizeFieldProps($props);
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
	 * Facade for `Normalizer::normalizeFieldsProps()`
	 *
	 * @return array<string, array>
	 */
	public static function fieldsProps(mixed $fields): array
	{
		return Normalizer::normalizeFieldsProps($fields);
	}

	/**
	 * Find a blueprint by name
	 *
	 * @throws NotFoundException If the blueprint cannot be found
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
	 * Normalizes blueprint options. This must be used in the
	 * constructor of an extended class, if you want to make use
	 * of it. Facade for `Normalizer::normalizeOptions()`
	 */
	protected function normalizeOptions(
		array|string|bool|null $options,
		array $defaults,
		array $aliases = []
	): array {
		return Normalizer::normalizeOptions(
			options: $options,
			defaults: $defaults,
			aliases: $aliases
		);
	}

	/**
	 * Return the option settings depending on the user role
	 */
	public function optionForUser(User $user, string $action): bool|null
	{
		$rules = $this->options()[$action] ?? null;

		if ($rules === true || $rules === false) {
			return $rules;
		}

		// only associative arrays hold role-based rules.
		// Lists are used for other option settings,
		// e.g. `changeTemplate` with a list of template names
		if (
			is_array($rules) === true &&
			A::isAssociative($rules) === true
		) {
			$roleId = $user->role()->id();

			if (isset($rules[$roleId]) === true) {
				return $rules[$roleId];
			}

			if (isset($rules['*']) === true) {
				return $rules['*'];
			}
		}

		return null;
	}

	/**
	 * Returns a single tab by name or the
	 * first tab if no name is given
	 */
	public function tab(string|null $name = null): Tab|null
	{
		$tabs = $this->tabs();

		if ($name === null) {
			return $tabs->first();
		}

		return $tabs->get($name);
	}

	/**
	 * Creates and caches the collection of all tabs
	 * from the normalized tab props
	 */
	public function tabs(): Tabs
	{
		return $this->tabs ??= new Tabs(
			tabs: $this->props['tabs'] ?? [],
			model: $this->model
		);
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
		return [
			...$this->props,
			'tabs' => $this->tabs()->toArray()
		];
	}
}
