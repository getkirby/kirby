<?php

namespace Kirby\Cms;

use Closure;
use Exception;
use Kirby\Form\Field;

class Resources extends Collection
{

    /**
     * Only accepts Resource objects
     *
     * @var string
     */
    protected static $accept = Resource::class;

    public static function forPluginClass(string $className, $clone = null): self
    {
        if (method_exists($className, 'assets') === false) {
            throw new Exception(sprintf('The class "%s" has no assets definition', $className));
        }

        $result = new static;
        $assets = $className::assets();
        $kirby  = App::instance();

        foreach (['js', 'css', 'img'] as $type) {
            $files = $assets[$type] ?? [];

            if (is_string($files) === true) {
                $files = [$files];
            }

            foreach ($files as $file) {
                $fileParts  = explode('/', $file);
                $filePath   = implode('/', array_slice($fileParts, 2));
                $pluginName = implode('/', array_slice($fileParts, 0, 2));

                $resource = Resource::forPlugin($kirby->plugin($pluginName), $filePath, [
                    'type' => $type
                ]);

                $result->append($resource->id(), $resource);
            }
        }

        return $result;
    }

    public static function forField(string $className): self
    {
        return static::forPluginClass($className, function ($resource) {
            return [
                'path'      => 'plugins/fields/' . $resource->path(),
                'timestamp' => true
            ];
        });
    }

    public static function forFields(): self
    {
        $result = new static;

        foreach (App::instance()->extensions('fields') as $type => $className) {
            foreach (static::forField($className) as $resource) {
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
