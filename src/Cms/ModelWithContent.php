<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Content\Content;
use Kirby\Content\ImmutableMemoryStorage;
use Kirby\Content\Lock;
use Kirby\Content\MemoryStorage;
use Kirby\Content\Storage;
use Kirby\Content\Translation;
use Kirby\Content\Translations;
use Kirby\Content\Version;
use Kirby\Content\VersionId;
use Kirby\Content\Versions;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Form;
use Kirby\Panel\Model;
use Kirby\Toolkit\Str;
use Kirby\Uuid\Identifiable;
use Kirby\Uuid\Uuid;
use Stringable;
use Throwable;

/**
 * ModelWithContent
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
abstract class ModelWithContent implements Identifiable, Stringable
{
	/**
	 * Each model must define a CLASS_ALIAS
	 * which will be used in template queries.
	 * The CLASS_ALIAS is a short human-readable
	 * version of the class name, i.e. page.
	 */
	public const CLASS_ALIAS = null;

	/**
	 * Cached array of valid blueprints
	 * that could be used for the model
	 */
	public array|null $blueprints = null;

	public static App $kirby;
	protected Site|null $site;
	protected Storage $storage;

	/**
	 * Store values used to initilaize object
	 */
	protected array $propertyData = [];

	public function __construct(array $props = [])
	{
		$this->site = $props['site'] ?? null;

		$this->setContent($props['content'] ?? null);
		$this->setTranslations($props['translations'] ?? null);

		$this->propertyData = $props;
	}

	/**
	 * Returns the blueprint of the model
	 */
	abstract public function blueprint(): Blueprint;

	/**
	 * Returns an array with all blueprints that are available
	 */
	public function blueprints(string|null $inSection = null): array
	{
		// helper function
		$toBlueprints = static function (array $sections): array {
			$blueprints = [];

			foreach ($sections as $section) {
				if ($section === null) {
					continue;
				}

				foreach ((array)$section->blueprints() as $blueprint) {
					$blueprints[$blueprint['name']] = $blueprint;
				}
			}

			return array_values($blueprints);
		};

		$blueprint = $this->blueprint();

		// no caching for when collecting for specific section
		if ($inSection !== null) {
			return $toBlueprints([$blueprint->section($inSection)]);
		}

		return $this->blueprints ??= $toBlueprints($blueprint->sections());
	}

	/**
	 * Moves or copies the model to a new storage instance/type
	 * @since 5.0.0
	 * @internal
	 */
	public function changeStorage(Storage|string $toStorage, bool $copy = false): static
	{
		if (is_string($toStorage) === true) {
			if (is_subclass_of($toStorage, Storage::class) === false) {
				throw new InvalidArgumentException('Invalid storage class');
			}

			$toStorage = new $toStorage($this);
		}

		$method = $copy ? 'copyAll' : 'moveAll';

		$this->storage()->$method(to: $toStorage);
		$this->storage = $toStorage;
		return $this;
	}

	/**
	 * Creates a new instance with the same
	 * initial properties
	 *
	 * @todo eventually refactor without need of propertyData
	 */
	public function clone(array $props = []): static
	{
		$props = array_replace_recursive($this->propertyData, $props);
		$clone = new static($props);

		// Move the clone to a new instance of the same storage class
		// The storage classes might need to rely on the model instance
		// and thus we need to make sure that the cloned object is
		// passed on to the new storage instance
		$storage = match (true) {
			isset($props['content']),
			isset($props['translations']) => $clone->storage()::class,
			default                       => $this->storage()::class
		};

		$clone->changeStorage($storage);

		return $clone;
	}

	/**
	 * Executes any given model action
	 */
	abstract protected function commit(
		string $action,
		array $arguments,
		Closure $callback
	): mixed;

	/**
	 * Returns the content
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the language for the given code does not exist
	 */
	public function content(string|null $languageCode = null): Content
	{
		// get the targeted language
		$language  = Language::ensure($languageCode ?? 'current');
		$versionId = VersionId::$render ?? VersionId::latest();
		$version   = $this->version($versionId);

		if ($version->exists($language) === true) {
			return $version->content($language);
		}

		return $this->version()->content($language);
	}

	/**
	 * Prepares the content that should be written
	 * to the text file
	 * @internal
	 */
	public function contentFileData(
		array $data,
		string|null $languageCode = null
	): array {
		return $data;
	}

	/**
	 * Converts model to new blueprint
	 * incl. its content for all translations
	 */
	protected function convertTo(string $blueprint): static
	{
		// Keep a copy of the old model with the original storage handler.
		// This will be used to delete the old versions.
		$old = $this->clone();

		// Clone the object with the new blueprint as template
		$new = $this->clone(['template' => $blueprint]);

		// Make sure to use the same storage class as the original model.
		// This is needed if the model has been constructed with `content` or
		// `translations` props. In this case, the storage would be set to
		// `MemoryStorage` in the clone method again, even if it might have been
		// changed before.
		$new->changeStorage($this->storage()::class);

		// Copy this instance into immutable storage.
		// Moving the content would prematurely delete the old content storage entries.
		// But we need to keep them until the new content is written.
		$this->changeStorage(
			toStorage: new ImmutableMemoryStorage(
				model: $this,
				nextModel: $new
			),
			copy: true
		);

		// Get all languages to loop through
		$languages = Languages::ensure();

		// Loop through all versions
		foreach ($old->versions() as $oldVersion) {
			// Loop through all languages
			foreach ($languages as $language) {
				// Skip non-existing versions
				if ($oldVersion->exists($language) === false) {
					continue;
				}

				// Convert the content to the new blueprint
				$content = $oldVersion->content($language)->convertTo($blueprint);

				// Save to re-create the new version
				// with the converted/updated content
				$new->version($oldVersion->id())->save($content, $language);

				// Delete the old versions. This will also remove the
				// content files from the storage if this is a plain text
				// storage instance.
				$oldVersion->delete($language);
			}
		}

		return $new;
	}

	/**
	 * Creates default content for the model, by using our
	 * Form class to generate the defaults, based on the
	 * model's blueprint setup.
	 *
	 * @since 5.0.0
	 */
	public function createDefaultContent(): array
	{
		// create the form to get the generate the defaults
		$form = Form::for($this, [
			'language' => Language::ensure('default')->code(),
		]);

		return $form->strings(true);
	}

	/**
	 * Decrement a given field value
	 */
	public function decrement(
		string $field,
		int $by = 1,
		int $min = 0
	): static {
		$value = (int)$this->content()->get($field)->value() - $by;

		if ($value < $min) {
			$value = $min;
		}

		return $this->update([$field => $value]);
	}

	/**
	 * Returns all content validation errors
	 */
	public function errors(): array
	{
		$errors = [];

		foreach ($this->blueprint()->sections() as $section) {
			$errors = [...$errors, ...$section->errors()];
		}

		return $errors;
	}

	/**
	 * Creates a clone and fetches all
	 * lazy-loaded getters to get a full copy
	 */
	public function hardcopy(): static
	{
		$clone = $this->clone();

		foreach (get_object_vars($clone) as $name => $default) {
			if (method_exists($clone, $name) === true) {
				$clone->$name();
			}
		}

		return $clone;
	}

	/**
	 * Each model must return a unique id
	 */
	public function id(): string|null
	{
		return null;
	}

	/**
	 * Increment a given field value
	 */
	public function increment(
		string $field,
		int $by = 1,
		int|null $max = null
	): static {
		$value = (int)$this->content()->get($field)->value() + $by;

		if ($max && $value > $max) {
			$value = $max;
		}

		return $this->update([$field => $value]);
	}

	/**
	 * Checks if the model is locked for the current user
	 * @deprecated 5.0.0 Use `->lock()->isLocked()` instead
	 */
	public function isLocked(): bool
	{
		return $this->lock()->isLocked() === true;
	}

	/**
	 * Checks if the data has any errors
	 */
	public function isValid(): bool
	{
		return Form::for($this)->isValid() === true;
	}

	/**
	 * Returns the parent Kirby instance
	 */
	public function kirby(): App
	{
		return static::$kirby ??= App::instance();
	}

	/**
	 * Returns lock for the model
	 */
	public function lock(): Lock
	{
		return $this->version(VersionId::changes())->lock('*');
	}

	/**
	 * Returns the panel info of the model
	 * @since 3.6.0
	 */
	abstract public function panel(): Model;

	/**
	 * Must return the permissions object for the model
	 */
	abstract public function permissions(): ModelPermissions;

	/**
	 * Clean internal caches
	 *
	 * @return $this
	 */
	public function purge(): static
	{
		$this->blueprints = null;
		return $this;
	}

	/**
	 * Creates a string query, starting from the model
	 * @internal
	 */
	public function query(
		string|null $query = null,
		string|null $expect = null
	): mixed {
		if ($query === null) {
			return null;
		}

		try {
			$result = Str::query($query, [
				'kirby'             => $this->kirby(),
				'site'              => $this instanceof Site ? $this : $this->site(),
				'model'             => $this,
				static::CLASS_ALIAS => $this
			]);
		} catch (Throwable) {
			return null;
		}

		if ($expect !== null && $result instanceof $expect === false) {
			return null;
		}

		return $result;
	}

	/**
	 * Read the content from the content file
	 * @internal
	 * @deprecated 5.0.0 Use `->version()->read()` instead
	 */
	public function readContent(string|null $languageCode = null): array
	{
		Helpers::deprecated('$model->readContent() is deprecated. Use $model->version()->read() instead.'); // @codeCoverageIgnore
		return $this->version()->read($languageCode ?? 'default') ?? [];
	}

	/**
	 * Returns the absolute path to the model
	 */
	abstract public function root(): string|null;

	/**
	 * @internal
	 */
	public function save(
		array|null $data = null,
		string|null $languageCode = null,
		bool $overwrite = false
	): static {
		// create a clone to avoid modifying the original
		$clone = $this->clone();

		// move the old model into memory
		$this->changeStorage(
			toStorage: new ImmutableMemoryStorage(
				model: $this,
				nextModel: $clone
			),
			copy: true
		);

		// update the clone
		$clone->version()->save(
			$data ?? [],
			$languageCode ?? 'default',
			$overwrite
		);

		ModelState::update(
			method: 'set',
			current: $this,
			next: $clone
		);

		return $clone;
	}

	/**
	 * @deprecated 5.0.0 Use $model->save() instead
	 */
	protected function saveContent(
		array|null $data = null,
		bool $overwrite = false
	): static {
		Helpers::deprecated('$model->saveContent() is deprecated. Use $model->save() instead.');
		return $this->save($data, 'default', $overwrite);
	}

	/**
	 * @deprecated 5.0.0 Use $model->save() instead
	 */
	protected function saveTranslation(
		array|null $data = null,
		string|null $languageCode = null,
		bool $overwrite = false
	): static {
		Helpers::deprecated('$model->saveTranslation() is deprecated. Use $model->save() instead.');
		return $this->save($data, $languageCode ?? 'default', $overwrite);
	}

	/**
	 * Sets the Content object
	 *
	 * @return $this
	 */
	protected function setContent(array|null $content = null): static
	{
		if ($content === null) {
			return $this;
		}

		$this->changeStorage(MemoryStorage::class, copy: true);
		$this->version()->save($content, 'default');

		return $this;
	}

	/**
	 * Create the translations collection from an array
	 *
	 * @return $this
	 */
	protected function setTranslations(array|null $translations = null): static
	{
		if ($translations === null) {
			return $this;
		}

		$this->changeStorage(MemoryStorage::class, copy: true);

		Translations::create(
			model: $this,
			version: $this->version(),
			translations: $translations
		);

		return $this;
	}

	/**
	 * Returns the parent Site instance
	 */
	public function site(): Site
	{
		return $this->site ??= $this->kirby()->site();
	}

	/**
	 * Returns the content storage handler
	 * @internal
	 */
	public function storage(): Storage
	{
		return $this->storage ??= $this->kirby()->storage($this);
	}

	/**
	 * Convert the model to a simple array
	 */
	public function toArray(): array
	{
		return [
			'content'      => $this->content()->toArray(),
			'translations' => $this->translations()->toArray()
		];
	}

	/**
	 * String template builder with automatic HTML escaping
	 * @since 3.6.0
	 *
	 * @param string|null $template Template string or `null` to use the model ID
	 * @param string|null $fallback Fallback for tokens in the template that cannot be replaced
	 *                              (`null` to keep the original token)
	 */
	public function toSafeString(
		string|null $template = null,
		array $data = [],
		string|null $fallback = ''
	): string {
		return $this->toString($template, $data, $fallback, 'safeTemplate');
	}

	/**
	 * String template builder
	 *
	 * @param string|null $template Template string or `null` to use the model ID
	 * @param string|null $fallback Fallback for tokens in the template that cannot be replaced
	 *                              (`null` to keep the original token)
	 * @param string $handler For internal use
	 */
	public function toString(
		string|null $template = null,
		array $data = [],
		string|null $fallback = '',
		string $handler = 'template'
	): string {
		if ($template === null) {
			return $this->id() ?? '';
		}

		if ($handler !== 'template' && $handler !== 'safeTemplate') {
			throw new InvalidArgumentException(message: 'Invalid toString handler'); // @codeCoverageIgnore
		}

		$result = Str::$handler($template, array_replace([
			'kirby'             => $this->kirby(),
			'site'              => $this instanceof Site ? $this : $this->site(),
			'model'             => $this,
			static::CLASS_ALIAS => $this,
		], $data), ['fallback' => $fallback]);

		return $result;
	}

	/**
	 * Makes it possible to convert the entire model
	 * to a string. Mostly useful for debugging
	 */
	public function __toString(): string
	{
		return (string)$this->id();
	}

	/**
	 * Returns a single translation by language code
	 * If no code is specified the current translation is returned
	 *
	 * @throws \Kirby\Exception\NotFoundException If the language does not exist
	 */
	public function translation(
		string|null $languageCode = null
	): Translation {
		$language = Language::ensure($languageCode ?? 'current');

		return new Translation(
			model: $this,
			version: $this->version(),
			language: $language
		);
	}

	/**
	 * Returns the translations collection
	 */
	public function translations(): Translations
	{
		return Translations::load(
			model: $this,
			version: $this->version()
		);
	}

	/**
	 * Updates the model data
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the input array contains invalid values
	 */
	public function update(
		array|null $input = null,
		string|null $languageCode = null,
		bool $validate = false
	): static {
		$form = Form::for($this, [
			'ignoreDisabled' => $validate === false,
			'input'          => $input,
			'language'       => $languageCode,
		]);

		// validate the input
		if ($validate === true && $form->isInvalid() === true) {
			throw new InvalidArgumentException(
				fallback: 'Invalid form with errors',
				details: $form->errors()
			);
		}

		return $this->commit(
			'update',
			[
				static::CLASS_ALIAS => $this,
				'values'            => $form->data(),
				'strings'           => $form->strings(),
				'languageCode'      => $languageCode
			],
			fn ($model, $values, $strings, $languageCode) =>
				$model->save($strings, $languageCode, true)
		);
	}

	/**
	 * Returns the model's UUID
	 * @since 3.8.0
	 */
	public function uuid(): Uuid|null
	{
		return Uuid::for($this);
	}

	/**
	 * Returns a content version instance
	 * @since 5.0.0
	 */
	public function version(VersionId|string|null $versionId = null): Version
	{
		return new Version(
			model: $this,
			id: VersionId::from($versionId ?? VersionId::latest())
		);
	}

	/**
	 * Returns a versions collection
	 * @since 5.0.0
	 */
	public function versions(): Versions
	{
		return Versions::load($this);
	}

	/**
	 * Low level data writer method
	 * to store the given data on disk or anywhere else
	 * @internal
	 * @deprecated 5.0.0 Use `->version()->save()` instead
	 */
	public function writeContent(array $data, string|null $languageCode = null): bool
	{
		Helpers::deprecated('$model->writeContent() is deprecated. Use $model->version()->save() instead.'); // @codeCoverageIgnore
		$this->version()->save($data, $languageCode ?? 'default', true);
		return true;
	}
}
