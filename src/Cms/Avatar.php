<?php

namespace Kirby\Cms;

use Kirby\Image\Image;

/**
 * The Avatar class handles user images.
 * User images are normally stored in
 * /media/users/...
 *
 * For image methods and manipulation,
 * this class wraps around the Kirby Image class.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 */
class Avatar extends Object
{

    use HasThumbs;

    /**
     * Magic caller to wrap around
     * Kirby Image methods like size, mime, etc.
     * But also allows access to props like
     * url, root and user.
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $method, array $arguments = [])
    {
        $asset = $this->props->asset;

        if (method_exists($asset, $method)) {
            return $asset->$method(...$arguments);
        }

        return $this->props->get($method);
    }

    /**
     * Takes the required props and creates
     * a new clean avatar object with those,
     * that can serve as a fresh clone.
     *
     * @param array $props
     * @return self
     */
    public function clone(array $props = []): self
    {
        return new static(array_merge([
            'root' => $this->props->root,
            'url'  => $this->props->url,
            'user' => $this->props->user,
        ], $props));
    }

    /**
     * Creates the avatar on upload.
     * The file system handling is done
     * by the store.
     *
     * @param User $user
     * @param string $source
     * @return self
     */
    public static function create(User $user, string $source): self
    {
        return static::store()->commit('avatar.create', $user, $source);
    }

    /**
     * Deletes the avatar from the file system.
     * This is handled by the store.
     *
     * @return boolean
     */
    public function delete(): bool
    {
        return $this->plugin('store')->commit('avatar.delete', $this);
    }

    /**
     * Replaces the avatar file with a new one.
     * This is handled by the store.
     *
     * @param string $source
     * @return self
     */
    public function replace(string $source): self
    {
        return $this->plugin('store')->commit('avatar.replace', $this, $source);
    }

    /**
     * Property schema
     *
     * @return array
     */
    protected function schema()
    {
        return [
            'asset' => [
                'type'    => Image::class,
                'freeze'  => true,
                'default' => function () {
                    return new Image($this->props->root, $this->props->url);
                }
            ],
            'root' => [
                'type'     => 'string',
                'freeze'   => true,
                'required' => true
            ],
            'url' => [
                'type'     => 'string',
                'freeze'   => true,
                'required' => true
            ],
            'user' => [
                'type'     => User::class,
                'freeze'   => true,
                'required' => true
            ]
        ];
    }

    /**
     * Main thumb generation method.
     * This is also reused by crop and resize
     * methods in the HasThumbs trait.
     *
     * @param array $options
     * @return self
     */
    public function thumb(array $options = []): self
    {
        return $this->plugin('media')->create($this->user(), $this, $options);
    }

}
