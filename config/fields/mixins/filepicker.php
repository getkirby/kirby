<?php

return [
    'methods' => [
        'filepicker' => function (array $params = []) {

            // fetch the parent model
            $model = $this->model();

            // find the right default query
            if (empty($params['query']) === false) {
                $query = $params['query'];
            } elseif (is_a($model, 'Kirby\Cms\File') === true) {
                $query = 'file.siblings';
            } else {
                $query = $model::CLASS_ALIAS . '.files';
            }

            // fetch all files for the picker
            $files = $model->query($query, 'Kirby\Cms\Files');
            $data  = [];

            // prepare the response for each file
            foreach ($files as $index => $file) {
                if (empty($params['map']) === false) {
                    $data[] = $params['map']($file);
                } else {
                    $data[] = $file->panelPickerData([
                        'image' => $params['image'] ?? [],
                        'info'  => $params['info'] ?? false,
                        'model' => $model,
                        'text'  => $params['text'] ?? '{{ file.filename }}',
                    ]);
                }
            }

            return $data;
        }
    ]
];
