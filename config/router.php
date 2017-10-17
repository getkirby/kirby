<?php

use Kirby\Http\Router;

return function () {
    return new Router($this->routes());
};
