<?php

use Kirby\Cms\Content;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Data\Data;
use Kirby\FileSystem\File as Asset;
use Kirby\Image\Image;

return [
    'file.content' => function (File $file): Content {

        $content = Data::read($file->root() . '.txt');

        return new Content($content, $file);

    },
    'file.create' => function (Page $page = null, string $source, string $filename, array $content = []): File {

        if (is_file($source) === false) {
            throw new Exception('The file does not exist');
        }

        // create a temporary image object to run validations
        $this->rules()->check('file.create', new Image($source, '/tmp'), $filename);

        if ($page === null) {
            $file = new File([
                'id'   => $filename,
                'root' => $this->site()->root() . '/' . $filename,
                'url'  => $this->url('media') . '/' . $filename,
            ]);
        } else {
            $file = new File([
                'id'   => $page->id() . '/' . $filename,
                'root' => $page->root() . '/' . $filename,
                'url'  => $this->url('media') . '/' . $page->id() . '/' . $filename,
            ]);
        }

        // copy the original to the content folder
        copy($source, $file->root());

        // create a public version of the file
        $this->media()->create($page, $file);

        return $file->update($content);

    },
    'file.update' => function (File $file, array $content): File {

        $content = $file->content()->update($content);
        Data::write($file->root() . '.txt', $content->toArray());

        return $file->set('content', $content);

    },
    'file.replace' => function (File $file, string $source): File {

        if (file_exists($source) === false) {
            throw new Exception(sprintf('The source file "%s" does not exist', $source));
        }

        // create a temporary image object to run validations
        $this->rules()->check('file.replace', $file, new Image($source, '/tmp'));

        // delete all public versions
        $this->media()->delete($file->model(), $file);

        // overwrite the original
        copy($source, $file->root());

        // create a new public file
        $this->media()->create($file->model(), $file);

        return $file;

    },
    'file.rename' => function (File $file, string $name): File {

        $content    = $file->content()->toArray();
        $filename   = $name . '.' . $file->extension();
        $props      = [
            'id'   => $id = ltrim($file->model()->id() . '/' . $filename, '/'),
            'root' => $file->model()->root() . '/' . $filename,
            'url'  => $this->media()->url($file->model()) . '/' . $filename
        ];

        // remove the content file first
        if (file_exists($txt = $file->root() . '.txt')) {
            unlink($txt);
        }

        // remove all public versions
        $this->media()->delete($file->model(), $file);

        // rename the file
        rename($file->root(), $props['root']);

        // create a clean file object for it
        $file = $file->clone($props);

        // create a new public version
        $this->media()->create($file->model(), $file);

        // store the content in a fresh content file
        if (empty($content) === false) {
            $file->update($content);
        }

        return $file;

    },
    'file.delete' => function (File $file): bool {

        // delete the meta file first
        if (file_exists($txt = $file->root() . '.txt') === true) {
            unlink($txt);
        }

        // delete all public versions
        $this->media()->delete($file->model(), $file);

        // delete the real thing
        if (file_exists($file->root()) === true) {
            unlink($file->root()) !== false;
        }

        return true;

    },
];
