<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Form;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;
use Kirby\Uuid\Identifiable;
use Kirby\Uuid\Uuid;
use Kirby\Uuid\Uuids;
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
abstract class ModelWithContent extends Model implements Identifiable
{
	/**
	 * The content
	 *
	 * @var \Kirby\Cms\Content
	 */
	public $content;

	public Collection|null $languages = null;
	/**
	 * @depcrecated 4.0.0 Use `$languages` instead
	 * @todo content.translations.deprecated
	 */
	public $translations;

	/**
	 * Returns the blueprint of the model
	 *
	 * @return \Kirby\Cms\Blueprint
	 */
	abstract public function blueprint();

	/**
	 * Returns an array with all blueprints that are available
	 */
	public function blueprints(string $inSection = null): array
	{
		$blueprints = [];
		$blueprint  = $this->blueprint();
		$sections   = $inSection !== null ? [$blueprint->section($inSection)] : $blueprint->sections();

		foreach ($sections as $section) {
			if ($section === null) {
				continue;
			}

			foreach ((array)$section->blueprints() as $blueprint) {
				$blueprints[$blueprint['name']] = $blueprint;
			}
		}

		return array_values($blueprints);
	}

	/**
	 * Executes any given model action
	 */
	abstract protected function commit(
		string $action,
		array $arguments,
		Closure $callback
	);

	/**
	 * Returns the content
	 *
	 * @return \Kirby\Cms\Content
	 * @throws \Kirby\Exception\InvalidArgumentException If the language for the given code does not exist
	 */
	public function content(string $languageCode = null)
	{
		// single language support
		if ($this->kirby()->multilang() === false) {
			// don't normalize field keys (already handled by the `Data` class)
			return $this->content ??= new Content($this->readContent(), $this, false);
		}

		// get the targeted language
		$language = $this->kirby()->language($languageCode);

		// stop if the language does not exist
		if ($language === null) {
			throw new InvalidArgumentException('Invalid language: ' . $languageCode);
		}

		// only fetch from cache for the current language
		if ($languageCode === null && $this->content instanceof Content) {
			return $this->content;
		}

		// get the language by code
		$language = $this->contentLanguage($language->code());

		// don't normalize field keys
		// (already handled by the `ContentLanguage` class)
		$content = new Content($language->content(), $this, false);

		// only store the content for the current language
		if ($languageCode === null) {
			$this->content = $content;
		}

		return $content;
	}

	/**
	 * Returns the absolute path to the content file
	 *
	 * @internal
	 * @throws \Kirby\Exception\InvalidArgumentException If the language for the given code does not exist
	 */
	public function contentFile(
		string $languageCode = null,
		bool $force = false
	): string {
		$extension = $this->contentFileExtension();
		$directory = $this->contentFileDirectory();
		$filename  = $this->contentFileName();

		// overwrite the language code
		if ($force === true) {
			if (empty($languageCode) === false) {
				return $directory . '/' . $filename . '.' . $languageCode . '.' . $extension;
			}

			return $directory . '/' . $filename . '.' . $extension;
		}

		// add and validate the language code in multi language mode
		if ($this->kirby()->multilang() === true) {
			if ($language = $this->kirby()->languageCode($languageCode)) {
				return $directory . '/' . $filename . '.' . $language . '.' . $extension;
			}

			throw new InvalidArgumentException('Invalid language: ' . $languageCode);
		}

		return $directory . '/' . $filename . '.' . $extension;
	}

	/**
	 * Returns an array with all content files
	 */
	public function contentFiles(): array
	{
		if ($this->kirby()->multilang() === true) {
			return A::map(
				$this->kirby()->languages()->codes(),
				fn ($code) => $this->contentFile($code)
			);
		}

		return [
			$this->contentFile()
		];
	}

	/**
	 * Prepares the content that should be written
	 * to the text file
	 *
	 * @internal
	 */
	public function contentFileData(
		array $data,
		string $languageCode = null
	): array {
		return $data;
	}

	/**
	 * Returns the absolute path to the
	 * folder in which the content file is
	 * located
	 *
	 * @internal
	 */
	public function contentFileDirectory(): string|null
	{
		return $this->root();
	}

