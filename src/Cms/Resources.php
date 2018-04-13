<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Form\Field;

class Resources extends Collection
{

    /**
     * Only accepts Resource objects
     *
     * @var string
     */
    protected static $accept = Resource::class;

    public static function forField(string $className, Plugin $plugin = null): self
    {
        $result = new static;

        // only fields within a plugin can have resources/assets
        if ($plugin === null) {
            return $result;
        }

        foreach ($className::assets() as $asset) {
            $resource = Resource::forPlugin($plugin, $asset);
            $result->append($resource->id(), $resource);
        }

        return $result;
    }

    public static function forFields(): self
    {
        $result = new static;

        foreach (App::instance()->extensions('fields') as $type => $field) {
            foreach (static::forField($field['class'], $field['plugin']) as $resource) {
                $result->append($resource->id(), $resource);
            }
        }

        return $result;
    }

    public static function forPage(Page $page)
    {
        $result = new static;

        foreach ($page->files() as $file) {
            $resource = Resource::forFile($file);
            $result->append($resource->id(), $resource);
        }

        return $result;
    }

    public static function forPlugins()
    {
        return static::forFields();
    }

    public static function forSite(Site $site = null)
    {
        $site   = $site ?? App::instance()->site();
        $result = new static;

        foreach ($site->files() as $file) {
            $resource = Resource::forFile($file);
            $result->append($resource->id(), $resource);
        }

        return $result;
    }

    public function link(): bool
    {
        foreach ($this as $resource) {
            $resource->link();
        }

        return true;
    }

    public function type(string $type): self
    {
        return $this->filterBy('type', $type);
    }

    public function unlink(): bool
    {
        foreach ($this as $resource) {
            $resource->unlink();
        }

        return true;
    }

}
