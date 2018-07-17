<?php

namespace Kirby\Cms;

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
    protected function commit(string $action, ...$arguments)
    {
        $old = $this->hardcopy();

        $this->rules()->$action($this, ...$arguments);
        $this->kirby()->trigger('site.' . $action . ':before', $this, ...$arguments);
        $result = $this->store()->$action(...$arguments);
        $this->kirby()->trigger('site.' . $action . ':after', $result, $old);
        $this->kirby()->cache('pages')->flush();
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

        return $this->commit('changeTitle', $title);
    }

    /**
     * Creates a main page
     *
     * @param array $props
     * @return self
     */
    public function createChild(array $props)
    {
        $props = array_merge($props, [
            'url'    => null,
            'num'    => null,
            'parent' => null,
            'site'   => $this,
            'store'  => $this->store()::PAGE_STORE_CLASS,
        ]);

        return Page::create($props);
    }

    public function createFile(array $props)
    {
        $props = array_merge($props, [
            'parent' => $this,
            'store'  => $this->store()::FILE_STORE_CLASS,
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

        // get the data values array
        $values  = $form->values();
        $strings = $form->strings();

        return $this->commit('update', $values, $strings);
    }

}
