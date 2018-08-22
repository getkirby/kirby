<?php

use Kirby\Cms\Form;

return [
    'props' => [
        'fields' => function (array $fields = []) {
            return $fields;
        }
    ],
    'computed' => [
        'form' => function () {

            $fields   = $this->fields;
            $disabled = $this->model->permissions()->update() === false;

            if ($disabled === true) {
                foreach ($fields as $key => $props) {
                    $fields[$key]['disabled'] = true;
                }
            }

            return new Form([
                'fields' => $fields,
                'values' => $this->model->content()->toArray(),
                'model'  => $this->model,
            ]);

        },
        'fields' => function () {
            return $this->form->fields()->toArray();
        },
        'errors' => function () {
            return $this->form->errors();
        },
        'data' => function () {
            return $this->form->values();
        }
    ],
    'toArray' => function () {
        return [
            'data'   => $this->data,
            'errors' => $this->errors,
            'fields' => $this->fields,
        ];
    }
];
