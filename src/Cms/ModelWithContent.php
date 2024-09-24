<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Content\Content;
use Kirby\Content\ContentStorageHandler;
use Kirby\Content\ContentTranslation;
use Kirby\Content\PlainTextContentStorageHandler;
use Kirby\Content\Version;
use Kirby\Content\VersionId;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Form;
use Kirby\Panel\Model;
use Kirby\Toolkit\Str;
use Kirby\Uuid\Identifiable;
use Kirby\Uuid\Uuid;
use Kirby\Uuid\Uuids;
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

	public Content|null $content;
	public static App $kirby;
	protected Site|null $site;
	protected ContentStorageHandler $storage;
	public Collection|null $translations = null;

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
	 * Creates a new instance with the same
	 * initial properties
	 *
	 * @todo eventually refactor without need of propertyData
	 */
	public function clone(array $props = []): static
	{
		return new static(array_replace_recursive($this->propertyData, $props));
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
		$language = Language::ensure($languageCode);

		// fetch a specific version in preview render mode
		// @todo this entire block can be radically simplified as soon
		// as the models use the versions exclusively.
		if (VersionId::$render ?? null) {
			$version = $this->version(VersionId::$render);

			if ($version->exists($language) === true) {
				return $version->content($language);
			}
		}

		// single language support
		if ($this->kirby()->multilang() === false) {
			return $this->content ??= $this->version()->content($language);
		}

		// only fetch from cache for the current language
		if ($languageCode === null && $this->content instanceof Content) {
			return $this->content;
		}

		// get the translation by code
		$translation = $this->translation($language->code());

		// don't normalize field keys (already handled by the `ContentTranslation` class)
		$content = new Content($translation->content(), $this, false);

		// only store the content for the current language
		if ($languageCode === null) {
			$this->content = $content;
		}

		return $content;
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
		// first close object with new blueprint as template
		$new = $this->clone(['template' => $blueprint]);

		// temporary compatibility change (TODO: also convert changes)
		$identifier = VersionId::published();

		// for multilang, we go through all translations and
		// covnert the content for each of them, remove the content file
		// to rewrite it with converted content afterwards
		if ($this->kirby()->multilang() === true) {
			$translations = [];

			foreach ($this->kirby()->languages()->codes() as $code) {
				if ($this->translation($code)?->exists() === true) {
					$content = $this->content($code)->convertTo($blueprint);

					// delete the old text file
					$this->version($identifier)->delete($code);

					// save to re-create the translation content file
					// with the converted/updated content
					$new->save($content, $code);
				}

				$translations[] = [
					'code'    => $code,
					'content' => $content ?? null
				];
			}

			// cloning the object with the new translations content ensures
			// that `propertyData` prop does not hold any old translations
			// content that could surface on subsequent cloning
			return $new->clone(['translations' => $translations]);
		}

		// for single language setups, we do the same,
		// just once for the main content
		$content = $this->content()->convertTo($blueprint);

		// delete the old text file
		$this->version($identifier)->delete('default');

		return $new->save($content);
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
	 */
	public function isLocked(): bool
	{
		$lock = $this->lock();
		return $lock && $lock->isLocked() === true;
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
	 * Returns the lock object for this model
	 *
	 * Only if a content directory exists,
	 * virtual pages will need to overwrite this method
	 */
	public function lock(): ContentLock|null
	{
		$dir = $this->root();

		if ($this::CLASS_ALIAS === 'file') {
			$dir = dirname($dir);
		}

		if (
			$this->kirby()->option('content.locking', true) &&
			is_string($dir) === true &&
			file_exists($dir) === true
		) {
			return new ContentLock($this);
		}

		return null;
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
		$this->blueprints   = null;
		$this->content      = null;
		$this->translations = null;

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
	 */
	public function readContent(string|null $languageCode = null): array
	{
		return $this->version()->read($languageCode ?? 'default') ?? [];
	}

	/**
	 * Returns the absolute path to the model
	 */
	abstract public function root(): string|null;

	/**
	 * Stores the content on disk
	 * @internal
	 */
	public function save(
		array|null $data = null,
		string|null $languageCode = null,
		bool $overwrite = false
	): static {
		if ($this->kirby()->multilang() === true) {
			return $this->saveTranslation($data, $languageCode, $overwrite);
		}

		return $this->saveContent($data, $overwrite);
	}

	/**
	 * Save the single language content
	 */
	protected function saveContent(
		array|null $data = null,
		bool $overwrite = false
	): static {
		// create a clone to avoid modifying the original
		$clone = $this->clone();

		// merge the new data with the existing content
		$clone->content()->update($data, $overwrite);

		// send the full content array to the writer
		$clone->writeContent($clone->content()->toArray());

		return $clone;
	}

	/**
	 * Save a translation
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the language for the given code does not exist
	 */
	protected function saveTranslation(
		array|null $data = null,
		string|null $languageCode = null,
		bool $overwrite = false
	): static {
		// create a clone to not touch the original
		$clone = $this->clone();

		// fetch the matching translation and update all the strings
		$translation = $clone->translation($languageCode);

		if ($translation === null) {
			throw new InvalidArgumentException(
				message: 'Invalid language: ' . $languageCode
			);
		}

		// get the content to store
		$content      = $translation->update($data, $overwrite)->content();
		$kirby        = $this->kirby();
		$languageCode = $kirby->languageCode($languageCode);

		// remove all untranslatable fields
		if ($languageCode !== $kirby->defaultLanguage()->code()) {
			foreach ($this->blueprint()->fields() as $field) {
				if (($field['translate'] ?? true) === false) {
					$content[strtolower($field['name'])] = null;
				}
			}

			// remove UUID for non-default languages
			if (Uuids::enabled() === true && isset($content['uuid']) === true) {
				$content['uuid'] = null;
			}

			// merge the translation with the new data
			$translation->update($content, true);
		}

		// send the full translation array to the writer
		$clone->writeContent($translation->content(), $languageCode);

		// reset the content object
		$clone->content = null;

		// return the updated model
		return $clone;
	}

	/**
	 * Sets the Content object
	 *
	 * @return $this
	 */
	protected function setContent(array|null $content = null): static
	{
		if ($content !== null) {
			$content = new Content($content, $this);
		}

		$this->content = $content;
		return $this;
	}

	/**
	 * Create the translations collection from an array
	 *
	 * @return $this
	 */
	protected function setTranslations(array|null $translations = null): static
	{
		if ($translations !== null) {
			$this->translations = new Collection();

			foreach ($translations as $props) {
				$props['parent'] = $this;
				$translation = new ContentTranslation($props);
				$this->translations->data[$translation->code()] = $translation;
			}
		} else {
			$this->translations = null;
		}

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
	public function storage(): ContentStorageHandler
	{
		return $this->storage ??= new PlainTextContentStorageHandler(
			model: $this,
		);
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
	): ContentTranslation|null {
		$language = Language::ensure($languageCode ?? 'current');
		return $this->translations()->find($language->code());
	}

	/**
	 * Returns the translations collection
	 */
	public function translations(): Collection
	{
		if ($this->translations !== null) {
			return $this->translations;
		}

		$this->translations = new Collection();

		foreach ($this->kirby()->languages() as $language) {
			$translation = new ContentTranslation([
				'parent' => $this,
				'code'   => $language->code(),
			]);

			$this->translations->data[$translation->code()] = $translation;
		}

		return $this->translations;
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
			id: VersionId::from($versionId ?? VersionId::published())
		);
	}

	/**
	 * Low level data writer method
	 * to store the given data on disk or anywhere else
	 * @internal
	 */
	public function writeContent(array $data, string|null $languageCode = null): bool
	{
		$this->version()->save($data, $languageCode ?? 'default', true);
		return true;
	}
}
