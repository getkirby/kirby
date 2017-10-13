<?php

use Kirby\Cms\Blueprint;
use Kirby\Cms\Schema;

return [
    'pattern' => 'pages/(:all)',
    'method'  => 'POST',
    'action'  => function ($path) {


        $page      = $this->site()->find($path);
        $blueprint = new Blueprint($this->app()->root('blueprints'), $page->template());
        $schema    = new Schema($page, $blueprint->toArray(), $this->app()->schema());
        $data      = $schema->write($this->request()->data());

        // update the page
        $page = $page->update($data);

        return $this->output('page', $page);

    }
];
