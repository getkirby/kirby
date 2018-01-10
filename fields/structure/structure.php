<?php

use Kirby\Cms\Fields;
use Kirby\Data\Handler\Yaml;

return [
    'type'  => 'structure',
    'props' => [
        'fields' => [
            'type'     => 'array',
            'required' => true
        ],
        'style' => [
            'type'     => 'string',
            'validate' => function ($value) {
                return in_array($value, ['table', 'list']) === true;
            }
        ],
        'value' => [
            'type' => 'array'
        ],
    ],
    'methods' => [
        'createDataValue' => function ($value) {
            if ($value === null) {
                $value = [];
            }

            if (is_string($value)) {
                $value = Yaml::decode($value);
            }

            if (is_array($value) === false) {
                throw $this->exception('Invalid value type');
            }

            $result = [];

            foreach ($value as $key => $row) {
                $result[] = $this->fields()->values($row);
            }

            return $result;
        },
        'createTextValue' => function ($value) {
            $result = [];

            foreach ($value as $key => $row) {
                $result[] = $this->fields()->createTextValues($row);
            }

            return Yaml::encode($result);
        },
        'fields' => function () {
            if (is_a($this->_data->fields, Fields::class)) {
                return $this->_data->fields;
            }

            return $this->_data->fields = new Fields($this->_props->fields, [], $this->model);
        },
        'toArray' => function ($array) {

            $array['fields'] = array_map(function ($field) {
                unset($field['error']);
                unset($field['value']);
                return $field;
            }, $this->fields()->toArray());

            return $array;
        },
        'validate' => function (array $rows) {

            foreach ($rows as $key => $row) {
                if ($this->fields()->validate($row) === false) {
                    try {
                        $this->fields()->submit($row);
                    } catch (Exception $e) {
                        throw new Exception($e->getMessage() . ' in entry #' . $key);
                        return false;
                    }
                }
            }

            return true;

        }
    ]
];
