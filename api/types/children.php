<?php

return function ($page, array $query = []) {
    return $this->output('collection', $page->children(), 'page', $query);
};
