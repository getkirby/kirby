<?php

return [
    'methods' => [
        'pagepicker' => function ($params) {
            $query = $this->query();
            $model = $this->model();
            $site  = $this->kirby()->site();

            if ($query) {
                $pages = $model->query($query, 'Kirby\Cms\Pages');
                $self  = null;
            } else {
                if (!$parent = $site->find($params['parent'] ?? null)) {
                    $parent = $site;
                }

                $pages = $parent->children();
                $self  = [
                    'id'     => $parent->id() == '' ? null : $parent->id(),
                    'title'  => $parent->title()->value(),
                    'parent' => $parent->parent() ? $parent->parent()->id() : null,
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
                'model' => $self,
                'pages' => $children
            ];
        }
    ]
];
