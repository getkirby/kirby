<?php

namespace Kirby\Cms;

use Closure;
use Darkroom\Plan;
use Kirby\Filesystem\Asset;
use Kirby\Filesystem\F;
use Kirby\Filesystem\File as FilesystemFile;
use Kirby\Image\Dimensions;

/**
 * A thumb of a file or asset, planned from the
 * original's header: its size is known without
 * making it, the thumb is made on first access
 * to its content
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class FileVersion extends FilesystemFile
{
	/**
	 * `false` if no driver makes the thumb
	 */
	protected Plan|false|null $plan = null;

	/**
	 * Named by its plan in the original's media
	 * folder, unless a `root` or `url` is given
	 *
	 * @param array<string, mixed> $options
	 */
	public function __construct(
		protected File|Asset $original,
		protected array $options = [],
		string|null $root = null,
		Closure|string|null $url = null
	) {
		if ($root === null && $url === null) {
			$name = $this->basename();

			if ($name !== null) {
				$root = $original->mediaDir() . '/' . $name;
				$url  = $original->mediaUrl($name);
			}
		}

		parent::__construct(root: $root, url: $url);
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
	 * Named by the plan's key, so requests for the same
	 * image share the thumb, and signed for the media route
	 */
	protected function basename(): string|null
	{
		$plan = $this->plan();

		if ($plan === null) {
			return null;
		}

		$name = F::safeBasename($this->original->filename());
		$name = $name . '-' . $plan->key() . '.' . $plan->format->extension();

		return Media::sign($this->original, $name);
	}

	/**
	 * The planned size, without making the thumb
	 */
	public function dimensions(): Dimensions|null
	{
		$plan = $this->plan();

		if ($plan === null) {
			return parent::dimensions();
		}

		return Dimensions::from($plan->size());
	}

	/**
	 * Checks if the version has already been generated
	 */
	public function exists(): bool
	{
		return $this->root !== null && file_exists($this->root) === true;
	}

	/**
	 * Returns the filename without making the thumb
	 */
	public function filename(): string
	{
		return basename($this->root ?? $this->url());
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
		// the name without making the thumb
		return dirname($this->original()->id()) . '/' . $this->filename();
	}

	/**
	 * Checks if the version is a thumb a driver makes,
	 * without making it
	 */
	public function isResizable(): bool
	{
		return $this->plan() !== null;
	}

	/**
	 * Returns the parent Kirby App instance
	 */
	public function kirby(): App
	{
		return $this->original()->kirby();
	}

	/**
	 * @deprecated 7.0.0 Use `::options()` instead
	 *
	 * @return array<string, mixed>
	 */
	public function modifications(): array
	{
		return $this->options();
	}

	/**
	 * Returns the thumb options, as planned
	 *
	 * @return array<string, mixed>
	 */
	public function options(): array
	{
		$plan = $this->plan();

		if ($plan === null) {
			return $this->options;
		}

		return $plan->options->toArray();
	}

	/**
	 * Returns the instance of the original File object
	 */
	public function original(): File|Asset
	{
		return $this->original;
	}

	/**
	 * Planned from the header the original
	 * read once for all its thumbs
	 * @internal
	 */
	protected function plan(): Plan|null
	{
		if ($this->plan === null) {
			$file = $this->original;

			if ($this->original instanceof File === true) {
				$file = $this->original->asset();
			}

			$source = $file->source();

			$this->plan = match ($source) {
				null    => false,
				default => $this->kirby()->darkroom()->tryPlan($source, $this->options) ?? false
			};
		}

		return $this->plan !== false ? $this->plan : null;
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
	 * Makes the thumb
	 *
	 * @return $this
	 */
	public function save(): static
	{
		$this->kirby()->thumb(
			$this->original()->root(),
			$this->root,
			$this->options()
		);

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
			'options' => $this->options()
		];

		ksort($array);

		return $array;
	}
}
