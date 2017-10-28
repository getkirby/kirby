<?php

use Kirby\Cms\Output;

return function ($page) {
    return (new Output($page))->toArray();
};
