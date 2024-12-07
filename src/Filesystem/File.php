<?php

namespace Kirby\Filesystem;

use IntlDateFormatter;
use Kirby\Cms\App;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Http\Response;
use Kirby\Sane\Sane;
use Kirby\Toolkit\Escape;
use Kirby\Toolkit\Html;
use Kirby\Toolkit\V;

/**
 * Flexible File object with a set of helpful
 * methods to inspect and work with files.
 *
 * @since 3.6.0
 *
 * @package   Kirby Filesystem
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class File
{
	/**
	 * Parent file model
	 * The model object must use the `\Kirby\Filesystem\IsFile` trait
	 */
	protected object|null $model;

	/**
	 * Absolute file path
	 */
	protected string|null $root;

	/**
	 * Absolute file URL
	 */
	protected string|null $url;

	/**
	 * Validation rules to be used for `::match()`
	 */
	public static array $validations = [
		'maxsize' => ['size', 'max'],
		'minsize' => ['size', 'min']
	];

	/**
	 * Constructor sets all file properties
	 *
	 * @param array|string|null $props Properties or deprecated `$root` string
	 * @param string|null $url Deprecated argument, use `$props['url']` instead
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException When the model does not use the `Kirby\Filesystem\IsFile` trait
	 */
	public function __construct(
		array|string|null $props = null,
		string|null $url = null
	) {
		// Legacy support for old constructor of
		// the `Kirby\Image\Image` class
		if (is_array($props) === false) {
			$props = [
				'root' => $props,
				'url'  => $url
			];
		}

		$this->root  = $props['root'] ?? null;
		$this->url   = $props['url'] ?? null;
		$this->model = $props['model'] ?? null;

		if (
			$this->model !== null &&
			method_exists($this->model, 'hasIsFileTrait') !== true
		) {
			throw new InvalidArgumentException('The model object must use the "Kirby\Filesystem\IsFile" trait');
		}
	}

	/**
	 * Improved `var_dump` output
	 * @codeCoverageIgnore
	 */
	public function __debugInfo(): array
	{
		return $this->toArray();
	}

	/**
	 * Returns the URL for the file object
	 */
	public function __toString(): string
	{
		return $this->url() ?? $this->root() ?? '';
	}

	/**
	 * Returns the file content as base64 encoded string
	 */
	public function base64(): string
	{
		return base64_encode($this->read());
	}

	/**
	 * Copy a file to a new location.
	 */
	public function copy(string $target, bool $force = false): static
	{
		if (F::copy($this->root(), $target, $force) !== true) {
			throw new Exception('The file "' . $this->root() . '" could not be copied');
		}

		return new static($target);
	}

	/**
	 * Returns the file as data uri
	 *
	 * @param bool $base64 Whether the data should be base64 encoded or not
	 */
	public function dataUri(bool $base64 = true): string
	{
		if ($base64 === true) {
			return 'data:' . $this->mime() . ';base64,' . $this->base64();
		}

		return 'data:' . $this->mime() . ',' . Escape::url($this->read());
	}

	/**
	 * Deletes the file
	 */
	public function delete(): bool
	{
		if (F::remove($this->root()) !== true) {
			throw new Exception('The file "' . $this->root() . '" could not be deleted');
		}

		return true;
	}

	/*
	 * Automatically sends all needed headers
	 * for the file to be downloaded and
	 * echos the file's content
	 *
	 * @param string|null $filename Optional filename for the download
	 */
	public function download(string|null $filename = null): string
	{
		return Response::download($this->root(), $filename ?? $this->filename());
	}

	/**
	 * Checks if the file actually exists
	 */
	public function exists(): bool
	{
		return file_exists($this->root()) === true;
	}

	/**
	 * Returns the current lowercase extension (without .)
	 */
	public function extension(): string
	{
		return F::extension($this->root());
	}

	/**
	 * Returns the filename
	 */
	public function filename(): string
	{
		return basename($this->root());
	}

	/**
	 * Returns a md5 hash of the root
	 */
	public function hash(): string
	{
		return md5($this->root());
	}

	/**
	 * Sends an appropriate header for the asset
	 */
	public function header(bool $send = true): Response|null
	{
		$response = new Response('', $this->mime());

		if ($send !== true) {
			return $response;
		}

		$response->send();
		return null;
	}

	/**
	 * Converts the file to html
	 */
	public function html(array $attr = []): string
	{
		return Html::a($this->url() ?? '', $attr);
	}

	/**
	 * Checks if a file is of a certain type
	 *
	 * @param string $value An extension or mime type
	 */
	public function is(string $value): bool
	{
		return F::is($this->root(), $value);
	}

	/**
	 * Checks if the file is readable
	 */
	public function isReadable(): bool
	{
		return is_readable($this->root()) === true;
	}

	/**
	 * Checks if the file is a resizable image
	 */
	public function isResizable(): bool
	{
		return false;
	}

	/**
	 * Checks if a preview can be displayed for the file
	 * in the panel or in the frontend
	 */
	public function isViewable(): bool
	{
		return false;
	}

	/**
	 * Checks if the file is writable
	 */
	public function isWritable(): bool
	{
		return F::isWritable($this->root());
	}

	/**
	 * Returns the app instance if it exists
	 */
	public function kirby(): App|null
	{
		return App::instance(null, true);
	}

	/**
	 * Runs a set of validations on the file object
	 * (mainly for images).
	 *
	 * @throws \Kirby\Exception\Exception
	 */
	public function match(array $rules): bool
	{
		$rules = array_change_key_case($rules);

		if (is_array($rules['mime'] ?? null) === true) {
			$mime = $this->mime();

			// the MIME type could not be determined, but matching
			// to it was requested explicitly
			if ($mime === null) {
				throw new Exception([
					'key'  => 'file.mime.missing',
					'data' => ['filename' => $this->filename()]
				]);
			}

			// determine if any pattern matches the MIME type;
			// once any pattern matches, `$carry` is `true` and the rest is skipped
			$matches = array_reduce(
				$rules['mime'],
				fn ($carry, $pattern) => $carry || Mime::matches($mime, $pattern),
				false
			);

			if ($matches !== true) {
				throw new Exception([
					'key'  => 'file.mime.invalid',
					'data' => compact('mime')
				]);
			}
		}

		if (is_array($rules['extension'] ?? null) === true) {
			$extension = $this->extension();
			if (in_array($extension, $rules['extension']) !== true) {
				throw new Exception([
					'key'  => 'file.extension.invalid',
					'data' => compact('extension')
				]);
			}
		}

		if (is_array($rules['type'] ?? null) === true) {
			$type = $this->type();
			if (in_array($type, $rules['type']) !== true) {
				throw new Exception([
					'key'  => 'file.type.invalid',
					'data' => compact('type')
				]);
			}
		}

		foreach (static::$validations as $key => $arguments) {
			$rule = $rules[$key] ?? null;

			if ($rule !== null) {
				$property  = $arguments[0];
				$validator = $arguments[1];

				if (V::$validator($this->$property(), $rule) === false) {
					throw new Exception([
						'key'  => 'file.' . $key,
						'data' => [$property => $rule]
					]);
				}
			}
		}

		return true;
	}

	/**
	 * Detects the mime type of the file
	 */
	public function mime(): string|null
	{
		return Mime::type($this->root());
	}

	/**
	 * Returns the parent file model, which uses this instance as proxied file asset
	 */
	public function model(): object|null
	{
		return $this->model;
	}

	/**
	 * Returns the file's last modification time
	 *
	 * @param 'date'|'intl'|'strftime'|null $handler Custom date handler or `null`
	 *                                               for the globally configured one
	 */
	public function modified(
		string|IntlDateFormatter|null $format = null,
		string|null $handler = null
	): string|int|false {
		return F::modified($this->root(), $format, $handler);
	}

	/**
	 * Move the file to a new location
	 *
	 * @param bool $overwrite Force overwriting any existing files
	 */
	public function move(string $newRoot, bool $overwrite = false): static
	{
		if (F::move($this->root(), $newRoot, $overwrite) !== true) {
			throw new Exception('The file: "' . $this->root() . '" could not be moved to: "' . $newRoot . '"');
		}

		return new static($newRoot);
	}

	/**
	 * Getter for the name of the file
	 * without the extension
	 */
	public function name(): string
	{
		return pathinfo($this->root(), PATHINFO_FILENAME);
	}

	/**
	 * Returns the file size in a
	 * human-readable format
	 *
	 * @param string|false|null $locale Locale for number formatting,
	 *                                  `null` for the current locale,
	 *                                  `false` to disable number formatting
	 */
	public function niceSize(string|false|null $locale = null): string
	{
		return F::niceSize($this->root(), $locale);
	}

	/**
	 * Reads the file content and returns it.
	 */
	public function read(): string|false
	{
		return F::read($this->root());
	}

	/**
	 * Returns the absolute path to the file
	 */
	public function realpath(): string
	{
		return realpath($this->root());
	}

	/**
	 * Changes the name of the file without
	 * touching the extension
	 *
	 * @param bool $overwrite Force overwrite existing files
	 */
	public function rename(string $newName, bool $overwrite = false): static
	{
		$newRoot = F::rename($this->root(), $newName, $overwrite);

		if ($newRoot === false) {
			throw new Exception('The file: "' . $this->root() . '" could not be renamed to: "' . $newName . '"');
		}

		return new static($newRoot);
	}

	/**
	 * Returns the given file path
	 */
	public function root(): string|null
	{
		return $this->root ??= $this->model?->root();
	}

	/**
	 * Returns the absolute url for the file
	 */
	public function url(): string|null
	{
		// lazily determine the URL from the model object
		// only if it's needed to avoid breaking custom file::url
		// components that rely on `$cmsFile->asset()` methods
		return $this->url ??= $this->model?->url();
	}

	/**
	 * Sanitizes the file contents depending on the file type
	 * by overwriting the file with the sanitized version
	 * @since 3.6.0
	 *
	 * @param string|bool $typeLazy Explicit sane handler type string,
	 *                              `true` for lazy autodetection or
	 *                              `false` for normal autodetection
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the file didn't pass validation
	 * @throws \Kirby\Exception\LogicException If more than one handler applies
	 * @throws \Kirby\Exception\NotFoundException If the handler was not found
	 * @throws \Kirby\Exception\Exception On other errors
	 */
	public function sanitizeContents(string|bool $typeLazy = false): void
	{
		Sane::sanitizeFile($this->root(), $typeLazy);
	}

	/**
	 * Returns the sha1 hash of the file
	 * @since 3.6.0
	 */
	public function sha1(): string
	{
		return sha1_file($this->root());
	}

	/**
	 * Returns the raw size of the file
	 */
	public function size(): int
	{
		return F::size($this->root());
	}

	/**
	 * Converts the media object to a
	 * plain PHP array
	 */
	public function toArray(): array
	{
		return [
			'extension'    => $this->extension(),
			'filename'     => $this->filename(),
			'hash'         => $this->hash(),
			'isReadable'   => $this->isReadable(),
			'isResizable'  => $this->isResizable(),
			'isWritable'   => $this->isWritable(),
			'mime'         => $this->mime(),
			'modified'     => $this->modified('c'),
			'name'         => $this->name(),
			'niceSize'     => $this->niceSize(),
			'root'         => $this->root(),
			'safeName'     => F::safeName($this->name()),
			'size'         => $this->size(),
			'type'         => $this->type(),
			'url'          => $this->url()
		];
	}

	/**
	 * Converts the entire file array into
	 * a json string
	 */
	public function toJson(): string
	{
		return json_encode($this->toArray());
	}

	/**
	 * Returns the file type.
	 */
	public function type(): string|null
	{
		return F::type($this->root());
	}

	/**
	 * Validates the file contents depending on the file type
	 *
	 * @param string|bool $typeLazy Explicit sane handler type string,
	 *                              `true` for lazy autodetection or
	 *                              `false` for normal autodetection
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the file didn't pass validation
	 * @throws \Kirby\Exception\NotFoundException If the handler was not found
	 * @throws \Kirby\Exception\Exception On other errors
	 */
	public function validateContents(string|bool $typeLazy = false): void
	{
		Sane::validateFile($this->root(), $typeLazy);
	}

	/**
	 * Writes content to the file
	 */
	public function write(string $content): bool
	{
		if (F::write($this->root(), $content) !== true) {
			throw new Exception('The file "' . $this->root() . '" could not be written');
		}

		return true;
	}
}
