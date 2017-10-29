<?php

namespace Kirby\Cms;

use Exception;

class Fields
{

    protected $model;
    protected $fields = [];
    protected $schema;

    public function __construct(Object $model = null, array $fields = [])
    {

        $this->model  = $model;
        $this->schema = App::instance()->schema();

        foreach ((array)$fields as $attributes) {
            $field = $this->field($attributes);
            $this->fields[$field['name']] = $field;
        }

    }

    public function schema(): array
    {
        return $this->schema;
    }

    public function converter(string $type)
    {
        $schema = $this->schema[$type] ?? null;
        return $schema['setup'] ?? null;
    }

    public function field(array $attributes): array
    {

        if (empty($attributes['name'])) {
            throw new Exception('The field name is missing');
        }

        if (empty($attributes['type'])) {
            throw new Exception('Invalid field type');
        }

        // normalize the field name
        $attributes['name'] = strtolower($attributes['name']);

        if ($converter = $this->converter($attributes['type'])) {
            $attributes = array_merge($attributes, $converter($this->model, $attributes));
        }

        return $attributes;
    }

    public function toArray(): array
    {
        return $this->fields;
    }

}
