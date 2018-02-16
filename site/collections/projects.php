<?php

return function ($site) {
    return $site->find('projects')->children()->visible();
};
