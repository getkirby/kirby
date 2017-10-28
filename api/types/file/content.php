<?php

use Kirby\Cms\Output;

return function ($file) {
    return (new Output($file))->toArray();
};
