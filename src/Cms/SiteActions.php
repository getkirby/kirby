<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Util\Str;

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

}
