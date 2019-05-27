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

    public function __call(string $key, array $arguments = null)
    {
        return $this->info()[$key] ?? null;
    }

    public function __construct(string $name, array $extends = [])
    {
        $this->setName($name);
        $this->extends = $extends;
        $this->root    = $extends['root'] ?? dirname(debug_backtrace()[0]['file']);

        unset($this->extends['root']);
    }

    public function extends(): array
    {
        return $this->extends;
    }

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

    public function manifest(): string
    {
        return $this->root() . '/composer.json';
    }

    public function mediaRoot(): string
    {
        return App::instance()->root('media') . '/plugins/' . $this->name();
    }

    public function mediaUrl(): string
    {
        return App::instance()->url('media') . '/plugins/' . $this->name();
    }

    public function name(): string
    {
        return $this->name;
    }

    public function option(string $key)
    {
        return $this->kirby()->option($this->prefix() . '.' . $key);
    }

    public function prefix(): string
    {
        return str_replace('/', '.', $this->name());
    }

    public function root(): string
    {
        return $this->root;
    }

    /**
     * @param string $name
     * @return self
     */
    protected function setName(string $name)
    {
        if (preg_match('!^[a-z0-9-]+\/[a-z0-9-]+$!i', $name) == false) {
            throw new InvalidArgumentException('The plugin name must follow the format "a-z0-9-/a-z0-9-"');
        }

        $this->name = $name;
        return $this;
    }

    public function toArray(): array
    {
        return $this->propertiesToArray();
    }
}
