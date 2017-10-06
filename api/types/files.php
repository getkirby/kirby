<?php

return function ($page, $arguments) {
    return $this->output('collection', $page->children()->paginate(20), 'page');
};
