<?php

namespace Kirby\Form;

use Kirby\Collection\Collection;

class Form
{

    protected $errors;
    protected $fields;
    protected $values;

    public function __construct(array $props)
    {
        $fields = $props['fields'] ?? [];
        $values = $props['values'] ?? [];
        $inject = $props;

        unset($inject['fields'], $inject['values']);

        $this->fields = new Fields;

        foreach ($fields as $name => $props) {

            // inject the name
            $props['name'] = $name;

            // inject the value
            if (isset($values[$name]) === true) {
                $props['value'] = $values[$name];
            }

            $field = new Field($props, $inject);

            if ($field->save() !== false) {
                $this->values[$name] = $field->value();
            }

            $this->fields->append($name, $field);
        }
    }

    public function errors(): array
    {
        if ($this->errors !== null) {
            return $this->errors;
        }

        $this->errors = [];

        foreach ($this->fields as $field) {
            if (empty($field->errors()) === false) {
                $this->errors[$field->name()] = [
                    'label' => $field->label(),
                    'message' => $field->errors()
                ];
            }
        }

        return $this->errors;
    }

    public function fields()
    {
        return $this->fields;
    }

    public function isInvalid(): bool
    {
        return empty($this->errors()) === false;
    }

    public function isValid(): bool
    {
        return empty($this->errors()) === true;
    }

    public function toArray()
    {
        $array = [
            'errors' => $this->errors(),
            'fields' => $this->fields->toArray(function ($item) {
                return $item->toArray();
            }),
            'invalid' => $this->isInvalid(),
            'values'  => $this->values,
        ];

        return $array;
    }

        public function strings(): array
    {
        $array = [];

        foreach ($this->fields as $field) {
            if ($field->save() !== false) {
                $array[$field->name()] = $field->toString();
            }
        }

        return $array;
    }

    public function values(): array
    {
        return $this->values;
    }

}
