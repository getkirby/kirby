<?php

namespace Kirby\Cms;

use Darkroom\Options;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\Asset;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Http\Response;
use Kirby\Toolkit\Str;
use Throwable;

/**
 * Handles all tasks to get the Media API
 * up and running and link files correctly
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Media
{
	/**
	 * Tries to find a file by model and filename
	 * and to copy it to the media folder.
	 */
	public static function link(
		Page|Site|User|null $model,
		string $hash,
		string $filename
	): Response|false {
		if ($model === null) {
			return false;
		}

		// fix issues with spaces in filenames
		$filename = urldecode($filename);

		// try to find a file by model and filename
		// this should work for all original files
		if ($file = $model->file($filename)) {
			// check if the request contained an outdated media hash
			if ($file->mediaHash() !== $hash) {
				// if at least the token was correct, redirect
				if (Str::startsWith($hash, $file->mediaToken() . '-') === true) {
					return Response::redirect($file->mediaUrl(), 307);
				}

				// don't leak the correct token, render the error page
				return false;
			}

			// send the file to the browser
			return Response::file($file->publish()->root());
		}

		// try to generate a thumb for the file
		try {
			return static::thumb($model, $hash, $filename);
		} catch (NotFoundException) {
			// render the error page if there is no job for this filename
			return false;
		}
	}

	/**
	 * The original whose media folder `$hash` is,
	 * among the files the thumb's name may stem from
	 */
	protected static function original(
		File|Page|Site|User|string $model,
		string $hash,
		string $filename
	): File|Asset|null {
		if ($model instanceof File) {
			return $model->mediaHash() === $hash ? $model : null;
		}

		if (is_string($model) === true) {
			$index = App::instance()->root('index');
			$root  = Dir::realpath($index . '/' . $model, $index);
			$files = array_map(
				fn (string $name) => new Asset($model . '/' . $name),
				Dir::files($root)
			);
		} else {
			$files = $model->files();
		}

		foreach ($files as $file) {
			$prefix = F::safeBasename($file->filename()) . '-';

			if (
				str_starts_with($filename, $prefix) === true &&
				$file->mediaHash() === $hash
			) {
				return $file;
			}
		}

		return null;
	}

	/**
	 * Copy the file to the final media folder location
	 */
	public static function publish(File $file, string $dest): bool
	{
		// never publish risky files (e.g. HTML, PHP or Apache config files)
		$file->guards()->validators()->validateFile(mime: false);

		$src       = $file->root();
		$version   = dirname($dest);
		$directory = dirname($version);

		// unpublish all files except stuff in the version folder
		Media::unpublish($directory, $file, $version);

		// copy/overwrite the file to the dest folder
		return F::copy($src, $dest, true);
	}

	/**
	 * The thumb's name with a signature, so that the
	 * media route only makes the thumbs Kirby named
	 * @since 7.0.0
	 */
	public static function sign(File|Asset $original, string $name): string
	{
		$value = $original->mediaHash() . '/' . $name;
		$token = $original->kirby()->contentToken($original, $value);

		return F::name($name) . '-' . substr($token, 0, 10) . '.' . F::extension($name);
	}

	/**
	 * Makes the thumb that a signed name asks for:
	 * `{original}-{key}-{signature}.{extension}`
	 *
	 * @throws NotFoundException Without a valid name
	 */
	public static function thumb(
		File|Page|Site|User|string $model,
		string $hash,
		string $filename
	): Response|false {
		try {
			// a plain name, appended to the original's media folder
			if (basename($filename) !== $filename) {
				throw new NotFoundException();
			}

			$original = static::original($model, $hash, $filename);

			if (
				$original === null ||
				preg_match('/^(.+)-[0-9a-f]{10}(\.[a-z0-9]+)$/', $filename, $match) !== 1 ||
				hash_equals(static::sign($original, $match[1] . $match[2]), $filename) === false
			) {
				throw new NotFoundException();
			}

			$prefix  = F::safeBasename($original->filename()) . '-';
			$key     = substr($match[1], strlen($prefix));
			$options = Options::from($key)->toArray();
		} catch (Throwable) {
			throw new NotFoundException(
				message: 'The thumbnail could not be found'
			);
		}

		$thumb = $original->mediaDir() . '/' . $filename;

		try {
			$original->kirby()->thumb($original->root(), $thumb, $options);

			return Response::file($thumb);
		} catch (Throwable $e) {
			// remove potentially broken thumbnails
			F::remove($thumb);
			throw $e;
		}
	}

	/**
	 * Deletes all versions of the given file
	 * within the parent directory
	 */
	public static function unpublish(
		string $directory,
		File $file,
		string|null $ignore = null
	): bool {
		if (is_dir($directory) === false) {
			return true;
		}

		// get both old and new versions (pre and post Kirby 3.4.0)
		$versions = [
			...glob($directory . '/' . crc32($file->filename()) . '-*', GLOB_ONLYDIR) ?: [],
			...glob($directory . '/' . $file->mediaToken() . '-*', GLOB_ONLYDIR) ?: []
		];

		// delete all versions of the file
		foreach ($versions as $version) {
			if ($version === $ignore) {
				continue;
			}

			Dir::remove($version);
		}

		return true;
	}
}
