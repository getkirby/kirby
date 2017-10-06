<?php

use Kirby\Api\Type;
use Kirby\Toolkit\Str;

return function ($site) {
    return [
        'description' => 'Creates a new page',
        'type'        => Type::page(),
        'args'        => [
            'page' => [
                'type'     => Type::pageInput(),
                'required' => true,
            ]
        ],
        'resolve' => function ($root, $args) use ($site) {

            $page       = $args['page'];
            $parentId   = dirname($page['id']);
            $parentPage = $parentId === '.' ? $site : $site->find($parentId);
            $slug       = basename($page['id']);

            if ($parentPage === null) {
                throw new Exception(sprintf('The parent page "%s" cannot be found', $parentId));
            }

            $content = [];

            foreach ($page['content'] as $field) {
                $content[$field['key']] = $field['value'];
            }

            $page = $parentPage->child([
                'template' => $page['template'],
                'slug'     => $slug,
                'content'  => $content
            ]);

            $page->save();

            return $page;

        }
    ];
};
