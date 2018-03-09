<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Util\Str;

trait SiteActions
{

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
            // TODO: refactor this to be independent from the page store
            'store'  => PageStore::class,
        ]);

        return Page::create($props);
    }

    public function createFile(array $props)
    {
        $props = array_merge($props, [
            'parent' => $this,
            // TODO: make this independent from the store
            'store'  => FileStore::class,
            'url'    => null
        ]);

        return File::create($props);
    }

}
