<?php

namespace Kirby\Filesystem;

use Closure;
use Darkroom\Source;
use IntlDateFormatter;
use Kirby\Cms\App;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\NotFoundException;
use Kirby\Http\Response;
use Kirby\Image\Dimensions;
use Kirby\Image\Exif;
use Kirby\Image\Svg;
use Kirby\Sane\Sane;
use Kirby\Toolkit\Escape;
use Kirby\Toolkit\Html;
use Kirby\Toolkit\V;
use Stringable;

/**
 * Flexible File object with a set of helpful
 * methods to inspect and work with files.
 *
 * Image files additionally provide their dimensions
 * and exif data; other files return `null` for those.
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     3.6.0
 */
class File implements Stringable
{
	/**
	 * Validation rules to be used for `::match()`
	 *
	 * @var array<string, array{0: string, 1: string}>
	 */
	public static array $validations = [
		'maxsize'     => ['size',   'max'],
		'minsize'     => ['size',   'min'],
		'maxwidth'    => ['width',  'max'],
		'minwidth'    => ['width',  'min'],
		'maxheight'   => ['height', 'max'],
		'minheight'   => ['height', 'min'],
		'orientation' => ['orientation', 'same']
	];
	public static array $viewableTypes = [
		'avif',
		'jpg',
		'jpeg',
		'gif',
		'png',
		'svg',
		'webp'
	];

	/**
	 * Cached dimensions; `false` for files without
	 */
	protected Dimensions|false|null $dimensions = null;

	/**
	 * Cached exif data
	 */
	protected Exif|null $exif = null;

	/**
	 * Facts from the image's header, read when asked
	 */
	protected Source|null $source = null;

	/**
	 * @param $root Absolute file path
	 * @param $url Absolute file URL or a closure to resolve it lazily
	 */
	public function __construct(
		protected string|null $root = null,
		protected Closure|string|null $url = null
	) {
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
	 * Returns the `<img>` tag for images with a URL,
	 * otherwise the URL or root of the file
	 */
	public function __toString(): string
	{
		if ($this->type() === 'image' && $this->url() !== null) {
			return $this->html();
		}

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
			throw new Exception(
				message: 'The file "' . $this->root() . '" could not be copied'
			);
		}

		return new static(root: $target);
	}

	/**
	 * Returns the file as data uri
	 *
	 * @param $base64 Whether the data should be base64 encoded or not
	 */
	public function dataUri(bool $base64 = true): string
	{
		return match ($base64) {
			true  => 'data:' . $this->mime() . ';base64,' . $this->base64(),
			false => 'data:' . $this->mime() . ',' . Escape::url($this->read())
		};
	}

	/**
	 * Deletes the file
	 */
	public function delete(): bool
	{
		if (F::remove($this->root()) !== true) {
			throw new Exception(
				message: 'The file "' . $this->root() . '" could not be deleted'
			);
		}

		return true;
	}

	/**
	 * Returns the dimensions of the file as displayed, i.e. with
	 * its orientation applied, or `null` if the file has no visual
	 * dimensions; 0×0 for an image whose size can't be read
	 */
	public function dimensions(): Dimensions|null
	{
		// `false` caches that the file has no dimensions
		if ($this->dimensions !== null) {
			return $this->dimensions ?: null;
		}

		$source = $this->source();

		if ($source === null) {
			$this->dimensions = false;
			return null;
		}

		// the library reads no SVG
		if ($this->extension() === 'svg') {
			$svg = Svg::from($source->file);

			return $this->dimensions = new Dimensions(
				(int)round($svg->width() ?? 0),
				(int)round($svg->height() ?? 0)
			);
		}

		return $this->dimensions = Dimensions::from($source->size());
	}

	/**
	 * Automatically sends all needed headers
	 * for the file to be downloaded and
	 * echos the file's content
	 *
	 * @param $filename Optional filename for the download
	 */
	public function download(string|null $filename = null): string
	{
		return Response::download(
			$this->root(),
			$filename ?? $this->filename()
		);
	}

	/**
	 * Returns the exif object for image files
	 */
	public function exif(): Exif|null
	{
		$source = $this->source();

		if ($source === null) {
			return null;
		}

		return $this->exif ??= new Exif($source->exif(), $this->modified());
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
		return F::extension($this->root);
	}

	/**
	 * Returns the filename
	 */
	public function filename(): string
	{
		return basename($this->root);
	}

