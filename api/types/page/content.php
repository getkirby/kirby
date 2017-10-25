<?php

use Kirby\Cms\Blueprint;
use Kirby\Cms\Schema;

return function ($page) {

    $blueprint = new Blueprint($this->app()->root('blueprints'), $page->template());
    $schema    = new Schema($page, $blueprint->toArray(), $this->app()->schema());
    return $schema->read($page->content()->toArray());

};
