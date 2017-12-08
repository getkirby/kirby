<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Image\Image;
use Kirby\Image\Darkroom;
use Kirby\Util\Str;

class Media extends Object
{

    public function __construct(array $props)
    {
        parent::__construct($props, [
            'darkroom' => [
                'required' => true,
                'type'     => Darkroom::class,
            ],
            'root' => [
                'required' => true,
                'type'     => 'string',
            ],
            'url' => [
                'required' => true,
                'type'     => 'string',
            ],
        ]);
    }

    public function path(Object $model, Object $file = null, array $attributes = []): string
    {
        switch (get_class($model)) {
            case Page::class:
                $path = 'pages/' . $model->id();
                break;
            case Site::class:
                $path = 'site';
                break;
            case User::class:
                $path = 'users/' . $model->hash();
                break;
            default:
                throw new Exception('Invalid media model');
        }

        if ($file !== null) {
            $path .= '/' . $this->filename($file, $attributes);
        }

        return $path;
    }

    public function url(Object $model, Object $file = null, array $attributes = []): string
    {
        if ($model === null) {
            return $this->prop('url');
        }

        return $this->prop('url') . '/' . $this->path($model, $file, $attributes);
    }

    public function root(Object $model, Object $file = null, array $attributes = []): string
    {
        if ($model === null) {
            return $this->prop('root');
        }

        return $this->prop('root') . '/' . $this->path($model, $file, $attributes);
    }

    public function link($source, $link, $method = 'link'): bool
    {
        $folder = new Folder(dirname($link));
        $folder->make(true);

        if (is_file($link) === true) {
            return true;
        }

        if (is_file($source) === false) {
            throw new Exception(sprintf('The file "%s" does not exist and cannot be linked', $source));
        }

        return $method($source, $link);
    }

    public function unlink($root): bool
    {

        if (is_dir($root) === true) {
            $folder = new Folder($root);
            $folder->delete();
            return true;
        }

        $dirname   = dirname($root);
        $name      = pathinfo($root, PATHINFO_FILENAME);
        $extension = pathinfo($root, PATHINFO_EXTENSION);

        // remove the original file
        if (is_file($root) === true) {
            unlink($root);
        }

        // remove all thumbnails
        foreach (glob($dirname . '/' . $name . '-*.' . $extension) as $file) {
            unlink($file);
        }

        return true;

    }

    public function filename(Object $file, array $attributes = [])
    {

        if (empty($attributes) === true || $file->type() !== 'image') {
            return $file->filename();
        }

        $options = [
            'crop' => [
                'default'   => 'center',
                'key'       => 'crop',
                'separator' => '-'
            ],
            'blur' => [
                'default' => false,
                'key'     => 'blur',
            ],
            'quality' => [
                'default' => 100,
                'key'     => 'q',
            ],
            'grayscale' => [
                'default' => false,
                'key' => 'bw'
            ]
        ];

        $chain = [];

        foreach ($attributes as $key => $value) {
            if ($value !== false && $value !== null && isset($options[$key]) === true && $options[$key]['default'] !== $value) {
                if ($value === true) {
                    $chain[] = $options[$key]['key'];
                } else {
                    $chain[] = $options[$key]['key'] . ($options[$key]['separator'] ?? '') . Str::slug($value);
                }
            }
        }

        sort($chain);

        // add the dimensions to the chain
        array_unshift($chain, ($attributes['width'] ?? '') . 'x' . ($attributes['height'] ?? '' ));

        return $file->name() . '-' . implode('-', $chain) . '.' . $file->extension();

    }

    public function create(Object $model, Object $file, array $attributes = [])
    {

        if ($file->exists() === false) {
            throw new Exception('The source file does not exist');
        }

        // thumb creation
        if (empty($attributes) === false) {

            if ($file->original() !== null) {
                throw new Exception('Resized images cannot be further processed');
            }

            $attributes = $this->darkroom()->preprocess($file->root(), $attributes);
            $root       = $this->root($model, $file, $attributes);

            // check if the thumbnail has to be regenerated
            if (file_exists($root) !== true || filemtime($root) < $file->modified()) {
                $this->link($file->root(), $root, 'copy');
                $this->darkroom()->process($root, $attributes);
            }

        } else {

            $root = $this->root($model, $file);
            $this->link($file->root(), $root);

        }

        return $file->clone([
            'root'     => $root,
            'url'      => $this->url($model, $file, $attributes),
            'original' => $file
        ]);

    }

    public function delete(Object $model, Object $file = null): bool
    {
        return $this->unlink($this->root($model, $file));
    }

    public function resolve(App $app, string $type, string $path)
    {

        switch ($type) {
            case 'pages':
                $model = $app->site()->find(dirname($path));
                $file  = $model->file(basename($path));
                break;
            case 'site':
                $model = $app->site();
                $file  = $model->file($path);
                break;
            case 'users':
                $model = $app->users()->findBy('hash', dirname($path));
                $file  = $model->avatar();
                break;
        }

        return $app->media()->create($model, $file);

    }

}
