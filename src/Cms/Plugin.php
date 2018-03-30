<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Data\Data;

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

    public function __construct(array $props = [])
    {
        $this->setProperties($props);
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

    public function resource(string $path)
    {
        return Resource::forPlugin($this, $path);
    }

    public function root(): string
    {
        return $this->root;
    }

    protected function setExtends(array $extends = null)
    {
        $this->extends = $extends;
        return $this;
    }

    protected function setName(string $name)
    {
        if (preg_match('!^[a-z-]+\/[a-z-]+$!', $name) == false) {
            throw new Exception('The plugin name must follow the format "abc/def"');
        }

        $this->name = $name;
        return $this;
    }

    protected function setRoot(string $root)
    {
        $this->root = $root;
        return $this;
    }

    public function toArray(): array
    {
        return $this->propertiesToArray();
    }

}
