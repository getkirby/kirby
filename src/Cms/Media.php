<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Image\Image;
use Kirby\Image\Darkroom;
use Kirby\FileSystem\Folder;
use Kirby\Util\Str;
use Kirby\Util\F;

class Media extends Component
{

    /**
     * Properties for the array export
     *
     * @var array
     */
    protected static $toArray = [
        'darkroom',
        'root',
        'url'
    ];

    /**
     * @var Darkroom
     */
    protected $darkroom;

    /**
     * @var string
     */
    protected $root;

    /**
     * @var string
     */
    protected $url;

    /**
     * Creates a new media manager instance
     *
     * @param array $props
     */
    public function __construct(array $props)
    {
        $this->setProperties($props);
    }

    /**
     * @param Component $model
     * @param Component $file
     * @param array $attributes
     * @return array
     */
    public function create(Component $model, Component $file, array $attributes = [])
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

    /**
     * Returns the Darkroom instance
     *
     * @return Darkroom
     */
    public function darkroom()
    {
        return $this->darkroom;
    }

    /**
     * @param Component $model
     * @param Component $file
     * @return boolean
     */
    public function delete(Component $model, Component $file = null): bool
    {
        return $this->unlink($this->root($model, $file));
    }

    /**
     * @param Component $file
     * @param array $attributes
     * @return string
     */
    public function filename(Component $file, array $attributes = []): string
    {
        if (empty($attributes) === true || $file->type() !== 'image') {
            return $file->filename();
        }

        $filename = new Filename($file->filename(), $attributes);

        return $filename->toString();
    }

    /**
     * @param string $source
     * @param string $link
     * @param string $method
     * @return boolean
     */
    public function link(string $source, string $link, string $method = 'link'): bool
    {
        return F::link($source, $link, $method);
    }

    /**
     * @param Component $model
     * @param Component $file
     * @param array $attributes
     * @return string
     */
    public function path(Component $model, Component $file = null, array $attributes = []): string
    {

        if (is_a($model, Page::class) === true) {
            $path = 'pages/' . $model->id();
        } elseif (is_a($model, Site::class) === true) {
            $path = 'site';
        } elseif (is_a($model, User::class) === true) {
            $path = 'users/' . $model->id();
        } else {
            throw new Exception('Invalid media model');
        }

        if ($file !== null) {
            $path .= '/' . $this->filename($file, $attributes);
        }

        return $path;
    }

    /**
     * @param App $app
     * @param string $type
     * @param string $path
     * @return void
     */
    public function resolve(App $app, string $type, string $path)
    {

        switch ($type) {
            case 'pages':

                $id    = dirname($path);
                $model = $app->site()->find($id);

                if ($model === null) {
                    $model = $app->site()->draft($id);
                }

                if ($model === null) {
                    throw new Exception('The page could not be found');
                }

                $file = $model->file(basename($path));
                break;
            case 'site':
                $model = $app->site();
                $file  = $model->file($path);
                break;
            case 'users':
                $model = $app->users()->findBy('id', dirname($path));
                $file  = $model->avatar();
                break;
        }

        return $this->create($model, $file);
    }

    /**
     * @param Component $model
     * @param Component $file
     * @param array $attributes
     * @return string
     */
    public function root(Component $model = null, Component $file = null, array $attributes = []): string
    {
        if ($model === null) {
            return $this->root;
        }

        return $this->root . '/' . $this->path($model, $file, $attributes);
    }

    /**
     * @param Darkroom $darkroom
     * @return self
     */
    protected function setDarkroom(Darkroom $darkroom): self
    {
        $this->darkroom = $darkroom;
        return $this;
    }

    /**
     * @param string $root
     * @return self
     */
    protected function setRoot(string $root): self
    {
        $this->root = $root;
        return $this;
    }

    /**
     * @param string $url
     * @return self
     */
    protected function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @param string $root
     * @return boolean
     */
    public function unlink(string $root): bool
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

    /**
     * @param Component $model
     * @param Component $file
     * @param array $attributes
     * @return string
     */
    public function url(Component $model = null, Component $file = null, array $attributes = []): string
    {
        if ($model === null) {
            return $this->url;
        }

        return $this->url . '/' . $this->path($model, $file, $attributes);
    }

}
