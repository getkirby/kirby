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

            $fields = $this->form->fields()->toArray();

            if (is_a($this->model, 'Kirby\Cms\Page') === true || is_a($this->model, 'Kirby\Cms\Site') === true) {
                // the title should never be updated directly via
                // fields section to avoid conflicts with the rename dialog
                unset($fields['title']);
            }

            return $fields;

        },
        'errors' => function () {
            return $this->form->errors();
        },
        'data' => function () {

            $values = $this->form->values();

            if (is_a($this->model, 'Kirby\Cms\Page') === true || is_a($this->model, 'Kirby\Cms\Site') === true) {
                // the title should never be updated directly via
                // fields section to avoid conflicts with the rename dialog
                unset($values['title']);
            }

            return $values;

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
