<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Asset;
use Kirby\Filesystem\F;
use Kirby\Filesystem\File as FilesystemFile;

/**
 * A modified version of a file or asset
 * (e.g. a thumb), which gets generated on
 * first access to its content
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class FileVersion extends FilesystemFile
{
	/**
	 * @var array<string, mixed>
	 */
	protected array $modifications;
	protected File|Asset $original;

	/**
	 * @param array<string, mixed> $props
	 */
	public function __construct(array $props)
	{
		parent::__construct(
			root: $props['root'] ?? null,
			url:  $props['url'] ?? null
		);

		$this->original      = $props['original'];
		$this->modifications = $props['modifications'] ?? [];
	}

	/**
	 * Proxy for content field getters of the original file
	 */
	public function __call(string $method, array $arguments = []): mixed
	{
		if ($this->original() instanceof File) {
			return $this->original()->content()->get($method);
		}

		return null;
	}

	/**
	 * Checks if the version has already been generated
	 */
	public function exists(): bool
	{
		return $this->root !== null && file_exists($this->root) === true;
	}

	/**
	 * Converts the version to html and infers
	 * the alt text from the original's content
	 */
	public function html(array $attr = []): string
	{
		if ($this->original() instanceof File) {
			$alt = $this->original()->content()->get('alt');

			if ($alt->isNotEmpty() === true) {
				$attr['alt'] ??= $alt->value();
			}
		}

		return parent::html($attr);
	}

	/**
	 * Returns the unique ID
	 */
	public function id(): string
	{
		return dirname($this->original()->id()) . '/' . $this->filename();
	}

	/**
	 * Returns the parent Kirby App instance
	 */
	public function kirby(): App
	{
		return $this->original()->kirby();
	}

	/**
	 * Returns an array with all applied modifications
	 *
	 * @return array<string, mixed>
	 */
	public function modifications(): array
	{
		return $this->modifications;
	}

	/**
	 * Returns the instance of the original File object
	 *
	 * @return File|Asset
	 */
	public function original(): mixed
	{
		return $this->original;
	}

	/**
	 * Returns the absolute path to the version
	 * and generates the version if it doesn't exist yet
	 */
	public function root(): string|null
	{
		if ($this->root !== null && $this->exists() === false) {
			$this->save();
		}

		return $this->root;
	}

	/**
	 * Applies the stored modifications and
	 * saves the file on disk
	 *
	 * @return $this
	 */
	public function save(): static
	{
		$this->kirby()->thumb(
			$this->original()->root(),
			$this->root,
			$this->modifications()
		);

		// like `Media::thumb()`, as the job is done
		F::remove(dirname($this->root) . '/.jobs/' . basename($this->root) . '.json');

		return $this;
	}

	/**
	 * Converts the object to an array
	 *
	 * @return array<string, mixed>
	 */
	public function toArray(): array
	{
		$array = [
			...parent::toArray(),
			'modifications' => $this->modifications()
		];

		ksort($array);

		return $array;
	}

	/**
	 * Returns the file type without generating the version
	 */
	public function type(): string|null
	{
		return F::type($this->root ?? $this->url());
	}
}
