<?php

namespace Kirby\Cms;

use Dotenv\Dotenv;
use Kirby\Util\F;

trait AppOptions
{
    protected $options;

    /**
     * Load a specific configuration option
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function option(string $key, $default = null)
    {
        return $this->options()[$key] ?? $default;
    }

    /**
     * Returns all configuration options
     *
     * @return array
     */
    public function options(): array
    {
        if (is_array($this->options) === true) {
            return $this->options;
        }

        $fromExtensions = $this->optionsFromExtensions();
        $fromFiles      = $this->optionsFromFiles();

        return $this->options = array_replace_recursive($fromExtensions, $fromFiles);
    }

    /**
     * Load all options from files in site/config
     *
     * @return array
     */
    protected function optionsFromFiles(): array
    {
        $server = $this->server();
        $root   = $this->root('config');

        $main = (array)F::load($root . '/config.php', []);
        $host = (array)F::load($root . '/config.' . basename($server->host()) . '.php', []);
        $addr = (array)F::load($root . '/config.' . basename($server->address()) . '.php', []);

        return array_replace_recursive($main, $host, $addr);
    }

    /**
     * Load all options from plugins
     *
     * @return array
     */
    protected function optionsFromExtensions(): array
    {
        return $this->extensions('options');
    }

}
