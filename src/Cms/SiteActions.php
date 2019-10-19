<?php

namespace Kirby\Cms;

use Closure;

/**
 * SiteActions
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
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
     * @param Closure $callback
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
    public function changeTitle(string $title, string $languageCode = null)
    {
        return $this->commit('changeTitle', [$this, $title, $languageCode], function ($site, $title, $languageCode) {
            return $site->save(['title' => $title], $languageCode);
        });
    }

    /**
     * Creates a main page
     *
     * @param array $props
     * @return \Kirby\Cms\Page
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
     *
     * @return self
     */
    public function purge()
    {
        $this->blueprint    = null;
        $this->children     = null;
        $this->content      = null;
        $this->files        = null;
        $this->inventory    = null;
        $this->translations = null;

        return $this;
    }
}
