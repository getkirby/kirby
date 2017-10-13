<?php

return function ($page) {

    $output = [
        'id'       => $page->id(),
        'title'    => $page->title()->toString(),
        'slug'     => $page->slug(),
        'url'      => $page->url(),
        'num'      => $page->num(),
        'template' => $page->template(),
        'content'  => $this->output('page/content', $page),
        'parent'   => $page->parent() ? $page->parent()->id() : null,
        'parents'  => array_values($page->parents()->toArray(function ($parent) {
            return [
                'id'    => $parent->id(),
                'title' => $parent->title()->toString(),
            ];
        }))
    ];

    if ($image = $page->image()) {
        $output['image'] = $this->output('file', $image);
    }

    if ($prev = $page->prev()) {
        $output['prev'] = $prev->id();
    }

    if ($next = $page->next()) {
        $output['next'] = $next->id();
    }

    return $output;

};
