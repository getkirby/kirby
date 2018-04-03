<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Toolkit\V;
use Kirby\Util\Str;

class FileRules
{

    public static function changeName(File $file, string $name): bool
    {
        if ($file->permissions()->changeName() !== true) {
            throw new Exception('The name for this file cannot be changed');
        }

        $parent    = $file->parent();
        $duplicate = $parent->files()->not($file)->findBy('name', $name);

        if ($duplicate) {
            throw new Exception('A file with this name exists');
        }

        return true;
    }

    public static function changeSort(File $file, int $sort): bool
    {
        return true;
    }

    public static function create(File $file, Upload $upload): bool
    {
        if ($file->exists() === true) {
            throw new Exception('The file exists and cannot be overwritten');
        }

        if ($file->permissions()->create() !== true) {
            throw new Exception('The file cannot be created');
        }

        static::validExtension($file, $file->extension());
        static::validMime($file, $upload->mime());
        static::validFilename($file, $file->filename());

        $upload->match($file->blueprint()->accept());

        return true;
    }

    public static function delete(File $file): bool
    {
        if ($file->permissions()->delete() !== true) {
            throw new Exception('The file cannot be deleted');
        }

        return true;
    }

    public static function replace(File $file, Upload $upload): bool
    {
        if ($file->permissions()->replace() !== true) {
            throw new Exception('The file cannot be replaced');
        }

        static::validExtension($file, $upload->extension());
        static::validMime($file, $upload->mime());
        static::validFilename($file, $upload->filename());

        if ($upload->mime() != $file->mime()) {
            throw new Exception(sprintf('The mime type of the new file (%s) does not match the old one (%s)', $upload->mime(), $file->mime()));
        }

        $upload->match($file->blueprint()->accept());

        return true;
    }

    public static function update(File $file, array $content = []): bool
    {
        if ($file->permissions()->update() !== true) {
            throw new Exception('The file cannot be updated');
        }

        return true;
    }

    public static function validExtension(File $file, string $extension): bool
    {

        // make it easier to compare the extension
        $extension = strtolower($extension);

        if (empty($extension)) {
            throw new Exception('The extension is missing');
        }

        if (V::in($extension, ['php', 'html', 'htm', 'exe'])) {
            throw new Exception(sprintf('Unallowed extension "%s"', $extension));
        }

        if(Str::contains($extension, 'php')) {
            throw new Exception('You are not allowed to upload PHP files');
        }

        return true;

    }

    public static function validFilename(File $file, string $filename)
    {

        // make it easier to compare the filename
        $filename = strtolower($filename);

        // check for missing filenames
        if (empty($filename)) {
            throw new Exception('The filename must not be empty');
        }

        // Block htaccess files
        if(Str::startsWith($filename, '.ht')) {
            throw new Exception('You are not allowed to upload Apache config files');
        }

        // Block invisible files
        if(Str::startsWith($filename, '.')) {
            throw new Exception('You are not allowed to upload invisible files');
        }

        return true;

    }

    public static function validMime(File $file, string $mime = null)
    {

        // make it easier to compare the mime
        $mime = strtolower($mime);

        if (empty($mime)) {
            throw new Exception('The mime type cannot be detected');
        }

        if (Str::contains($mime, 'php')) {
            throw new Exception('You are not allowed to upload PHP files');
        }

        if (V::in($mime, ['text/html', 'application/x-msdownload'])) {
            throw new Exception(sprintf('Unallowed mime type "%s"', $mime));
        }

        return true;

    }

}
