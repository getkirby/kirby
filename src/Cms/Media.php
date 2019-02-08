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
 */
class Media
{

    /**
     * Tries to find a job file for the
     * given filename and then calls the thumb
     * component to create a thumbnail accordingly
     *
     * @param Model $model
     * @param string $hash
     * @param string $filename
     * @return Response|false
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

            $kirby->thumb($source, $thumb, $options);

            F::remove($job);

            return Response::file($thumb);
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Tries to find a file by model and filename
     * and to copy it to the media folder.
     *
     * @param Model $model
     * @param string $hash
     * @param string $filename
     * @return Response|false
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

            // the media hash is outdated. redirect to the correct url
            if ($file->mediaHash() !== $hash) {
                return Response::redirect($file->mediaUrl(), 307);
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
     * @param string $src
     * @param string $dest
     * @return boolean
     */
    public static function publish(string $src, string $dest): bool
    {
        $filename  = basename($src);
        $version   = dirname($dest);
        $directory = dirname($version);

        // unpublish all files except stuff in the version folder
        Media::unpublish($directory, $filename, $version);

        // copy/overwrite the file to the dest folder
        return F::copy($src, $dest, true);
    }

    /**
     * Deletes all versions of the given filename
     * within the parent directory
     *
     * @param string $directory
     * @param string $filename
     * @param string $ignore
     * @return bool
     */
    public static function unpublish(string $directory, string $filename, string $ignore = null): bool
    {
        if (is_dir($directory) === false) {
            return true;
        }

        $versions = glob($directory . '/' . crc32($filename) . '*', GLOB_ONLYDIR);

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
