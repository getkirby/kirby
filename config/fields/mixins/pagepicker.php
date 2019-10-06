<?php

return [
    'methods' => [
        'pagepicker' => function (array $params = []) {
            $query    = $params['query'] ?? null;
            $subpages = $params['subpages'] ?? true;
            $model    = $this->model();
            $site     = $this->kirby()->site();
            $root     = $site;
            
            if (empty($query) === false) {
                if ($subpages === true) {
                    $root  = $model->query($query, 'Kirby\Cms\Pages');
                    
                    if ($parent = $site->find($params['parent'] ?? null)) {
                        $pages = $parent->children();
                    } else {
                        $pages  = $root;
                        $parent = $pages->parent();
                    }
                } else {
                    $pages  = $model->query($query, 'Kirby\Cms\Pages');
                    $parent = null;
                }
            } else {
                if (!$parent = $site->find($params['parent'] ?? null)) {
                    $parent = $root;
                }
                
                $pages = $parent->children();
            }
            
            if ($subpages === true && $parent !== null) {
                $self  = [
                    'id'     => (empty($parent->id()) === true || $parent->id() === $root->parent()->id()) ? null : $parent->id(),
                    'parent' => is_a($parent->parent(), 'Kirby\Cms\Page') === true ? $parent->parent()->id() : null,
                    'title'  => $parent->title()->value(),
                ];
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
