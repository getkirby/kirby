<?php

namespace Kirby\Cms;

use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\PermissionException;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;

/**
 * Validators for all file actions
 */
class FileRules
{
    public static function changeName(File $file, string $name): bool
    {
        if ($file->permissions()->changeName() !== true) {
            throw new PermissionException([
                'key'  => 'file.changeName.permissions',
                'data' => ['filename' => $file->filename()]
            ]);
        }

        $parent    = $file->parent();
        $duplicate = $parent->files()->not($file)->findBy('name', $name);

        if ($duplicate) {
            throw new DuplicateException([
                'key'  => 'file.duplicate',
                'data' => ['filename' => $duplicate->filename()]
            ]);
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
            throw new LogicException('The file exists and cannot be overwritten');
        }

        if ($file->permissions()->create() !== true) {
            throw new PermissionException('The file cannot be created');
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
            throw new LogicException('The file cannot be deleted');
        }

        return true;
    }

    public static function replace(File $file, Upload $upload): bool
    {
        if ($file->permissions()->replace() !== true) {
            throw new LogicException('The file cannot be replaced');
        }

        static::validMime($file, $upload->mime());


        if ((string)$upload->mime() !== (string)$file->mime()) {
            throw new InvalidArgumentException([
                'key'  => 'file.mime.differs',
                'data' => ['mime' => $file->mime()]
            ]);
        }

        $upload->match($file->blueprint()->accept());

        return true;
    }

    public static function update(File $file, array $content = []): bool
    {
        if ($file->permissions()->update() !== true) {
            throw new LogicException('The file cannot be updated');
        }

        return true;
    }

    public static function validExtension(File $file, string $extension): bool
    {
        // make it easier to compare the extension
        $extension = strtolower($extension);

        if (empty($extension)) {
            throw new InvalidArgumentException([
                'key'  => 'file.extension.missing',
                'data' => ['filename' => $file->filename()]
            ]);
        }

        if (V::in($extension, ['php', 'html', 'htm', 'exe'])) {
            throw new InvalidArgumentException([
                'key'  => 'file.invalid.extension.forbidden',
                'data' => ['extension' => $extension]
            ]);
        }

        if (Str::contains($extension, 'php')) {
            throw new InvalidArgumentException([
                'key'  => 'file.forbidden',
                'data' => ['type' => 'PHP']
            ]);
        }

        return true;
    }

    public static function validFilename(File $file, string $filename)
    {

        // make it easier to compare the filename
        $filename = strtolower($filename);

        // check for missing filenames
        if (empty($filename)) {
            throw new InvalidArgumentException([
                'key'  => 'file.name.missing'
            ]);
        }

        // Block htaccess files
        if (Str::startsWith($filename, '.ht')) {
            throw new InvalidArgumentException([
                'key'  => 'file.forbidden',
                'data' => ['type' => 'Apache config']
            ]);
        }

        // Block invisible files
        if (Str::startsWith($filename, '.')) {
            throw new InvalidArgumentException([
                'key'  => 'file.forbidden',
                'data' => ['type' => 'invisible']
            ]);
        }

        return true;
    }

    public static function validMime(File $file, string $mime = null)
    {
        // make it easier to compare the mime
        $mime = strtolower($mime);

        if (empty($mime)) {
            throw new InvalidArgumentException([
                'key'  => 'file.mime.missing',
                'data' => ['filename' => $file->filename()]
            ]);
        }

        if (Str::contains($mime, 'php')) {
            throw new InvalidArgumentException([
                'key'  => 'file.forbidden',
                'data' => ['type' => 'PHP']
            ]);
        }

        if (V::in($mime, ['text/html', 'application/x-msdownload'])) {
            throw new InvalidArgumentException([
                'key'  => 'file.mime.forbidden',
                'data' => ['mime' => $mime]
            ]);
        }

        return true;
    }
}
