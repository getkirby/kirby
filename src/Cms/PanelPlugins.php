<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;

/**
 * The PanelPlugins class takes care of collecting
 * js and css plugin files for the panel and caches
 * them in the media folder
 */
class PanelPlugins
{

    /**
     * Cache of all collected plugin files
     *
     * @var array
     */
    public $files;

    /**
     * Cache of the unique plugin hash for the url and root
     *
     * @var string
     */
    public $hash;

    /**
     * css or js
     *
     * @var string
     */
    public $type;

    /**
     * Creates a new panel plugin instance by type (css or js)
     *
     * @param string $type
     */
    public function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * Collects and returns the plugin files for all plugins
     *
     * @return array
     */
    public function files(): array
    {
        if ($this->files !== null) {
            return $this->files;
        }

        $this->files = [];

        foreach (App::instance()->plugins() as $plugin) {
            $file = $plugin->root() . '/index.' . $this->type;

            if (file_exists($file) === true) {
                $this->files[] = $file;
            }
        }

        return $this->files;
    }

    /**
     * Checks if the cache exists
     *
     * @return boolean
     */
    public function exist(): bool
    {
        return file_exists($this->root());
    }

    /**
     * Returns the path to the cache folder
     *
     * @return string
     */
    public function folder(): string
    {
        return 'panel/' . App::versionHash() . '/plugins/' . $this->type;
    }

    /**
     * Collects and removes garbage from old plugin versions
     *
     * @return boolean
     */
    public function gc(): bool
    {
        $folder = App::instance()->root('media') . '/' . $this->folder();

        foreach (glob($folder . '/*') as $dir) {
            $name = basename($dir);

            if ($name !== $this->hash()) {
                Dir::remove($dir);
            }
        }

        return true;
    }

    /**
     * Returns the unique hash for the cache file
     * The hash is generated from all plugin filenames
     * and the max modification date to make sure changes
     * will always be cached properly
     *
     * @return string
     */
    public function hash(): string
    {
        if ($this->hash !== null) {
            return $this->hash;
        }

        return $this->hash = $this->id() . '-' . $this->modified();
    }

    /**
     * Returns a unique id based on all
     * plugin file roots
     *
     * @return string
     */
    public function id(): string
    {
        return crc32(implode(array_values($this->files())));
    }

    /**
     * Returns the last modification
     * of the collected plugin files
     *
     * @return int
     */
    public function modified(): int
    {
        $files    = $this->files();
        $modified = [0];

        foreach ($files as $file) {
            $modified[] = F::modified($file);
        }

        return max($modified);
    }

    /**
     * Returns the full path to the cache file
     * This is used for the root and url methods
     *
     * @return string
     */
    public function path(): string
    {
        return $this->folder() . '/' . $this->hash() . '/index.' . $this->type;
    }

    /**
     * Read the files from all plugins and concatenate them
     *
     * @return string
     */
    public function read(): string
    {
        $dist = [];

        foreach ($this->files() as $file) {
            $dist[] = file_get_contents($file);
        }

        return implode(PHP_EOL, $dist);
    }

    /**
     * Checks if the cache exists and
     * otherwise (re)creates it
     *
     * @return boolean
     */
    public function publish(): bool
    {
        if ($this->exist() === true) {
            return true;
        }

        $this->write();
        $this->gc();

        return true;
    }

    /**
     * Absolute path to the cache file
     *
     * @return string
     */
    public function root(): string
    {
        return App::instance()->root('media') . '/' . $this->path();
    }

    /**
     * Absolute url to the cache file
     * This is used by the panel to link the plugins
     *
     * @return string
     */
    public function url(): string
    {
        return App::instance()->url('media') . '/' . $this->path();
    }

    /**
     * Creates the cache file
     *
     * @return boolean
     */
    public function write(): bool
    {
        return F::write($this->root(), $this->read());
    }
}
