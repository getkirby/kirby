<?php

return [
    'methods' => [
        'userpicker' => function (array $params = []) {

            // fetch the parent model
            $model = $this->model();

            // find the right default query
            if (empty($params['query']) === false) {
                $query = $params['query'];
            } elseif (is_a($model, 'Kirby\Cms\User') === true) {
                $query = 'user.siblings';
            } else {
                $query = 'kirby.users';
            }

            // fetch all users for the picker
            $users = $model->query($query, 'Kirby\Cms\Users');
            $data  = [];

            if (!$users) {
                return [];
            }

            // prepare the response for each user
            foreach ($users->sortBy('username', 'asc') as $index => $user) {
                if (empty($params['map']) === false) {
                    $data[] = $params['map']($user);
                } else {
                    $data[] = $user->panelPickerData([
                        'image' => $params['image'] ?? [],
                        'info'  => $params['info'] ?? false,
                        'model' => $model,
                        'text'  => $params['text'] ?? '{{ user.username }}',
                    ]);
                }
            }

            return $data;
        }
    ]
];
