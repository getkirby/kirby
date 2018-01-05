<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Image\Image;

/**
 * The File class is a wrapper around
 * the Kirby\Image\Image class, which
 * is used to handle all file methods.
 * In addition the File class handles
 * File meta data via Kirby\Cms\Content.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 */
class File extends Object
{

    use HasSiblings;
    use HasThumbs;

    /**
     * Property schema
     * Required props are `id`, `url` and `root`
     *
     * @return array
     */
    protected function schema()
    {
        return [
            'asset' => [
                'type' => Image::class,
                'default' => function () {
                    return new Image($this->root(), $this->url());
                }
            ],
            'collection' => [
                'type'    => Files::class,
                'default' => function () {
                    if ($page = $this->page()) {
                        return $this->page()->files();
                    }
                }
            ],
            'content' => [
                'type'    => Content::class,
                'default' => function (): Content {
                    return $this->store()->commit('file.content', $this);
                }
            ],
            'id' => [
                'required' => true,
                'type'     => 'string',
            ],
            'page' => [
                'type' => Page::class,
            ],
            'root' => [
                'required' => true,
                'type'     => 'string',
            ],
            'store' => [
                'type'    => Store::class,
                'default' => function () {
                    return $this->plugin('store');
                }
            ],
            'url' => [
                'required' => true,
                'type'     => 'string'
            ],
            'original' => [
                'type' => File::class,
            ]
        ];
    }

    /**
     * Clones a File object and throws out
     * all stuff that is too specific to provide
     * a clean slate. You can pass additional
     * props that will be merged with the existing
     * props with the `$props` argument.
     *
     * @param array $props
     * @return self
     */
    public function clone(array $props = []): self
    {
        return new static(array_merge([
            'id'     => $this->id(),
            'root'   => $this->root(),
            'url'    => $this->url(),
            'page'   => $this->page()
        ], $props));
    }

    /**
     * Creates a new file on disk and returns the
     * File object. The store is used to handle file
     * writing, so it can be replaced by any other
     * way of generating files.
     *
     * @param Page $parent
     * @param string $source
     * @param string $filename
     * @param array $content
     * @return self
     */
    public static function create(Page $parent = null, string $source, string $filename, array $content = []): self
    {
        return static::store()->commit('file.create', $parent, $source, $filename, $content);
    }

    /**
     * Deletes the file. The store is used to
     * manipulate the filesystem or whatever you prefer.
     *
     * @return bool
     */
    public function delete(): bool
    {
        $this->rules()->check('file.delete', $this);
        $this->perms()->check('file.delete', $this);

        return $this->store()->commit('file.delete', $this);
    }

    /**
     * Alias for the old way of fetching File
     * content. Nowadays `File::content()` should
     * be used instead.
     *
     * @return Content
     */
    public function meta()
    {
        return $this->content();
    }

    /**
     * Returns the parent model.
     * This is normally the parent page
     * or the site object.
     *
     * @return Site|Page
     */
    public function model()
    {
        return is_a($this->page(), Page::class) ? $this->page() : $this->site();
    }

    /**
     * Renames the file without touching the extension
     * The store is used to actually execute this.
     *
     * @param string $name
     * @return self
     */
    public function rename(string $name): self
    {
        $this->rules()->check('file.rename', $this, $name);
        $this->perms()->check('file.rename', $this, $name);

        return $this->store()->commit('file.rename', $this, $name);
    }

    /**
     * Replaces the file. The source must
     * be an absolute path to a file or a Url.
     * The store handles the replacement so it
     * finally decides what it will support as
     * source.
     *
     * @param string $source
     * @return self
     */
    public function replace(string $source): self
    {
        return $this->store()->commit('file.replace', $this, $source);
    }

    /**
     * Updates the file content.
     * The store writes content into text
     * files or any other place you determin in
     * custom store implementations.
     *
     * @param array $content
     * @return self
     */
    public function update(array $content = []): self
    {
        $this->rules()->check('file.update', $this, $content);
        $this->perms()->check('file.update', $this, $content);

        return $this->store()->commit('file.update', $this, $content);
    }

    /**
     * Returns the parent Site object if available
     *
     * @return Site|null
     */
    public function site()
    {
        return $this->plugin('site');
    }

    /**
     * Creates a modified version of images
     * The media manager takes care of generating
     * those modified versions and putting them
     * in the right place. This is normally the
     * /media folder of your installation, but
     * could potentially also be a CDN or any other
     * place.
     *
     * @param array $options
     * @return self
     */
    public function thumb(array $options = []): self
    {
        try {
            if ($this->page() === null) {
                return $this->plugin('media')->create($this->site(), $this, $options);
            }

            return $this->plugin('media')->create($this->page(), $this, $options);
        } catch (Exception $e) {
            return $this;
        }
    }

    /**
     * Magic caller for props, plugins, file methods
     * and content fields. (in this order)
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $method, array $arguments = [])
    {
        if ($this->props->has($method, true)) {
            return $this->props->get($method, $arguments);
        }

        if ($this->hasPlugin($method)) {
            return $this->plugin($method, $arguments);
        }

        if (method_exists($this->asset(), $method)) {
            return $this->asset()->$method(...$arguments);
        }

        return $this->content()->get($method, $arguments);
    }

}
