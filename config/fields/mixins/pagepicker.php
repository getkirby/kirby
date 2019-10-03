<?php

return [
    'methods' => [
        'pagepicker' => function (array $params = []) {
            $query    = $params['query'] ?? null;
            $subpages = $params['subpages'] ?? true;
            $model    = $this->model();
            $site     = $this->kirby()->site();

            if (empty($query) === false) {
                if ($subpages === true) {
                    if ($parent = $site->find($params['parent'] ?? null)) {
                        $pages = $parent->children();
                    } else {
                        $pages  = $model->query($query, 'Kirby\Cms\Pages');
                        $parent = $pages->parent();
                    }
                    
                    $self  = [
                        'id'     => (empty($parent->id()) === true || empty($params['parent']) === true) ? null : $parent->id(),
                        'parent' => is_a($parent->parent(), 'Kirby\Cms\Page') === true ? $parent->parent()->id() : null,
                        'title'  => $parent->title()->value(),
                    ];
                } else {
                    $pages = $model->query($query, 'Kirby\Cms\Pages');
                }
            } else {
                if (!$parent = $site->find($params['parent'] ?? null)) {
                    $parent = $site;
                }
                
                $pages = $parent->children();
                
                if ($subpages === true) {
                    $self  = [
                        'id'     => empty($parent->id()) === true ? null : $parent->id(),
                        'parent' => is_a($parent->parent(), 'Kirby\Cms\Page') === true ? $parent->parent()->id() : null,
                        'title'  => $parent->title()->value(),
                    ];
                }
            }

            $children = [];

            foreach ($pages as $index => $page) {
                if ($page->isReadable() === true) {
                    if (empty($params['map']) === false) {
                        $children[] = $params['map']($page);
                    } else {
                        $children[] = $page->panelPickerData([
                            'image' => $params['image'] ?? [],
                            'info'  => $params['info'] ?? false,
                            'model' => $model,
                            'text'  => $params['text'] ?? null,
                        ]);
                    }
                }
            }

            return [
                'model' => $self ?? null,
                'pages' => $children
            ];
        }
    ]
];
