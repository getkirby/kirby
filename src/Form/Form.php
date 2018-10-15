<?php

namespace Kirby\Form;

use Throwable;
use Kirby\Toolkit\Collection;
use Kirby\Data\Yaml;

/**
 * The main form class, that is being
 * used to create a list of form fields
 * and handles global form validation
 * and submission
 */
class Form
{
    protected $errors;
    protected $fields;
    protected $values = [];

    public function __construct(array $props)
    {
        $fields = $props['fields'] ?? [];
        $values = $props['values'] ?? [];
        $input  = $props['input']  ?? [];
        $inject = $props;

        // lowercase all value names
        $values = array_change_key_case($values);
        $input  = array_change_key_case($input);

        unset($inject['fields'], $inject['values'], $inject['input']);

        $this->fields = new Fields;
        $this->values = array_merge($values, $input);

        foreach ($fields as $name => $props) {

            // inject stuff from the form constructor (model, etc.)
            $props = array_merge($inject, $props);

            // inject the name
            $props['name']  = $name = strtolower($name);

            // overwrite the field value if not set
            if (($props['disabled'] ?? false) === true) {
                $props['value'] = $values[$name] ?? null;
            } else {
                $props['value'] = $input[$name] ?? $values[$name] ?? null;
            }

            try {
                $field = new Field($props['type'], $props);
            } catch (Throwable $e) {
                $props = array_merge($props, [
                    'name'  => $props['name'],
                    'label' => 'Error in "' . $props['name'] . '" field',
                    'theme' => 'negative',
                    'text'  => $e->getMessage(),
                ]);

                $field = new Field('info', $props);
            }

            if ($field->save() !== false) {
                $this->values[$name] = $field->value();
            }

            $this->fields->append($name, $field);
        }
    }

    public function data(): array
    {
        $data = [];

        foreach ($this->fields as $field) {
            if ($field->save() !== false) {
                $data[$field->name()] = $field->data();
            }
        }

        return $data + $this->values;
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

    public function strings(): array
    {
        $strings = [];

        foreach ($this->data() as $key => $value) {
            if (is_array($value) === true) {
                $strings[$key] = Yaml::encode($value);
            } else {
                $strings[$key] = (string)$value;
            }
        }

        return $strings;
    }

    public function toArray()
    {
        $array = [
            'errors' => $this->errors(),
            'fields' => $this->fields->toArray(function ($item) {
                return $item->toArray();
            }),
            'invalid' => $this->isInvalid()
        ];

        return $array;
    }

    public function values(): array
    {
        return $this->values;
    }
}
