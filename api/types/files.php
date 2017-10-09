<?php

return function ($page, array $query = []) {
    return $this->output('collection', $page->files(), 'file', $query);
};
