<?php

declare(strict_types = 1);

namespace Darkroom;

use Closure;
use Darkroom\Exception\Exception;
use Darkroom\Exception\LimitException;
use Darkroom\Exception\UnsupportedException;
use Darkroom\Formats\Writer;
use InvalidArgumentException;

final class Darkroom
{
	public const array DRIVERS = ['imagick', 'gd', 'vips'];

	public readonly Defaults $defaults;
	public readonly Settings $settings;

	/**
	 * @var array<Driver>
	 */
	protected array $instances;

	/**
	 * @var array<string, true>|null
	 */
	protected array|null $temps = null;

	/**
	 * @param array<string|class-string<Driver>|Driver> $drivers
	 */
	public function __construct(
		public readonly array $drivers = self::DRIVERS,
		Defaults|array $defaults = [],
		Settings|array $settings = [],
	) {
		$this->defaults  = Defaults::from($defaults);
		$this->settings  = Settings::from($settings);
		$this->instances = array_map($this->instance(...), $drivers);
	}

	/**
	 * @throws UnsupportedException Without a readable size
	 * @throws LimitException Above the pixel limit
	 */
	protected function check(Source $source): void
	{
		$limit  = $this->settings->limit ?? PHP_INT_MAX;
		$pixels = $source->size()->pixels();

		if ($pixels === 0) {
			throw new UnsupportedException(
				message: 'Image without a readable size: ' . $source->file
			);
		}

		if ($pixels > $limit) {
			throw new LimitException(
				message: 'Image above the limit of ' . $limit . ' pixels: ' . $source->file
			);
		}
	}

	protected function clean(): void
	{
		foreach (array_keys($this->temps ?? []) as $temp) {
			@unlink($temp);
		}
	}

	/**
	 * First available driver that
	 * reads `$input` and writes `$output`
	 */
	public function driver(
		Format|Source|string $input,
		Format|string $output
	): Driver|null {
		$input  = $input instanceof Source ? $input : Format::from($input);
		$output = Format::from($output);

		foreach ($this->instances as $driver) {
			if (
				$driver->isAvailable() === true &&
				$driver->canRead($input) === true &&
				$driver->canWrite($output) === true
			) {
				return $driver;
			}
		}

		return null;
	}

	/**
	 * The output format, for callers without a file name yet:
	 * the first a driver writes of `$format`,
	 * the source's if allowed, the allowed ones
	 *
	 * @param array<Format|string> $allowed
	 *
	 * @throws InvalidArgumentException For an unknown format
	 * @throws UnsupportedException Without a driver for any of them
	 */
	public function format(
		Source $source,
		Format|string|null $format = null,
		array $allowed = Format::VIEWABLE
	): Format {
		$input   = $source->format();
		$allowed = array_map(Format::from(...), $allowed);
		$formats = in_array($input, $allowed, true) === true ? [$input, ...$allowed] : $allowed;

		if ($format !== null) {
			$formats = [Format::from($format), ...$formats];
		}

		foreach ($formats as $candidate) {
			if ($this->driver($source, $candidate) !== null) {
				return $candidate;
			}
		}

		throw new UnsupportedException(
			message: 'No driver converts to an allowed format: ' . $source->file
		);
	}

	public function info(): array
	{
		$info = [];

		foreach ($this->instances as $key => $driver) {
			$available = $driver->isAvailable();
			$read      = [];
			$write     = [];

			// a library that isn't available can't be probed
			foreach ($available === true ? Format::cases() : [] as $format) {
				if ($driver->canRead($format) === true) {
					$read[] = $format->extension();
				}

				if ($driver->canWrite($format) === true) {
					$write[] = $format->extension();
				}
			}

			$info[$key] = [
				'class'     => $driver::class,
				'available' => $available,
				...$driver->info(),
				'formats'   => [
					'read'  => $read,
					'write' => $write,
				],
			];
		}

		return $info;
	}

	/**
	 * @throws InvalidArgumentException For an unknown driver
	 */
	protected function instance(Driver|string $driver): Driver
	{
		if ($driver instanceof Driver) {
			return $driver;
		}

		$class = __NAMESPACE__ . '\\Drivers\\' . ucfirst($driver);

		if (class_exists($class) === false) {
			$class = $driver;
		}

		if (is_subclass_of($class, Driver::class) === false) {
			throw new InvalidArgumentException(
				message: 'Unknown driver: ' . $driver
			);
		}

		return new $class($this->settings->toArray());
	}

	/**
	 * Writes the EXIF tags the plan keeps of its source and the
	 * driver's profile into the thumb, where its format has a place
	 */
	protected function meta(
		Plan $plan,
		string $file,
		string|null $icc
	): void {
		$kept = Meta::kept($plan->strip);
		$exif = $plan->source->keep($kept);

		if ($exif === '' && $icc === null) {
			return;
		}

		$thumb = @file_get_contents($file);

		if ($thumb === false) {
			throw new Exception(
				message: 'Can\'t read the thumb: ' . $file
			);
		}

		$data = Writer::embed($thumb, $plan->format, $exif, $icc);

		file_put_contents($file, $data);
	}

	public function open(string $file): Source
	{
		return new Source($file);
	}

	/**
	 * In the format of the options or defaults if a
	 * driver writes it, else as `::format()` falls back;
	 * frames and orientation are left to `::process()`
	 *
	 * @throws UnsupportedException For an unreadable source or without a driver
	 * @throws LimitException Above the pixel limit
	 */
	public function plan(
		Source|string $source,
		Options|array $options = []
	): Plan {
		if (is_string($source) === true) {
			$source = $this->open($source);
		}

		$this->check($source);

		$options = Options::from($options);
		$format  = $this->format($source, $options->format ?? $this->defaults->format);
		$options = Options::from([...$options->toArray(), 'format' => $format]);

		return Plan::for($source, $options, $this->defaults);
	}

