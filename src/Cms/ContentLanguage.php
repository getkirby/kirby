<?php

namespace Kirby\Cms;

/**
 * The content of each page, file, user or site
 * can be available in a language, represented by this class
 *
 * @package   Kirby Cms
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class ContentLanguage
{
	/**
	 * Creates a new content language object
	 */
	public function __construct(
		protected ModelWithContent $parent,
		protected string $code,
		protected array|null $content = null,
		protected string|null $slug = null,
	) {
		$this->content = match ($content) {
			null    => null,
			default => array_change_key_case($content)
		};
	}

	/**
	 * Improve `var_dump` output
	 * @see ::toArray()
	 */
	public function __debugInfo(): array
	{
		return $this->toArray();
	}

	/**
	 * Returns the language code
	 */
	public function code(): string
	{
		return $this->code;
	}

	/**
	 * Returns the language content as plain array
	 */
	public function content(): array
	{
		$parent  = $this->parent();
		$content = $this->content ??= $parent->readContent($this->code());

		// merge with the default content
		if (
			$this->isDefault() === false &&
			$language = $parent->kirby()->defaultLanguage()
		) {
			if ($default = $parent->contentLanguage($language->code())) {
				$content = array_merge($default->content(), $content);
			}
		}

		return $content;
	}

	/**
	 * Absolute path to the language content file
	 */
	public function contentFile(): string
	{
		return $this->parent->contentFile($this->code, true);
	}

	/**
	 * Checks if the language content file exists
	 */
	public function exists(): bool
	{
		return
			empty($this->content) === false ||
			file_exists($this->contentFile()) === true;
	}

	/**
	 * Returns the language code as id
	 */
	public function id(): string
	{
		return $this->code();
	}

	/**
	 * Checks if the this is the default language of the model
	 */
	public function isDefault(): bool
	{
		return $this->code() === $this->parent->kirby()->defaultLanguage()?->code();
	}

	/**
	 * Returns the parent page, file, user or site object
	 */
	public function parent(): ModelWithContent
	{
		return $this->parent;
	}

	/**
	 * Returns the custom language slug
	 */
	public function slug(): string|null
	{
		return $this->slug ??= $this->content()['slug'] ?? null;
	}

	/**
	 * Merge the old and new data
	 *
	 * @return $this
	 */
	public function update(array $data = [], bool $overwrite = false): static
	{
		$data = array_change_key_case($data);

		$this->content = match ($overwrite) {
			true    => $data,
			default => array_merge($this->content(), $data)
		};

		return $this;
	}

	/**
	 * Converts the most important props to an array
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
}
