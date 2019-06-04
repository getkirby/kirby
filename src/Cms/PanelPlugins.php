<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;

/**
 * The PanelPlugins class takes care of collecting
 * js and css plugin files for the panel and caches
 * them in the media folder
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
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
            $this->files[] = $plugin->root() . '/index.css';
            $this->files[] = $plugin->root() . '/index.js';
        }

        return $this->files;
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
     * Read the files from all plugins and concatenate them
     *
     * @param string $type
     * @return string
     */
    public function read(string $type): string
    {
        $dist = [];

        foreach ($this->files() as $file) {
            if (F::extension($file) === $type) {
                if ($content = F::read($file)) {
                    $dist[] = $content;
                }
            }
        }

        return implode(PHP_EOL, $dist);
    }

    /**
     * Absolute url to the cache file
     * This is used by the panel to link the plugins
     *
     * @param string $type
     * @return string
     */
    public function url(string $type): string
    {
        return App::instance()->url('media') . '/plugins/index.' . $type . '?' . $this->modified();
    }
}