	/**
	 * Writes the thumb atomically,
	 * in the format of its `$dst`'s extension
	 *
	 * @throws UnsupportedException For an unreadable source or without a driver
	 * @throws LimitException Above the pixel limit
	 * @throws InvalidArgumentException For invalid options or format, or a plan with options
	 */
	public function process(
		Plan|Source|string $src,
		string $dst,
		Options|array $options = []
	): Plan {
		if ($src instanceof Plan) {
			if ($options !== []) {
				throw new InvalidArgumentException(
					message: 'Either a plan or options, not both'
				);
			}

			$options = $src->options;
			$src     = $src->source;
		}

		$source = $src instanceof Source ? $src : $this->open($src);
		$input  = $source->format();

		$this->check($source);

		$options = Options::from($options);
		$output  = Format::from(pathinfo($dst, PATHINFO_EXTENSION));

		if (
			$options->format !== null &&
			$options->format !== $output
		) {
			throw new InvalidArgumentException(
				message: 'Format ' . $options->format->extension() . ' doesn\'t match: ' . $dst
			);
		}

		$driver = $this->driver($source, $output);

		if ($driver === null) {
			throw new UnsupportedException(
				message: 'No driver converts ' . $input->extension() . ' to ' . $output->extension()
			);
		}

		$options = Options::from([...$options->toArray(), 'format' => $output]);
		$plan    = Plan::for($source, $options, $this->defaults, $driver);

		$this->write($dst, function (string $temp) use ($driver, $plan): void {
			$driver->process($plan, $temp);

			$icc = $driver->icc($plan);

			$this->meta($plan, $temp, $icc);
		});

		return $plan;
	}

	/**
	 * Writes the image from the handle into the file
	 * through the writer
	 *
	 * @param class-string<Writer> $writer
	 * @param resource $in
	 */
	protected function rewrite(
		string $writer,
		$in,
		string $file,
		string $exif
	): void {
		$out = @fopen($file, 'wb');

		if ($out === false) {
			throw new Exception(
				message: 'Can\'t write the image: ' . $file
			);
		}

		try {
			$writer::write($in, $out, $exif);
		} finally {
			fclose($out);
		}
	}

	/**
	 * Copies the image atomically without decoding it and removes its
	 * EXIF tags based on `$strip`, else the defaults; it always keeps
	 * the color profile and orientation
	 *
	 * @param bool|list<Meta|string>|null $strip
	 *
	 * @throws UnsupportedException For an unreadable source
	 * @throws InvalidArgumentException For an invalid strip
	 */
	public function strip(
		Source|string $src,
		string $dst,
		bool|array|null $strip = null
	): void {
		$source      = $src instanceof Source ? $src : $this->open($src);
		$format      = $source->format();
		$writer      = Writer::for($format);
		$options     = Defaults::from(['strip' => $strip]);
		$kept        = Meta::kept($options->strip ?? $this->defaults->strip ?? Defaults::STRIP);
		$orientation = $writer::ORIENTS === true ? $source->orientation() : Orientation::None;
		$exif        = $source->keep($kept, $orientation);

		$this->write($dst, function (string $temp) use ($source, $writer, $exif): void {
			$in = @fopen($source->file, 'rb');

			if ($in === false) {
				throw new Exception(
					message: 'Can\'t read the image: ' . $source->file
				);
			}

			try {
				$this->rewrite($writer, $in, $temp, $exif);
			} finally {
				fclose($in);
			}
		});
	}

	public function tryPlan(
		Source|string $source,
		Options|array $options = []
	): Plan|null {
		try {
			return $this->plan($source, $options);
		} catch (LimitException|UnsupportedException) {
			return null;
		}
	}

	/**
	 * Like `::strip()`, but `false` for an unreadable source
	 *
	 * @param bool|list<Meta|string>|null $strip
	 *
	 * @throws InvalidArgumentException For an invalid strip
	 */
	public function tryStrip(
		Source|string $src,
		string $dst,
		bool|array|null $strip = null
	): bool {
		try {
			$this->strip($src, $dst, $strip);
			return true;
		} catch (UnsupportedException) {
			return false;
		}
	}

	/**
	 * Writes the file through `$write`, into a
	 * temporary file renamed once complete
	 *
	 * @param Closure(string): void $write
	 */
	protected function write(string $file, Closure $write): void
	{
		$dir  = dirname($file);
		$temp = $dir . '/.' . basename($file) . '.tmp-' . bin2hex(random_bytes(6));

		// another process may create it at the same time
		if (
			is_dir($dir) === false &&
			@mkdir($dir, 0777, true) === false &&
			is_dir($dir) === false
		) {
			throw new Exception(
				message: 'Can\'t create the directory: ' . $dir
			);
		}

		// once per instance, as shutdown functions can't be removed
		if ($this->temps === null) {
			register_shutdown_function($this->clean(...));
		}

		$this->temps[$temp] = true;

		try {
			$write($temp);

			if (@rename($temp, $file) === false) {
				throw new Exception(
					message: 'Can\'t move the image to: ' . $file
				);
			}
		} finally {
			unset($this->temps[$temp]);

			if (is_file($temp) === true) {
				unlink($temp);
			}
		}
	}
}
