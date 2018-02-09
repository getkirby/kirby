<?php

namespace Kirby\Cms;

use Kirby\Image\Image as Upload;
use Kirby\Toolkit\V;
use Kirby\Util\Str;

class FileRules
{

    public static function changeName(File $file, string $name): bool
    {
        $page      = $file->page();
        $model     = $page === null ? $this->site() : $page;
        $duplicate = $model->files()->not($file)->findBy('name', $name);

        if ($duplicate) {
            throw new Exception('A file with this name exists');
        }

        return true;
    }

    public static function create(File $file): bool
    {
        $this->rules()->validExtension($file->extension());
        $this->rules()->validMime($file->mime());
        $this->rules()->validFilename($file->filename());

        if ($file->exists() === true) {
            throw new Exception('The file exists and cannot be overwritten');
        }

        return true;
    }

    public static function delete(): bool
    {
        return true;
    }

    public static function replace(File $file, Upload $upload): bool
    {
        $this->rules()->validExtension($file, $upload->extension());
        $this->rules()->validMime($file, $upload->mime());
        $this->rules()->validFilename($file, $upload->filename());

        if ($upload->mime() !== $file->mime()) {
            throw new Exception('The mime type of the new file does not match the old one');
        }

        return true;
    }

    public static function update(File $file, array $content = []): bool
    {
        return true;
    }

    public static function validExtension(string $extension): bool
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

    public static function validFilename(string $filename)
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

    public static function validMime(string $mime)
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
