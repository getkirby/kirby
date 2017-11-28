<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Image\Image;

class File extends Object
{

    use HasSiblings;
    use HasThumbs;

    public function __construct(array $props = [])
    {

        parent::__construct($props, [
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
            'url' => [
                'required' => true,
                'type'     => 'string'
            ],
            'original' => [
                'type' => File::class,
            ]
        ]);

    }

    public function clone(array $props = []): self
    {
        return new static(array_merge([
            'id'     => $this->id(),
            'root'   => $this->root(),
            'url'    => $this->url(),
            'page'   => $this->page()
        ], $props));
    }

    public static function create(Page $parent = null, string $source, string $filename, array $content = []): self
    {
        return static::store()->commit('file.create', $parent, $source, $filename, $content);
    }

    public function delete(): bool
    {
        $this->rules()->check('file.delete', $this);
        $this->perms()->check('file.delete', $this);

        return $this->store()->commit('file.delete', $this);
    }

    public function meta()
    {
        return $this->content();
    }

    public function model()
    {
        return is_a($this->page(), Page::class) ? $this->page() : $this->site();
    }

    public function rename(string $name): self
    {
        $this->rules()->check('file.rename', $this, $name);
        $this->perms()->check('file.rename', $this, $name);

        return $this->store()->commit('file.rename', $this, $name);
    }

    public function replace(string $source): self
    {
        return $this->store()->commit('file.replace', $this, $source);
    }

    public function update(array $content = []): self
    {
        $this->rules()->check('file.update', $this, $content);
        $this->perms()->check('file.update', $this, $content);

        return $this->store()->commit('file.update', $this, $content);
    }

    public function site()
    {
        return $this->plugin('site');
    }

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

    public function __call($method, $arguments)
    {
        if ($this->hasPlugin($method)) {
            return $this->plugin($method, $arguments);
        }

        if ($this->hasProp($method)) {
            return $this->prop($method, $arguments);
        }

        if (method_exists($this->asset(), $method)) {
            return $this->asset()->$method(...$arguments);
        }

        return $this->content()->get($method, $arguments);
    }


}
