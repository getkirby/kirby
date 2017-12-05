<?php

namespace Kirby\Form;

use Exception;
use Kirby\Collection\Collection;
use Kirby\Html\Element;

class Form extends Component
{

    protected static $fieldTypes = [];
    protected $fields;


    public function __construct(array $props)
    {
        parent::__construct($props);

        // register all fields
        $this->fields = $this->fieldsCollection($this->prop('fields'));

        // fill with the first set of values
        $this->fill($this->prop('values'));
    }

    public function accepts(array $values = [])
    {
        foreach ($values as $key => $value) {
            if ($field = $this->fields()->find($key)) {
                if ($field->accepts($value) === false) {
                    return false;
                }
            } else {
                return false;
            }
        }

        return true;
    }

    public function begin()
    {
        return $this->element()->begin();
    }

    public function element(): Element
    {
        return new Element($this->tag(), $this->fields()->toHtml(), $this->attributes());
    }

    public function end()
    {
        return $this->element()->end();
    }

    public function errors(array $values = null)
    {
        if ($values === null) {
            $values = $this->values();
        }

        $errors = [];

        foreach ($values as $key => $value) {
            if ($field = $this->fields()->find($key)) {
                if ($field->accepts($value) === false) {
                    $errors[] = $key;
                }
            } else {
                $errors[] = $key;
            }
        }

        return $errors;

    }

    public function schema(...$extend): array
    {
        return parent::schema([
            'action' => [
                'type'      => 'string',
                'attribute' => true
            ],
            'autocomplete' => [
                'type'      => 'string',
                'attribute' => true
            ],
            'class' => [
                'type'      => 'string',
                'attribute' => true
            ],
            'enctype' => [
                'type'      => 'string',
                'attribute' => true
            ],
            'fields' => [
                'type'    => 'array',
                'default' => []
            ],
            'id' => [
                'type'      => 'string',
                'attribute' => true
            ],
            'method' => [
                'type'      => 'string',
                'default'   => 'POST',
                'attribute' => true
            ],
            'name' => [
                'type'      => 'string',
                'attribute' => true
            ],
            'novalidate' => [
                'type'      => 'boolean',
                'attribute' => true
            ],
            'target' => [
                'type'      => 'string',
                'attribute' => true
            ],
            'values' => [
                'type'      => 'array',
                'default'   => []
            ],
        ], ...$extend);
    }

    public function fields()
    {
        return $this->fields;
    }

    public function field($props)
    {

        // return a single registered field
        if (is_string($props)) {
            return $this->fields()->find($props);
        }

        return $this->fieldObject($props);

    }

    protected function fieldClassName($type): string
    {
        if ($fieldClass = static::fieldType($type)) {
            return $fieldClass;
        }

        // try to find specific field classes
        $fieldClass = 'Kirby\\Form\\Field\\' . ucfirst($type);

        if (class_exists($fieldClass) === true) {
            return $fieldClass;
        }

        return Field::class;
    }

    protected function fieldObject(array $props)
    {
        // globally switch off autocompletion if the form switches it off
        if ($this->prop('autocomplete') === 'off') {
            $props['autocomplete'] = 'off';
        }

        // check for a valid field type
        if (empty($props['type']) === true) {
            throw new Exception('Each field must define a field type');
        }

        $fieldClassName = $this->fieldClassName($props['type']);
        $fieldObject    = new $fieldClassName($props);

        // check for valid fields
        if (is_a($fieldObject, Field::class) === false) {
            throw new Exception('All fields must extend the field class');
        }

        return $fieldObject;
    }

    public static function fieldType(string $type, string $className = null)
    {
        if ($className === null) {
            return static::$fieldTypes[$type] ?? null;
        }

        return static::$fieldTypes[$type] = $className;
    }

    protected function fieldsCollection(array $fields)
    {
        return new Fields(array_map(function ($field) {
            return $this->field($field);
        }, $fields));
    }

    public function fill(array $values = [])
    {
        foreach ($values as $key => $value) {
            if ($field = $this->fields()->find($key)) {
                $field->fill($value);
            }
        }

        return $this;
    }

    public function tag()
    {
        return 'form';
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        // get a detailed array of all fields
        $array['fields'] = array_values($this->fields()->toArray(function ($field) {
            return $field->toArray();
        }));

        return $array;
    }

    public function values()
    {
        return $this->fields()->toArray(function ($field) {
            return $field->value();
        });
    }

}
