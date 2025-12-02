<?php

namespace Kirby\Image;

use Kirby\Cms\FileVersion;
use Kirby\Content\Content;
use Kirby\Exception\LogicException;
use Kirby\Filesystem\File;
use Kirby\Toolkit\Html;

/**
 * A representation of an image file
 * with dimensions, optional exif data and
 * a connection to our darkroom classes to resize/crop
 * images.
 *
 * Extends the `Kirby\Filesystem\File` class with
 * those image-specific methods.
 *
 * @package   Kirby Image
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Image extends File
{
	protected Exif|null $exif = null;
	protected Dimensions|null $dimensions = null;

	public static array $resizableTypes = [
		'avif',
		'jpg',
		'jpeg',
		'gif',
		'png',
		'webp'
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
	 * Validation rules to be used for `::match()`
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

	/**
	 * Returns the `<img>` tag for the image object
	 */
	public function __toString(): string
	{
		return $this->html();
	}

	/**
	 * Returns the dimensions of the file if possible
	 */
	public function dimensions(): Dimensions
	{
		if ($this->dimensions !== null) {
			return $this->dimensions;
		}

		if (in_array($this->mime(), [
			'image/avif',
			'image/gif',
			'image/jpeg',
			'image/jp2',
			'image/png',
			'image/webp'
		], true)) {
			return $this->dimensions = Dimensions::forImage($this);
		}

		if ($this->extension() === 'svg') {
			return $this->dimensions = Dimensions::forSvg($this->root);
		}

		return $this->dimensions = new Dimensions(0, 0);
	}

	/**
	 * Returns the exif object for this file (if image)
	 */
	public function exif(): Exif
	{
		return $this->exif ??= new Exif($this);
	}

	/**
	 * Returns the height of the asset
	 */
	public function height(): int
	{
		return $this->dimensions()->height();
	}

	/**
	 * Converts the file to html
	 */
	public function html(array $attr = []): string
	{
		$model = match (true) {
			$this->model instanceof FileVersion => $this->model->original(),
			default                             => $this->model
		};

		// if no alt text explicitly provided,
		// try to infer from model content file
		if (
			$model !== null &&
			method_exists($model, 'content') === true &&
			$model->content() instanceof Content &&
			$model->content()->get('alt')->isNotEmpty() === true
		) {
			$attr['alt'] ??= $model->content()->get('alt')->value();
		}

		if ($url = $this->url()) {
			return Html::img($url, $attr);
		}

		throw new LogicException(
			message: 'Calling Image::html() requires that the URL property is not null'
		);
	}

	/**
	 * Returns the PHP imagesize array
	 */
	public function imagesize(): array|false
	{
		return @getimagesize($this->root);
	}

	/**
	 * Checks if the dimensions of the asset are portrait
	 */
	public function isPortrait(): bool
	{
		return $this->dimensions()->portrait();
	}

	/**
	 * Checks if the dimensions of the asset are landscape
	 */
	public function isLandscape(): bool
	{
		return $this->dimensions()->landscape();
	}

	/**
	 * Checks if the dimensions of the asset are square
	 */
	public function isSquare(): bool
	{
		return $this->dimensions()->square();
	}

	/**
	 * Checks if the file is a resizable image
	 */
	public function isResizable(): bool
	{
		// check extension first to avoid expensive dimension calculation
		if (in_array($this->extension(), static::$resizableTypes, true) === false) {
			return false;
		}

		$dimensions = $this->dimensions();

		// invalid or corrupted images return 0x0 dimensions
		if ($dimensions->width() === 0 || $dimensions->height() === 0) {
			return false;
		}

		return true;
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
	 * Returns the ratio of the asset
	 */
	public function ratio(): float
	{
		return $this->dimensions()->ratio();
	}

	/**
	 * Returns the orientation as string
	 * `landscape` | `portrait` | `square`
	 */
	public function orientation(): string|false
	{
		return $this->dimensions()->orientation();
	}

	/**
	 * Converts the object to an array
	 */
	public function toArray(): array
	{
		$array = [
			...parent::toArray(),
			'dimensions' => $this->dimensions()->toArray(),
			'exif'       => $this->exif()->toArray(),
		];

		ksort($array);

		return $array;
	}

	/**
	 * Returns the width of the asset
	 */
	public function width(): int
	{
		return $this->dimensions()->width();
	}
}
