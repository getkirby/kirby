<?php

return [
    'pattern' => 'panel/autocomplete/tags',
    'action'  => function () {

        $page     = $this->site()->find($this->input('page'));
        $siblings = $page->siblings();
        $tags     = [];

        foreach ($siblings as $sibling) {
            $field = $this->input('field');
            $tags  = array_merge($tags, $sibling->$field()->split(','));
        }

        return array_unique($tags);

    }
];
