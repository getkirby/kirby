<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentLogicException;
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
     * Stores the file meta content on disk
     *
     * @return self
     */
    public function save(): self
    {
        if ($this->exists() === false) {
            return $this;
        }

        Data::write($this->contentFile(), $this->content()->toArray());
        return $this;
    }

    /**
     * Updates the model data
     *
     * @param array $input
     * @param boolean $validate
     * @return self
     */
    public function update(array $input = null, bool $validate = true): self
    {
        $form = Form::for($this, [
            'values' => $input
        ]);

        // validate the input
        if ($validate === true) {
            if ($form->isInvalid() === true) {
                throw new InvalidArgumentException([
                    'fallback' => 'Invalid form with errors',
                    'details'  => $form->errors()
                ]);
            }
        }

        return $this->commit('update', [$this, $form->values(), $form->strings()], function ($site, $values, $strings) {
            $content = $site
                ->content()
                ->update($strings)
                ->toArray();

            return $site->clone(['content' => $content])->save();
        });
    }
}
