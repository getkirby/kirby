<?php

namespace Kirby\Cms;

use Exception;
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
class Avatar extends Model
{

    use HasThumbs;

    /**
     * Properties that should be converted to array
     * in Avatar::toArray
     *
     * @var array
     */
    protected static $toArray = [
        'url',
        'exists'
    ];

    /**
     * The Image object, which
     * is being wrapped by this class.
     *
     * @var Image
     */
    protected $asset;

    /**
     * The public url for the avatar file
     *
     * @var string
     */
    protected $url;

    /**
     * The parent User object
     *
     * @var User
     */
    protected $user;

    /**
     * Magic caller to wrap around
     * Kirby Image methods like size, mime, etc.
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $method, array $arguments = [])
    {
        $asset = $this->asset();

        if (method_exists($asset, $method)) {
            return $asset->$method(...$arguments);
        }

        throw new Exception('Invalid avatar method: ' . $method);
    }

    /**
     * Creates a new Avatar object
     *
     * @param array $props
     */
    public function __construct(array $props)
    {
        $this->setRequiredProperties($props, ['user']);
        $this->setOptionalProperties($props, ['url', 'store', 'original']);
    }

    /**
     * Returns the defined Image object
     * or initializes a default.
     *
     * @return Image
     */
    public function asset(): Image
    {
        if (is_a($this->asset, Image::class)) {
            return $this->asset;
        }

        return $this->asset = $this->store()->asset();
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
    public function create(string $source): self
    {
        // temporary image object to inspect the source
        $source = new Image($source);

        $this->rules()->create($this, $source);

        return $this->store()->create($source);
    }

    /**
     * Deletes the avatar from the file system.
     * This is handled by the store.
     *
     * @return boolean
     */
    public function delete(): bool
    {
        $this->rules()->delete($this);

        return $this->store()->delete();
    }

    /**
     * Shortcut to the asset method
     * This is needed to make the toArray
     * method recognize this method at all.
     *
     * @return boolean
     */
    public function exists(): bool
    {
        return $this->store()->exists();
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
        // temporary image object to inspect the source
        $source = new Image($source);

        $this->rules()->replace($this, $source);

        return $this->store()->replace($source);
    }

    /**
     * Returns the AvatarRules class to
     * validate any important action.
     *
     * @return AvatarRules
     */
    protected function rules()
    {
        return new AvatarRules();
    }

    /**
     * Sets the public url for the avatar file
     *
     * @param string $url
     * @return self
     */
    protected function setUrl(string $url = null): self
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Sets the parent User object
     *
     * @param User $user
     * @return self
     */
    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
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
        return $this->store()->thumb($options);
    }

    /**
     * Returns the public url for the avatar file
     *
     * @return string
     */
    public function url(): string
    {
        return $this->url ?? $this->url = $this->asset()->url();
    }

    /**
     * Returns the parent User object
     *
     * @return User
     */
    public function user()
    {
        return $this->user;
    }

}
