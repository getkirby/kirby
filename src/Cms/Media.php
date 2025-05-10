<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Toolkit\Str;
use Throwable;

/**
 * Handles all tasks to get the Media API
 * up and running and link files correctly
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Media
{
	/**
	 * Tries to find a file by model and filename
	 * and to copy it to the media folder.
	 *
	 * @todo v6 Make `$subhash` argument accept only strings
	 */
	public static function link(
		Page|Site|User|null $model,
		string $hash,
		string|null $subhash,
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
			// support old URLs without the new subhash
			// TODO: Remove in v6
			if ($subhash === null) {
				return Response::redirect($file->mediaUrl(), 307);
			}

			// check if the subhash was correct
			// (otherwise the request cannot be trusted)
			if ($file->mediaToken($file->filename()) !== $subhash) {
				return false;
			}

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
			return Response::file($file->publish()->mediaPath());
		}

		// try to generate a thumb for the file
		try {
			return static::thumb($model, $hash, $subhash, $filename);
		} catch (NotFoundException) {
			// render the error page if there is no job for this filename
			return false;
		}
	}

	/**
	 * Copy the file to the final media folder location
	 */
	public static function publish(File $file, string $dest): bool
	{
		// never publish risky files (e.g. HTML, PHP or Apache config files)
		FileRules::validFile($file, false);

		$src       = $file->root();
		$version   = dirname($dest, 2);
		$directory = dirname($version);

		// unpublish all files except stuff in the version folder
		Media::unpublish($directory, $file, $version);

		// check if we can migrate files from their pre-5.1 location
		// to avoid having to regenerate all thumbs
		foreach (Dir::read($version, absolute: true) as $item) {
			if (is_file($item) === true) {
				$filename = basename($item);
				F::move($item, $file->mediaPath($filename));
			}
		}

		// copy/overwrite the file to the dest folder
		return F::copy($src, $dest, true);
	}

	/**
	 * Tries to find a job file for the
	 * given filename and then calls the thumb
	 * component to create a thumbnail accordingly
	 */
	public static function thumb(
		File|Page|Site|User|string $model,
		string $hash,
		string|null $subhash,
		string $filename
	): Response|false {
		$kirby = App::instance();

		$root = match (true) {
			// assets
			is_string($model)
				=> $kirby->root('media') . '/assets/' . $model . '/' . $hash,
			// parent files for file model that already included hash
			$model instanceof File
				=> $model->mediaRoot(),
			// model files
			default
			=> $model->mediaRoot() . '/' . $hash
		};

		$job = $root . '/.jobs/' . $filename . '.json';

		try {
			$options = Data::read($job);
		} catch (Throwable) {
			// send a customized error message to make clearer what happened here
			throw new NotFoundException(
				message: 'The thumbnail configuration could not be found'
			);
		}

		if (empty($options['filename']) === true) {
			throw new InvalidArgumentException(
				message: 'Incomplete thumbnail configuration'
			);
		}

		// find the correct source file depending on the model
		// this adds support for custom assets
		$source = match (true) {
			is_string($model) === true
				=> $kirby->root('index') . '/' . $model . '/' . $options['filename'],
			$model instanceof File
				=> $model,
			default
			=> $model->file($options['filename']) ?? throw new NotFoundException('Could not find referenced source file')
		};

		// default to a simple target path for custom assets…
		$thumb = $root . '/' . $filename;

		// …but file objects from a real model need special handling
		if ($source instanceof File) {
			// support old URLs without the new subhash
			// TODO: Remove in v6
			if ($subhash === null) {
				return Response::redirect($source->mediaUrl($filename), 307);
			}

			// validate the user-provided subhash
			if ($subhash !== $source->mediaToken($filename)) {
				throw new NotFoundException(
					message: 'The provided subhash for the thumbnail configuration is invalid'
				);
			}

			// check if we can migrate the thumb from its pre-5.1 location
			$thumb = $source->mediaPath($filename);
			if (is_file($source->mediaRoot() . '/' . $filename) === true) {
				$source->publish();
				return Response::file($thumb);
			}

			// continue with the file path
			$source = $source->root();
		}

		try {
			// generate the thumbnail and save it in the media folder
			$kirby->thumb($source, $thumb, $options);

			// remove the job file once the thumbnail has been created
			F::remove($job);

			// read the file and send it to the browser
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
			...glob($directory . '/' . crc32($file->filename()) . '-*', GLOB_ONLYDIR),
			...glob($directory . '/' . $file->mediaToken() . '-*', GLOB_ONLYDIR)
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
