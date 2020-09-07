<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Str;
use Throwable;

/**
 * Handles all tasks to get the Media API
 * up and running and link files correctly
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Media
{
    /**
     * Tries to find a file by model and filename
     * and to copy it to the media folder.
     *
     * @param \Kirby\Cms\Model|null $model
     * @param string $hash
     * @param string $filename
     * @return \Kirby\Cms\Response|false
     */
    public static function link(Model $model = null, string $hash, string $filename)
    {
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
                } else {
                    // don't leak the correct token
                    return new Response('Not Found', 'text/plain', 404);
                }
            }

            // send the file to the browser
            return Response::file($file->publish()->mediaRoot());
        }

        // try to generate a thumb for the file
        return static::thumb($model, $hash, $filename);
    }

    /**
     * Copy the file to the final media folder location
     *
     * @param \Kirby\Cms\File $file
     * @param string $dest
     * @return bool
     */
    public static function publish(File $file, string $dest): bool
    {
        $src       = $file->root();
        $version   = dirname($dest);
        $directory = dirname($version);

        // unpublish all files except stuff in the version folder
        Media::unpublish($directory, $file, $version);

        // copy/overwrite the file to the dest folder
        return F::copy($src, $dest, true);
    }

    /**
     * Tries to find a job file for the
     * given filename and then calls the thumb
     * component to create a thumbnail accordingly
     *
     * @param \Kirby\Cms\Model $model
     * @param string $hash
     * @param string $filename
     * @return \Kirby\Cms\Response|false
     */
    public static function thumb($model, string $hash, string $filename)
    {
        $kirby = App::instance();

        if (is_string($model) === true) {
            // assets
            $root = $kirby->root('media') . '/assets/' . $model . '/' . $hash;
        } else {
            // model files
            $root = $model->mediaRoot() . '/' . $hash;
        }

        try {
            $thumb   = $root . '/' . $filename;
            $job     = $root . '/.jobs/' . $filename . '.json';
            $options = Data::read($job);

            if (empty($options) === true) {
                return false;
            }

            if (is_string($model) === true) {
                $source = $kirby->root('index') . '/' . $model . '/' . $options['filename'];
            } else {
                $source = $model->file($options['filename'])->root();
            }

            try {
                $kirby->thumb($source, $thumb, $options);
                F::remove($job);
                return Response::file($thumb);
            } catch (Throwable $e) {
                F::remove($thumb);
                return Response::file($source);
            }
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Deletes all versions of the given file
     * within the parent directory
     *
     * @param string $directory
     * @param \Kirby\Cms\File $file
     * @param string|null $ignore
     * @return bool
     */
    public static function unpublish(string $directory, File $file, string $ignore = null): bool
    {
        if (is_dir($directory) === false) {
            return true;
        }

        // get both old and new versions (pre and post Kirby 3.4.0)
        $versions = array_merge(
            glob($directory . '/' . crc32($file->filename()) . '-*', GLOB_ONLYDIR),
            glob($directory . '/' . $file->mediaToken() . '-*', GLOB_ONLYDIR)
        );

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
