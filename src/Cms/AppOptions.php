<?php

namespace Kirby\Cms;

use Dotenv\Dotenv;
use Kirby\Toolkit\F;

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
        return $this->options[$key] ?? $default;
    }

    /**
     * Returns all configuration options
     *
     * @return array
     */
    public function options(): array
    {
        return $this->options;
    }

    /**
     * Inject options from Kirby instance props
     *
     * @return array
     */
    protected function optionsFromProps(array $options = [])
    {
        return $this->options = array_replace_recursive($this->options, $options);
    }

    /**
     * Load all options from files in site/config
     *
     * @return array
     */
    protected function optionsFromSystem(): array
    {
        $server = $this->server();
        $root   = $this->root('config');

        $main = (array)F::load($root . '/config.php', []);
        $host = (array)F::load($root . '/config.' . basename($server->host()) . '.php', []);
        $addr = (array)F::load($root . '/config.' . basename($server->address()) . '.php', []);

        return $this->options = array_replace_recursive($main, $host, $addr);
    }
}
