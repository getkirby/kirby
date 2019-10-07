<?php

return [
    'methods' => [
        'pagepicker' => function (array $params = []) {

            // default params
            $params = array_merge([
                'image'    => [],
                'info'     => false,
                'map'      => null,
                'parent'   => null,
                'query'    => null,
                'subpages' => true,
                'text'     => null
            ], $params);


            $model = $this->model();
            $site  = $this->kirby()->site();
            $root  = $site;

            if (empty($params['query']) === false) {
                if ($params['subpages'] === true) {
                    $root = $model->query($params['query'], 'Kirby\Cms\Pages');

                    if ($parent = $site->find($params['parent'])) {
                        $pages = $parent->children();
                    } else {
                        $pages  = $root;
                        $parent = $pages->parent();
                    }
                } else {
                    $pages  = $model->query($params['query'], 'Kirby\Cms\Pages');
                    $parent = null;
                }
            } else {
                if (!$parent = $site->find($params['parent'])) {
                    $parent = $root;
                }

                $pages = $parent->children();
            }

            if ($params['subpages'] === true && $parent !== null) {
                $self = [
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
                            'image' => $params['image'],
                            'info'  => $params['info'],
                            'model' => $model,
                            'text'  => $params['text'],
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
