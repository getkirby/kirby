<?php

namespace Kirby\Panel;

use Exception;

class FieldsSection extends Section
{

    protected $fields;
    protected $values;

    public function schema(): array
    {
        return [
            'fields' => [
                'type'     => 'array',
                'required' => true
            ]
        ];
    }

    public function fields(): array
    {

        if (is_array($this->fields) === true) {
            return $this->fields;
        }

        $this->fields = [];

        foreach ($this->prop('fields') as $field) {

            if (empty($field['name']) === true) {
                throw new Exception('The field name is missing');
            }

            if (empty($field['type']) === true) {
                throw new Exception('Invalid field type');
            }

            // normalize the field name
            $field['name'] = strtolower($field['name']);

            // get the model
            $model = $this->model();

            // fetch the value from the model
            $value = $model->content()->toArray()[$field['name']] ?? null;

            // creat the field object
            $this->fields[] = new Field($model, $value, $field);

        }

        return $this->fields;

    }

    public function values(): array
    {

        if (is_array($this->values) === true) {
            return $this->values;
        }

        foreach ($this->fields() as $field) {
            $this->values[ $field->props()['name'] ] = $field->value();
        }

        return $this->values;

    }

    public function results($input = []): array
    {

        $results = [];

        foreach ($this->fields() as $field) {

            if (isset($input[$field->name()]) === false) {
                $value = $field->originalValue();
            } else {
                $value = $field->result($input[$field->name()]);
            }

            $results[$field->name()] = $value;

        }

        return $results;

    }

    public function errors($input = [])
    {
        $values = $this->results($input);
        $errors = [];

        foreach ($this->fields() as $field) {
            if ($field->validate($values[$field->name()]) === false) {
                $errors[] = $field->name();
            }
        }

        return $errors;
    }

    public function toArray(): array
    {
        $fields = array_map(function ($field) {
            return $field->toArray();
        }, $this->fields());

        return [
            'fields' => $fields,
            'values' => $this->values(),
        ];
    }

}
