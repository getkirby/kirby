<?php

use Kirby\Cms\Panel;

return function () use ($kirby) {
    return Panel::browser($kirby);
};
