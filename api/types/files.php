<?php

return function ($page, $arguments) {
    return $this->output('collection', $page->files()->paginate(20), 'file');
};