	/**
	 * Returns a md5 hash of the root
	 */
	public function hash(): string
	{
		return md5($this->root);
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
	 * Returns the height of the file or `null`
	 * if the file has no visual dimensions
	 */
	public function height(): int|null
	{
		return $this->dimensions()?->height();
	}

	/**
	 * Converts the file to html
	 */
	public function html(array $attr = []): string
	{
		if ($this->type() !== 'image') {
			return Html::a($this->url() ?? '', $attr);
		}

		if ($url = $this->url()) {
			return Html::img($url, $attr);
		}

		throw new LogicException(
			message: 'Calling File::html() for an image requires that the URL property is not null'
		);
	}

	/**
	 * Checks if a file is of a certain type
	 *
	 * @param $value An extension or mime type
	 */
	public function is(string $value): bool
	{
		return F::is($this->root(), $value);
	}

	/**
	 * Checks if the dimensions of the file are landscape
	 */
	public function isLandscape(): bool
	{
		return $this->dimensions()?->isLandscape() ?? false;
	}

	/**
	 * Checks if the dimensions of the file are portrait
	 */
	public function isPortrait(): bool
	{
		return $this->dimensions()?->isPortrait() ?? false;
	}

	/**
	 * Checks if the file is readable
	 */
	public function isReadable(): bool
	{
		return is_readable($this->root()) === true;
	}

	/**
	 * Checks if a configured driver makes thumbs of the image
	 */
	public function isResizable(): bool
	{
		$source = $this->source();

		return
			$source !== null &&
			App::instance()->darkroom()->tryPlan($source) !== null;
	}

	/**
	 * Checks if the dimensions of the file are square
	 */
	public function isSquare(): bool
	{
		return $this->dimensions()?->isSquare() ?? false;
	}

	/**
	 * Checks if a preview can be displayed for the file
	 * in the Panel or in the frontend
	 */
	public function isViewable(): bool
	{
		return in_array($this->extension(), static::$viewableTypes, true) === true;
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
		return App::instance(lazy: true);
	}

	/**
	 * Runs a set of validations on the file object
	 * (mainly for images).
	 *
	 * @throws Exception
	 */
	public function match(array $rules): bool
	{
		$rules = array_change_key_case($rules);

		if (is_array($rules['mime'] ?? null) === true) {
			$mime = $this->mime();

			// the MIME type could not be determined, but matching
			// to it was requested explicitly
			if ($mime === null) {
				throw new Exception(
					key: 'file.mime.missing',
					data: ['filename' => $this->filename()]
				);
			}

			$matches = array_any(
				$rules['mime'],
				fn (string $pattern) => Mime::matches($mime, $pattern)
			);

			if ($matches === false) {
				throw new Exception(
					key: 'file.mime.invalid',
					data: compact('mime')
				);
			}
		}

		if (is_array($rules['extension'] ?? null) === true) {
			$extension = $this->extension();

			if (in_array($extension, $rules['extension'], true) === false) {
				throw new Exception(
					key: 'file.extension.invalid',
					data: compact('extension')
				);
			}
		}

		if (is_array($rules['type'] ?? null) === true) {
			$type = $this->type();

			if (in_array($type, $rules['type'], true) === false) {
				throw new Exception(
					key: 'file.type.invalid',
					data: compact('type')
				);
			}
		}

		foreach (static::$validations as $key => $arguments) {
			$rule = $rules[$key] ?? null;

			if ($rule !== null) {
				$property  = $arguments[0];
				$validator = $arguments[1];

				// rules on dimensions only apply to files that have them
				if (
					in_array($property, ['width', 'height', 'orientation'], true) === true &&
					$this->dimensions() === null
				) {
					continue;
				}

				if (V::$validator($this->$property(), $rule) === false) {
					throw new Exception(
						key: 'file.' . $key,
						data: [$property => $rule]
					);
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
	 * Returns the file's last modification time
	 *
	 * @param 'date'|'intl'|'strftime'|null $handler Custom date handler or `null`
	 *                                               for the globally configured one
	 * @return ($format is null ? int|false : string|false)
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
	 * @param $overwrite Force overwriting any existing files
	 */
	public function move(string $newRoot, bool $overwrite = false): static
	{
		if (F::move($this->root(), $newRoot, $overwrite) !== true) {
			throw new Exception(
				message: 'The file: "' . $this->root() . '" could not be moved to: "' . $newRoot . '"'
			);
		}

		return new static(root: $newRoot);
	}

	/**
	 * Getter for the name of the file
	 * without the extension
	 */
	public function name(): string
	{
		return pathinfo($this->root, PATHINFO_FILENAME);
	}

	/**
	 * Returns the file size in a
	 * human-readable format
	 *
	 * @param $locale Locale for number formatting,
	 *               `null` for the current locale,
	 *               `false` to disable number formatting
	 */
	public function niceSize(string|false|null $locale = null): string
	{
		return F::niceSize($this->root(), $locale);
	}

	/**
	 * Returns the orientation as string
	 * `landscape` | `portrait` | `square`
	 */
	public function orientation(): string|false
	{
		return $this->dimensions()?->orientation() ?? false;
	}

	/**
	 * Returns the ratio of the file or `null`
	 * if the file has no visual dimensions
	 */
	public function ratio(): float|null
	{
		return $this->dimensions()?->ratio();
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
	 * or `false` if the file does not exist
	 */
	public function realpath(): string|false
	{
		return realpath($this->root());
	}

	/**
	 * Changes the name of the file without
	 * touching the extension
	 *
	 * @param $overwrite Force overwrite existing files
	 */
	public function rename(string $newName, bool $overwrite = false): static
	{
		$newRoot = F::rename($this->root(), $newName, $overwrite);

		if ($newRoot === false) {
			throw new Exception(
				message: 'The file: "' . $this->root() . '" could not be renamed to: "' . $newName . '"'
			);
		}

		return new static(root: $newRoot);
	}

	/**
	 * Returns the given file path; methods that only
	 * need the path read it directly, so subclasses
	 * can make the file here on first access
	 */
	public function root(): string|null
	{
		return $this->root;
	}

	/**
	 * Sanitizes the file contents depending on the file type
	 * by overwriting the file with the sanitized version
	 * @since 3.6.0
	 *
	 * @param $typeLazy Explicit sane handler type string,
	 *                 `true` for lazy autodetection or
	 *                 `false` for normal autodetection
	 *
	 * @throws InvalidArgumentException If the file didn't pass validation
	 * @throws LogicException If more than one handler applies
	 * @throws NotFoundException If the handler was not found
	 * @throws Exception On other errors
	 */
	public function sanitizeContents(string|bool $typeLazy = false): void
	{
		Sane::sanitizeFile($this->root(), $typeLazy);
	}

	/**
	 * Returns the sha1 hash of the file
	 * @since 3.6.0
	 *
	 * @throws Exception If the file cannot be read
	 */
	public function sha1(): string
	{
		if (is_file($this->root()) === false) {
			throw new Exception(
				message: 'The file "' . $this->root() . '" could not be hashed'
			);
		}

		$hash = sha1_file($this->root());

		// @codeCoverageIgnoreStart
		// `sha1_file` failure after `is_file === true` would require
		// a filesystem race condition between the two calls
		if ($hash === false) {
			throw new Exception(
				message: 'The file "' . $this->root() . '" could not be hashed'
			);
		}
		// @codeCoverageIgnoreEnd

		return $hash;
	}

	/**
	 * Returns the raw size of the file
	 */
	public function size(): int
	{
		return F::size($this->root());
	}

	/**
	 * The image's facts, read from its header when asked;
	 * `null` for files that are no image
	 * @internal
	 */
	protected function source(): Source|null
	{
		if ($this->root() === null || $this->type() !== 'image') {
			return null;
		}

		return $this->source ??= new Source($this->root());
	}

	/**
	 * Converts the media object to a
	 * plain PHP array
	 *
	 * @return array<string, mixed>
	 */
	public function toArray(): array
	{
		return [
			'dimensions'   => $this->dimensions()?->toArray(),
			'exif'         => $this->exif()?->toArray(),
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
		return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
	}

	/**
	 * Returns the file type.
	 */
	public function type(): string|null
	{
		$path = $this->root ?? $this->url();

		if ($path === null) {
			return null;
		}

		return F::type($path);
	}

	/**
	 * Returns the absolute url for the file
	 */
	public function url(): string|null
	{
		// resolve a lazily passed URL only when needed
		if ($this->url instanceof Closure) {
			$this->url = ($this->url)();
		}

		return $this->url;
	}

	/**
	 * Validates the file contents depending on the file type
	 *
	 * @param $typeLazy Explicit sane handler type string,
	 *                 `true` for lazy autodetection or
	 *                 `false` for normal autodetection
	 *
	 * @throws InvalidArgumentException If the file didn't pass validation
	 * @throws NotFoundException If the handler was not found
	 * @throws Exception On other errors
	 */
	public function validateContents(string|bool $typeLazy = false): void
	{
		Sane::validateFile($this->root(), $typeLazy);
	}

	/**
	 * Returns the width of the file or `null`
	 * if the file has no visual dimensions
	 */
	public function width(): int|null
	{
		return $this->dimensions()?->width();
	}

	/**
	 * Writes content to the file
	 */
	public function write(string $content): bool
	{
		if (F::write($this->root(), $content) !== true) {
			throw new Exception(
				message: 'The file "' . $this->root() . '" could not be written'
			);
		}

		return true;
	}
}
