<?php

namespace Kirby\Cms;

use Kirby\Exception\BadMethodCallException;
use Kirby\Image\Image;
use Kirby\Toolkit\F;

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
    use AvatarActions;
    use HasThumbs;

    /**
     * The Image object, which
     * is being wrapped by this class.
     *
     * @var Image
     */
    protected $asset;

    /**
     * @var string
     */
    protected $root;

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
     * Creates a new Avatar object
     * The only required property is the
     * parent User
     *
     * @param array $props
     */
    public function __construct(array $props)
    {
        $this->setProperties($props);
    }

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

        throw new BadMethodCallException([
            'data' => ['method' => 'Avatar::' . $method]
        ]);
    }

    /**
     * Improve var_dump() output
     *
     * @return array
     */
    public function __debuginfo(): array
    {
        return $this->toArray();
    }

    /**
     * Renders the HTML for the avatar
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->asset()->html();
    }

    /**
     * Returns the defined Image object
     * or initializes a default.
     *
     * @return Image
     */
    public function asset(): Image
    {
        return $this->asset = $this->asset ?? new Image($this->root());
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
        return is_file($this->root()) === true;
    }

    /**
     * Return the avatar filename
     *
     * @return string
     */
    public function filename(): string
    {
        return 'profile.jpg';
    }

    /**
     * @param  array  $attr
     * @return string
     */
    public function html(array $attr = []): string
    {
        return Html::img($this->url(), $attr);
    }

    public function id(): string
    {
        return $this->root();
    }

    /**
     * Avatars are resizable by default
     *
     * @return boolean
     */
    public function isResizable(): bool
    {
        return true;
    }

    /**
     * Create a unique media hash
     *
     * @return string
     */
    public function mediaHash(): string
    {
        return crc32($this->filename()) . '-' . $this->modified();
    }

    /**
     * Returns the absolute path to the file in the public media folder
     *
     * @return string
     */
    public function mediaRoot(): string
    {
        return $this->user()->mediaRoot() . '/' . $this->mediaHash() . '/' . $this->filename();
    }

    /**
     * Returns the absolute Url to the file in the public media folder
     *
     * @return string
     */
    public function mediaUrl(): string
    {
        return $this->user()->mediaUrl() . '/' . $this->mediaHash() . '/' . $this->filename();
    }

    /**
     * Get the file's last modification time.
     *
     * @param  string $format
     * @param  string|null $handler date or strftime
     * @return mixed
     */
    public function modified(string $format = null, string $handler = null)
    {
        return F::modified($this->root(), $format, $handler ?? $this->kirby()->option('date.handler', 'date'));
    }

    /**
     * Returns the parent model
     *
     * @return User
     */
    public function parent(): User
    {
        return $this->user();
    }

    /**
     * @return string
     */
    public function root(): ?string
    {
        return $this->user()->root() . '/' . $this->filename();
    }

    /**
     * Sets the root for the avatar file
     *
     * @param string $root
     * @return self
     */
    protected function setRoot(string $root = null): self
    {
        $this->root = $root;
        return $this;
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
     * Converts the most important asset
     * properties to an array
     *
     * @return array
     */
    public function toArray(): array
    {
        return array_merge($this->asset()->toArray(), [
            'url' => $this->url(),
        ]);
    }

    /**
     * Returns the public url for the avatar file
     *
     * @return string
     */
    public function url(): string
    {
        return $this->url = $this->url ?? $this->mediaUrl();
    }

    /**
     * Returns the parent User object
     *
     * @return User
     */
    public function user(): User
    {
        return $this->user;
    }
}
