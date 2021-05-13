<?php

/** @var \Kirby\Cms\App $kirby */

use Kirby\Http\Response;
use Kirby\Toolkit\View;

return function () use ($kirby) {
    $view = new View($kirby->root('kirby') . '/views/browser.php');
    return new Response($view->render());
};
