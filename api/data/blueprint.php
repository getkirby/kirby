<?php

use Kirby\Data\Data;

return function ($root) {
    return ['name' => pathinfo($root, PATHINFO_FILENAME)] + Data::read($root);
};
