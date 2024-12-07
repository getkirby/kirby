<?php

namespace Kirby\Content;

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
class ContentTranslation
{
	protected string $code;
	protected array|null $content;
	protected string $contentFile;
	protected ModelWithContent $parent;
	protected string|null $slug;

	/**
	 * Creates a new translation object
	 */
	public function __construct(array $props)
	{
		$this->code   = $props['code'];
		$this->parent = $props['parent'];
		$this->slug   = $props['slug'] ?? null;

		if ($content = $props['content'] ?? null) {
			$this->content = array_change_key_case($content);
		} else {
			$this->content = null;
		}
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
		return $this->code;
	}

	/**
	 * Returns the translation content
	 * as plain array
	 */
	public function content(): array
	{
		$parent  = $this->parent();
		$content = $this->content ??= $parent->readContent($this->code());

		// merge with the default content
		if (
			$this->isDefault() === false &&
			$defaultLanguage = $parent->kirby()->defaultLanguage()
		) {
			$content = array_merge(
				$parent->translation($defaultLanguage->code())?->content() ?? [],
				$content
			);
		}

		return $content;
	}

	/**
	 * Absolute path to the translation content file
	 */
	public function contentFile(): string
	{
		// temporary compatibility change (TODO: take this from the parent `ModelVersion` object)
		$identifier = $this->parent::CLASS_ALIAS === 'page' && $this->parent->isDraft() === true ?
			'changes' :
			'published';

		return $this->contentFile = $this->parent->storage()->contentFile(
			$identifier,
			$this->code,
			true
		);
	}

	/**
	 * Checks if the translation file exists
	 */
	public function exists(): bool
	{
		return
			empty($this->content) === false ||
			file_exists($this->contentFile()) === true;
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
		return $this->code() === $this->parent->kirby()->defaultLanguage()?->code();
	}

	/**
	 * Returns the parent page, file or site object
	 */
	public function parent(): ModelWithContent
	{
		return $this->parent;
	}

	/**
	 * Returns the custom translation slug
	 */
	public function slug(): string|null
	{
		return $this->slug ??= ($this->content()['slug'] ?? null);
	}

	/**
	 * Merge the old and new data
	 *
	 * @return $this
	 */
	public function update(array|null $data = null, bool $overwrite = false): static
	{
		$data = array_change_key_case((array)$data);

		$this->content = match ($overwrite) {
			true    => $data,
			default => array_merge($this->content(), $data)
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
}
