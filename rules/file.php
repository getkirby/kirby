<?php

use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Image\Image as Upload;
use Kirby\Toolkit\V;

return [
    'file.create' => function (Page $page = null, Upload $upload, string $filename) {
        $this->rules()->check('file.valid.extension', pathinfo($filename, PATHINFO_EXTENSION));
        $this->rules()->check('file.valid.mime', $upload->mime());
        $this->rules()->check('file.valid.filename', $filename);
        $this->rules()->check('file.exists', $page, $filename);
    },
    'file.exists' => function (Page $page = null, string $filename, File $not = null) {

        $model     = $page === null ? $this->site() : $page;
        $duplicate = $model->files()->not($not)->find($filename);

        if ($duplicate) {
            throw new Exception('The file exists and cannot be overwritten');
        }

    },
    'file.rename' => function (File $file, string $name) {

        $page      = $file->page();
        $model     = $page === null ? $this->site() : $page;
        $duplicate = $model->files()->not($file)->findBy('name', $name);

        if ($duplicate) {
            throw new Exception('A file with this name exists');
        }

    },
    'file.replace' => function (File $file, Upload $upload) {

        $this->rules()->check('file.valid.extension', $file->extension());
        $this->rules()->check('file.valid.mime', $upload->mime());
        $this->rules()->check('file.valid.filename', $file->filename());

        if ($upload->mime() !== $file->mime()) {
            throw new Exception('The mime type of the new file does not match the old one');
        }

    },
    'file.valid.extension' => function (string $extension) {

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

    },
    'file.valid.filename' => function (string $filename) {

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

    },
    'file.valid.mime' => function (string $mime) {

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

    }
];
