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
     * @return self
     */
    public function changeTitle(string $title): self
    {
        if ($title === $this->title()->value()) {
            return $this;
        }

        return $this->commit('changeTitle', [$this, $title], function ($site, $title) {
            $content = $site
                ->content()
                ->update(['title' => $title])
                ->toArray();

            return $site->clone(['content' => $content])->save();
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
     * Creates a site file
     *
     * @param array $props
     * @return File
     */
    public function createFile(array $props)
    {
        $props = array_merge($props, [
            'parent' => $this,
            'url'    => null
        ]);

        return File::create($props);
    }

    /**
     * Clean internal caches
     */
    public function purge(): self
    {
        $this->children  = null;
        $this->blueprint = null;

        return $this;
    }

    /**
     * Delete the text file without language code
     * before storing the actual file
     *
     * @param string|null $languageCode
     * @return self
     */
    public function save(string $languageCode = null)
    {
        if ($this->kirby()->multilang() === true) {
            F::remove($this->contentFile());
        }

        return parent::save($languageCode);
    }
}
