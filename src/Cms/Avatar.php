<?php

namespace Kirby\Cms;

use Kirby\Image\Image;

class Avatar extends Object
{

    use HasThumbs;

    public function __construct(array $props)
    {

        parent::__construct($props, [
            'url' => [
                'type'     => 'string',
                'required' => true
            ],
            'asset' => [
                'type' => Image::class,
                'default' => function () {
                    return new Image($this->prop('root'), $this->prop('url'));
                }
            ],
            'root' => [
                'type'     => 'string',
                'required' => true
            ],
            'user' => [
                'type'     => User::class,
                'required' => true
            ]
        ]);

    }

    public function clone(array $props = []): self
    {
        return new static(array_merge([
            'root'   => $this->root(),
            'url'    => $this->url(),
            'user'   => $this->user()
        ], $props));
    }

    public static function create(User $user, string $source): self
    {
        return static::store()->commit('avatar.create', $user, $source);
    }

    public function delete(): bool
    {
        return $this->plugin('store')->commit('avatar.delete', $this);
    }

    public function replace(string $source): self
    {
        return $this->plugin('store')->commit('avatar.replace', $this, $source);
    }

    public function thumb(array $options = []): self
    {
        return $this->plugin('media')->create($this->user(), $this, $options);
    }

    public function __call($method, $arguments)
    {
        if (method_exists($this->prop('asset'), $method)) {
            return $this->prop('asset')->$method(...$arguments);
        }

        return $this->prop($method, $arguments);
    }

}
