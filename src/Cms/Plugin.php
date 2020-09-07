<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;

/**
 * Represents a Plugin and handles parsing of
 * the composer.json. It also creates the prefix
 * and media url for the plugin.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Plugin extends Model
{
    protected $extends;
    protected $info;
    protected $name;
    protected $root;

    /**
     * @param string $key
     * @param array|null $arguments
     * @return mixed|null
     */
    public function __call(string $key, array $arguments = null)
    {
        return $this->info()[$key] ?? null;
    }

    /**
     * Plugin constructor
     *
     * @param string $name
     * @param array $extends
     */
    public function __construct(string $name, array $extends = [])
    {
        $this->setName($name);
        $this->extends = $extends;
        $this->root    = $extends['root'] ?? dirname(debug_backtrace()[0]['file']);

        unset($this->extends['root']);
    }

    /**
     * @return array
     */
    public function extends(): array
    {
        return $this->extends;
    }

    /**
     * @return array
     */
    public function info(): array
    {
        if (is_array($this->info) === true) {
            return $this->info;
        }

        try {
            $info = Data::read($this->manifest());
        } catch (Exception $e) {
            // there is no manifest file or it is invalid
            $info = [];
        }

        return $this->info = $info;
    }

    /**
     * @return string
     */
    public function manifest(): string
    {
        return $this->root() . '/composer.json';
    }

    /**
     * @return string
     */
    public function mediaRoot(): string
    {
        return App::instance()->root('media') . '/plugins/' . $this->name();
    }

    /**
     * @return string
     */
    public function mediaUrl(): string
    {
        return App::instance()->url('media') . '/plugins/' . $this->name();
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function option(string $key)
    {
        return $this->kirby()->option($this->prefix() . '.' . $key);
    }

    /**
     * @return string
     */
    public function prefix(): string
    {
        return str_replace('/', '.', $this->name());
    }

    /**
     * @return string
     */
    public function root(): string
    {
        return $this->root;
    }

    /**
     * @param string $name
     * @return self
     * @throws \Kirby\Exception\InvalidArgumentException
     */
    protected function setName(string $name)
    {
        if (preg_match('!^[a-z0-9-]+\/[a-z0-9-]+$!i', $name) == false) {
            throw new InvalidArgumentException('The plugin name must follow the format "a-z0-9-/a-z0-9-"');
        }

        $this->name = $name;
        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->propertiesToArray();
    }
}
