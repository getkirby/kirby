<?php

namespace Kirby\Cms;

use Closure;
use Exception;

use Kirby\Cms\Page\Store;
use Kirby\Cms\Page\Traits\Assets;
use Kirby\Cms\Page\Traits\Content;
use Kirby\Cms\Page\Traits\Mutator;
use Kirby\Cms\Page\Traits\Navigator;
use Kirby\Cms\Page\Traits\Relatives;
use Kirby\Cms\Page\Traits\State;
use Kirby\FileSystem\Folder;
use Kirby\Toolkit\Str;

class Page
{

    use Assets;
    use Content;
    use Mutator;
    use Navigator;
    use Relatives;
    use State;

    protected $attributes;
    protected $id;
    protected $store;
    protected $url;

    public function __construct(array $attributes)
    {
        // store all passed attributes
        $this->attributes = $attributes;

        // initialize the store class that handles all administrative tasks
        $this->store = new Store($this, $attributes);

        // validations
        if (!isset($attributes['id'])) {
            throw new Exception('Please provide an ID for the page');
        }

        if (!isset($attributes['url'])) {
            throw new Exception('Please provide an URL for the page');
        }

        // required attributes
        $this->id  = (string)$attributes['id'];
        $this->url = (string)$attributes['url'];

        // optional attributes
        $this->num = $attributes['num'] ?? null;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function num()
    {
        return $this->num;
    }

    public function slug(): string
    {
        return basename($this->url);
    }

    public function template()
    {
        return $this->attributes['template'] ?? $this->store->template();
    }

    public function is($page): bool
    {
        return $this->id() === $page->id();
    }

    public function exists(): bool
    {
        return $this->store()->exists();
    }

    public function __call($method, $arguments)
    {
        if (isset($this->attributes[$method]) === true) {
            return $this->attributes[$method];
        }

        return $this->content()->get($method, $arguments);
    }

}
