<?php

use Kirby\Cms\Page;
use Kirby\Http\Response;

return function () {

    // fetch the page at the current path
    $response = $this->router()->call($this->path(), $this->request()->method());

    if (is_a($response, Response::class)) {
        return $response;
    }

    if (is_a($response, Page::class)) {
        return $response;
    }

    return $this->site()->find('error');

};
