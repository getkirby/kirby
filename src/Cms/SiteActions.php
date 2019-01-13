<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Str;

trait SiteActions
{

    /**
     * Commits a site action, by following these steps
     *
     * 1. checks the action rules
     * 2. sends the before hook
     * 3. commits the store action
     * 4. sends the after hook
     * 5. returns the result
     *
     * @param string $action
     * @param mixed ...$arguments
     * @return mixed
     */
    protected function commit(string $action, array $arguments, Closure $callback)
    {
        $old   = $this->hardcopy();
        $kirby = $this->kirby();

        $this->rules()->$action(...$arguments);
        $kirby->trigger('site.' . $action . ':before', ...$arguments);
        $result = $callback(...$arguments);
        $kirby->trigger('site.' . $action . ':after', $result, $old);
        $kirby->cache('pages')->flush();
        return $result;
    }

    /**
     * Change the site title
     *
     * @param string $title
     * @param string|null $languageCode
     * @return self
     */
    public function changeTitle(string $title, string $languageCode = null): self
    {
        return $this->commit('changeTitle', [$this, $title, $languageCode], function ($site, $title, $languageCode) {
            return $site->save(['title' => $title], $languageCode);
        });
    }

    /**
     * Creates a main page
     *
     * @param array $props
     * @return Page
     */
    public function createChild(array $props)
    {
        $props = array_merge($props, [
            'url'    => null,
            'num'    => null,
            'parent' => null,
            'site'   => $this,
        ]);

        return Page::create($props);
    }

    /**
     * Clean internal caches
     */
    public function purge(): self
    {
        $this->children  = null;
        $this->blueprint = null;
        $this->files     = null;
        $this->content   = null;
        $this->inventory = null;

        return $this;
    }
}