	/**
	 * Returns the extension of the content file
	 *
	 * @internal
	 */
	public function contentFileExtension(): string
	{
		return $this->kirby()->contentExtension();
	}

	/**
	 * Needs to be declared by the final model
	 *
	 * @internal
	 */
	abstract public function contentFileName(): string;

	/**
	 * Returns a single content language by  code
	 * If no code is specified the current language is returned
	 */
	public function contentLanguage(
		string $languageCode = null
	): ContentLanguage|null {
		if ($language = $this->kirby()->language($languageCode)) {
			return $this->contentLanguages()->find($language->code());
		}

		return null;
	}

	/**
	 * Returns the content languages collection
	 */
	public function contentLanguages(): Collection
	{
		if ($this->languages !== null) {
			return $this->languages;
		}

		$this->languages = new Collection();

		foreach ($this->kirby()->languages() as $language) {
			$this->languages->{$language->code()} = new ContentLanguage(
				parent: $this,
				code:   $language->code()
			);
		}

		return $this->languages;
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
			$errors = array_merge($errors, $section->errors());
		}

		return $errors;
	}

	/**
	 * Increment a given field value
	 */
	public function increment(
		string $field,
		int $by = 1,
		int $max = null
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
		return $this->lock()?->isLocked() === true;
	}

	/**
	 * Checks if the data has any errors
	 */
	public function isValid(): bool
	{
		return Form::for($this)->hasErrors() === false;
	}

	/**
	 * Returns the lock object for this model
	 *
	 * Only if a content directory exists,
	 * virtual pages will need to overwrite this method
	 *
	 * @return \Kirby\Cms\ContentLock|null
	 */
	public function lock()
	{
		$dir = $this->contentFileDirectory();

		if (
			$this->kirby()->option('content.locking', true) &&
			is_string($dir) === true &&
			file_exists($dir) === true
		) {
			return new ContentLock($this);
		}
	}

	/**
	 * Returns the panel info of the model
	 * @since 3.6.0
	 *
	 * @return \Kirby\Panel\Model
	 */
	abstract public function panel();

	/**
	 * Must return the permissions object for the model
	 *
	 * @return \Kirby\Cms\ModelPermissions
	 */
	abstract public function permissions();

	/**
	 * Creates a string query, starting from the model
	 *
	 * @internal
	 */
	public function query(string $query = null, string $expect = null)
	{
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
	 *
	 * @internal
	 */
	public function readContent(string $languageCode = null): array
	{
		$file = $this->contentFile($languageCode);

		// only if the content file really does not exist, it's ok
		// to return empty content. Otherwise this could lead to
		// content loss in case of file reading issues
		if (file_exists($file) === false) {
			return [];
		}

		return Data::read($file);
	}

	/**
	 * Returns the absolute path to the model
	 */
	abstract public function root(): string|null;

	/**
	 * Stores the content on disk
	 *
	 * @internal
	 * @return static
	 */
	public function save(
		array $data = null,
		string $languageCode = null,
		bool $overwrite = false
	) {
		if ($this->kirby()->multilang() === true) {
			return $this->saveContentLanguage($data, $languageCode, $overwrite);
		}

		return $this->saveContent($data, $overwrite);
	}

	/**
	 * Saves the single language content
	 *
	 * @param array|null $data
	 * @param bool $overwrite
	 * @return static
	 */
	protected function saveContent(array $data = null, bool $overwrite = false)
	{
		// create a clone to avoid modifying the original
		$clone = $this->clone();

		// merge the new data with the existing content
		$clone->content()->update($data, $overwrite);

		// send the full content array to the writer
		$clone->writeContent($clone->content()->toArray());

		return $clone;
	}

	/**
	 * Saves a content language
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the language for the given code does not exist
	 */
	protected function saveContentLanguage(
		array $data = null,
		string $languageCode = null,
		bool $overwrite = false
	): static {
		// create a clone to not touch the original
		$clone = $this->clone();

		// fetch the matching language and update all the strings
		$language = $clone->contentLanguage($languageCode);

		if ($language === null) {
			throw new InvalidArgumentException('Invalid language: ' . $languageCode);
		}

		// get the content to store
		$content      = $language->update($data, $overwrite)->content();
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

			// merge the language's content with the new data
			$language->update($content, true);
		}

		// send the full language's content array to the writer
		$clone->writeContent($language->content(), $languageCode);

		// reset the content object
		$clone->content = null;

		// return the updated model
		return $clone;
	}

	/**
	 * @deprecated 4.0.0 Use `::saveContentLanguage()` instead
	 * @see ::saveContentLanguage
	 */
	protected function saveTranslation(
		array $data = null,
		string $languageCode = null,
		bool $overwrite = false
	) {
		return $this->saveContentLanguage($data, $languageCode, $overwrite);
	}

	/**
	 * Sets the Content object
	 *
	 * @return $this
	 */
	protected function setContent(array $content = null): static
	{
		if ($content !== null) {
			$content = new Content($content, $this);
		}

		$this->content = $content;
		return $this;
	}

	/**
	 * Create the languages collection from an array
	 *
	 * @return $this
	 */
	protected function setLanguages(array $languages = null): static
	{
		if ($languages !== null) {
			$this->languages = new Collection();

			foreach ($languages as $props) {
				$props['parent'] = $this;
				$language = new ContentLanguage(...$props);
				$this->languages->{$language->code()} = $language;
			}
		}

		return $this;
	}

	/**
	 * Only used to proxy deprecated translation property
	 * @private
	 * @todo content.translations.deprecated
	 * @codeCoverageIgnore
	 */
	protected function setProperties($props, array $keys = null)
	{
		parent::setProperties($props, $keys);
		$this->translations = $this->languages;
	}

	/**
	 * @deprecated 4.0.0 Use `::setLanguages` instead
	 * @see ::setLanguages
	 * @todo content.translations.deprecated
	 * @codeCoverageIgnore
	 */
	protected function setTranslations(array $translations = null)
	{
		// TODO: add deprecation warning
		return $this->setLanguages($translations);
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
		string $template = null,
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
		string $template = null,
		array $data = [],
		string|null $fallback = '',
		string $handler = 'template'
	): string {
		if ($template === null) {
			return $this->id() ?? '';
		}

		if ($handler !== 'template' && $handler !== 'safeTemplate') {
			throw new InvalidArgumentException('Invalid toString handler'); // @codeCoverageIgnore
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
	 * @deprecated 4.0.0 Use `::contentLanguage()` instead
	 * @see ::contentLanguage
	 * @todo content.translations.deprecated
	 * @codeCoverageIgnore
	 */
	public function translation(string $languageCode = null)
	{
		// TODO: add deprecation warning
		return $this->contentLanguage($languageCode);
	}

	/**
	 * @deprecated 4.0.0 Use `::contentLanguages()` instead
	 * @see ::contentLanguages
	 * @todo content.translations.deprecated
	 * @codeCoverageIgnore
	 */
	public function translations()
	{
		// TODO: add deprecation warning
		return $this->contentLanguages();
	}

	/**
	 * Updates the model data
	 *
	 * @return static
	 * @throws \Kirby\Exception\InvalidArgumentException If the input array contains invalid values
	 */
	public function update(
		array $input = null,
		string $languageCode = null,
		bool $validate = false
	) {
		$form = Form::for($this, [
			'ignoreDisabled' => $validate === false,
			'input'          => $input,
			'language'       => $languageCode,
		]);

		// validate the input
		if ($validate === true && $form->isInvalid() === true) {
			throw new InvalidArgumentException([
				'fallback' => 'Invalid form with errors',
				'details'  => $form->errors()
			]);
		}

		return $this->commit(
			'update',
			[
				static::CLASS_ALIAS => $this,
				'values' 			=> $form->data(),
				'strings' 			=> $form->strings(),
				'languageCode' 		=> $languageCode
			],
			function ($model, $values, $strings, $languageCode) {
				return $model->save($strings, $languageCode, true);
			}
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
	 * Low level data writer method
	 * to store the given data on disk or anywhere else
	 *
	 * @internal
	 */
	public function writeContent(array $data, string $languageCode = null): bool
	{
		return Data::write(
			$this->contentFile($languageCode),
			$this->contentFileData($data, $languageCode)
		);
	}
}
