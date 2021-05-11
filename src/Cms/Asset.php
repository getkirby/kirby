<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Properties;

/**
 * Anything in your public path can be converted
 * to an Asset object to use the same handy file
 * methods and thumbnail generation as for any other
 * Kirby files. Pass a relative path to the Asset
 * object to create the asset.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Asset
{
    use FileFoundation;
    use FileModifications;
    use Properties;

    /**
     * @var string
     */
    protected $path;

    /**
     * Creates a new Asset object
     * for the given path.
     *
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->setPath(dirname($path));
        $this->setRoot($this->kirby()->root('index') . '/' . $path);
        $this->setUrl($this->kirby()->url('index') . '/' . $path);
    }

    /**
     * Returns the alternative text for the asset
     *
     * @return null
     */
    public function alt()
    {
        return null;
    }

    /**
     * Returns a unique id for the asset
     *
     * @return string
     */
    public function id(): string
    {
        return $this->root();
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
     * Returns the relative path starting at the media folder
     *
     * @return string
     */
    public function mediaPath(): string
    {
        return 'assets/' . $this->path() . '/' . $this->mediaHash() . '/' . $this->filename();
    }

    /**
     * Returns the absolute path to the file in the public media folder
     *
     * @return string
     */
    public function mediaRoot(): string
    {
        return $this->kirby()->root('media') . '/' . $this->mediaPath();
    }

    /**
     * Returns the absolute Url to the file in the public media folder
     *
     * @return string
     */
    public function mediaUrl(): string
    {
        return $this->kirby()->url('media') . '/' . $this->mediaPath();
    }

    /**
     * Returns the path of the file from the web root,
     * excluding the filename
     *
     * @return string
     */
    public function path(): string
    {
        return $this->path;
    }

    /**
     * Setter for the path
     *
     * @param string $path
     * @return $this
     */
    protected function setPath(string $path)
    {
        $this->path = $path === '.' ? '' : $path;
        return $this;
    }
}
