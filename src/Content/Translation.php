<?php

namespace Kirby\Content;

use Kirby\Cms\Helpers;
use Kirby\Cms\Language;
use Kirby\Cms\ModelWithContent;
use Kirby\Exception\Exception;

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
	 * @deprecated 5.0.0 Use `::language()->code()` instead
	 */
	public function code(): string
	{
		Helpers::deprecated('`$translation->code()` has been deprecated. Use `$translation->language()->code()` instead.', 'translation-methods');
		return $this->language->code();
	}

	/**
	 * Returns the translation content
	 * as plain array
	 *
	 * @deprecated 5.0.0 Use `::version()->content()->toArray()` instead
	 */
	public function content(): array
	{
		Helpers::deprecated('`$translation->content()->toArray()` has been deprecated. Use `$translation->version()->content()` instead.', 'translation-methods');
		return $this->version->content($this->language)->toArray();
	}

	/**
	 * Absolute path to the translation content file
	 *
	 * @deprecated 5.0.0
	 */
	public function contentFile(): string
	{
		Helpers::deprecated('`$translation->contentFile()` has been deprecated. Please let us know if you have a use case for a replacement.', 'translation-methods');
		return $this->version->contentFile($this->language);
	}

	/**
	 * Creates a new Translation for the given model
	 *
	 * @todo Needs to be refactored as soon as Version::create becomes static
	 * 		 (see https://github.com/getkirby/kirby/pull/6491#discussion_r1652264408)
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

		$version->save($fields, $language);

		return new static(
			model: $model,
			version: $version,
			language: $language,
		);
	}

	/**
	 * Checks if the translation file exists
	 *
	 * @deprecated 5.0.0 Use `::version()->exists()` instead
	 */
	public function exists(): bool
	{
		Helpers::deprecated('`$translation->exists()` has been deprecated. Use `$translation->version()->exists()` instead.', 'translation-methods');
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
	 * @deprecated 5.0.0 Use `::language()->isDefault()` instead
	 */
	public function isDefault(): bool
	{
		Helpers::deprecated('`$translation->isDefault()` has been deprecated. Use `$translation->language()->isDefault()` instead.', 'translation-methods');
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
	 * @deprecated 5.0.0 Use `$translation->model()` instead
	 */
	public function parent(): ModelWithContent
	{
		throw new Exception(
			message: '`$translation->parent()` has been deprecated. Please use `$translation->model()` instead'
		);
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
	 * @deprecated 5.0.0 Use `$model->version()->update()` instead
	 */
	public function update(array|null $data = null, bool $overwrite = false): static
	{
		throw new Exception(
			message: '`$translation->update()` has been deprecated. Please use `$model->version()->update()` instead'
		);
	}

	/**
	 * Returns the version
	 */
	public function version(): Version
	{
		return $this->version;
	}
}
