<?php

return function ($site) {
    return $site->find('blog')->children()->visible()->flip();
};
