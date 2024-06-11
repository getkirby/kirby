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
	 */
	public function code(): string
	{
		return $this->language->code();
	}

	/**
	 * Returns the translation content
	 * as plain array
	 */
	public function content(): array
	{
		return $this->version->read($this->language);
	}

	/**
	 * Absolute path to the translation content file
	 */
	public function contentFile(): string
	{
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
	 */
	public function exists(): bool
	{
		return $this->version->exists($this->language);
	}

	/**
	 * Returns the translation code as id
	 */
	public function id(): string
	{
		return $this->code();
	}

	/**
	 * Checks if the this is the default translation
	 * of the model
	 */
	public function isDefault(): bool
	{
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
		return $this->content()['slug'] ?? null;
	}

	/**
	 * Merge the old and new data
	 */
	public function update(array|null $data = null, bool $overwrite = false): static
	{
		$data = array_change_key_case((array)$data);

		$this->content = match ($overwrite) {
			true    => $data,
			default => [...$this->content(), ...$data]
		};

		return $this;
	}

	/**
	 * Converts the most important translation
	 * props to an array
	 */
	public function toArray(): array
	{
		return [
			'code'    => $this->code(),
			'content' => $this->content(),
			'exists'  => $this->exists(),
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
