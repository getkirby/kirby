<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Data\Data;
use Kirby\Filesystem\F;

class Loader
{
    protected $kirby;
    protected $plugins;

    public function __construct(App $kirby, bool $plugins = true)
    {
        $this->kirby   = $kirby;
        $this->plugins = $plugins;
    }

    public function area(string $name): ?array
    {
        return $this->areas()[$name] ?? null;
    }

    public function areas(): array
    {
        $areas      = [];
        $extensions = $this->plugins === true ? $this->kirby->extensions('areas') : [];

        // load core areas and extend them with elements from plugins if they exist
        foreach ($this->kirby->core()->areas() as $id => $area) {
            $area = $this->resolve($area);

            if (isset($extensions[$id]) === true) {
                $extension = $this->resolve($extensions[$id]);
                $area      = array_replace_recursive($area, $extension);
                unset($extensions[$id]);
            }

            $areas[$id] = $area;
        }

        // add additional areas from plugins
        foreach ($extensions as $id => $area) {
            $areas[$id] = $this->resolve($area);
        }

        return $areas;
    }

    public function component(string $name): ?Closure
    {
        return $this->extension('components', $name);
    }

    public function components()
    {
        return $this->extensions('components');
    }

    public function extension(string $type, string $name)
    {
        return $this->extensions($type)[$name] ?? null;
    }

    public function extensions(string $type)
    {
        return $this->plugins === false ? $this->kirby->core()->$type() : $this->kirby->extensions($type);
    }

    public function resolve($item)
    {
        if (is_string($item) === true) {
            if (F::extension($item) !== 'php') {
                $item = Data::read($item);
            } else {
                $item = require $item;
            }
        }

        if (is_callable($item)) {
            $item = $item($this->kirby);
        }

        return $item;
    }

    public function resolveAll(array $items)
    {
        $result = [];

        foreach ($items as $key => $value) {
            $result[$key] = $this->resolve($value);
        }

        return $result;
    }

    public function section(string $name): ?array
    {
        return $this->resolve($this->extension('sections', $name));
    }

    public function sections(): array
    {
        return $this->resolveAll($this->extensions('sections'));
    }
}
