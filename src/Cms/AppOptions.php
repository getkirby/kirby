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

        $this->optionsFromEnv();

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
        // TODO: implement host detection
        $host = 'localhost';
        $root = $this->root('config');

        $main = (array)F::load($root . '/config.php', []);
        $host = (array)F::load($root . '/config.' . $host . '.php', []);

        return array_replace_recursive($main, $host);
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

    /**
     * Load the env file
     * This is not injected into the options
     * namespace, but availbale seperatedly
     * via env() or $_ENV
     *
     * @return boolean
     */
    protected function optionsFromEnv(): bool
    {
        $root = $this->root('env');

        if (file_exists($root . '/.env') !== true) {
            return false;
        }

        $dotenv = new Dotenv($root);
        $dotenv->load();

        return true;
    }

}
