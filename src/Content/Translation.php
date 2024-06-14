<?php

namespace Kirby\Content;

use Kirby\Cms\Language;
use Kirby\Cms\ModelWithContent;

/**
 * Each page, file or site can have multiple
 * translated versions of their content,
 * represented by this class
 *
 * @package   Kirby Content
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Translation extends ContentTranslation
{
	/**
	 * Creates a new translation object
	 */
	public function __construct(
		protected ModelWithContent $model,
		protected Version $version,
		protected Language $language
	) {
	}

	/**
	 * Improve `var_dump` output
	 * @codeCoverageIgnore
	 */
	public function __debugInfo(): array
	{
		return $this->toArray();
	}

	/**
	 * Returns the language code of the
	 * translation
	 *
	 * @deprecated since 5.0.0 Use `::language()->code()` instead
	 */
	public function code(): string
	{
		Helpers::deprecated('`$translation->code()` has been deprecated. Use `$translation->language()->code()` instead.', 'translation-code');
		return $this->language->code();
	}

	/**
	 * Returns the translation content
	 * as plain array
	 *
	 * @deprecated since 5.0.0 Use `::version()->content()->toArray()` instead
	 */
	public function content(): array
	{
		Helpers::deprecated('`$translation->content()` has been deprecated. Use `$translation->version()->content()` instead.', 'translation-content');
		return $this->version->content($this->language)->toArray();
	}

	/**
	 * Absolute path to the translation content file
	 *
	 * @deprecated since 5.0.0 Use `::version()->contentFile()` instead
	 */
	public function contentFile(): string
	{
		Helpers::deprecated('`$translation->contentFile()` has been deprecated. Use `$translation->version()->contentFile()` instead.', 'translation-contentFile');
		return $this->version->contentFile($this->language);
	}

	/**
	 * Creates a new Translation for the given model
	 */
	public static function create(
		ModelWithContent $model,
		Version $version,
		Language $language,
		array $fields,
		string|null $slug = null
	): static {
		// add the custom slug to the fields array
		if ($slug !== null) {
			$fields['slug'] = $slug;
		}

		$version->create($fields, $language);

		return new static(
			model: $model,
			version: $version,
			language: $language,
		);
	}

	/**
	 * Checks if the translation file exists
	 *
	 * @deprecated since 5.0.0 Use `::version()->exists()` instead
	 */
	public function exists(): bool
	{
		Helpers::deprecated('`$translation->exists()` has been deprecated. Use `$translation->version()->exists()` instead.', 'translation-exists');
		return $this->version->exists($this->language);
	}

	/**
	 * Returns the translation code as id
	 */
	public function id(): string
	{
		return $this->language->code();
	}

	/**
	 * Checks if the this is the default translation
	 * of the model
	 *
	 * @deprecated since 5.0.0 Use `::language()->isDefault()` instead
	 */
	public function isDefault(): bool
	{
		Helpers::deprecated('`$translation->isDefault()` has been deprecated. Use `$translation->language()->isDefault()` instead.', 'translation-isDefault');
		return $this->language->isDefault();
	}

	/**
	 * Returns the language
	 */
	public function language(): Language
	{
		return $this->language;
	}

	/**
	 * Returns the parent page, file or site object
	 */
	public function model(): ModelWithContent
	{
		return $this->model;
	}

	/**
	 * Returns the custom translation slug
	 */
	public function slug(): string|null
	{
		return $this->version->read($this->language)['slug'] ?? null;
	}

	/**
	 * Converts the most important translation
	 * props to an array
	 */
	public function toArray(): array
	{
		return [
			'code'    => $this->language->code(),
			'content' => $this->version->content($this->language)->toArray(),
			'exists'  => $this->version->exists($this->language),
			'slug'    => $this->slug(),
		];
	}

	/**
	 * Returns the version
	 */
	public function version(): Version
	{
		return $this->version;
	}
}
