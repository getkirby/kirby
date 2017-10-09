<?php

namespace Kirby\Cms;

use Exception;

use Kirby\Cms\Site\Store;
use Kirby\Cms\Site\Traits\Assets;
use Kirby\Cms\Site\Traits\Content;
use Kirby\Cms\Site\Traits\Mutator;
use Kirby\Cms\Site\Traits\Relatives;

class Site
{

    use Assets;
    use Content;
    use Relatives;
    use Mutator;

    protected $attributes;
    protected $store;
    protected $url;

    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
        $this->store      = new Store($this, $attributes);

        if (empty($attributes['url'])) {
            throw new Exception('Please provide a URL for the site');
        }

        // required
        $this->url = $attributes['url'];
    }

    public function url(): string
    {
        return $this->url;
    }

    public function __call($method, $arguments)
    {

        if (isset($this->attributes[$method]) === true) {
            return $this->attributes[$method];
        }

        return $this->content()->get($method, $arguments);

    }

}
