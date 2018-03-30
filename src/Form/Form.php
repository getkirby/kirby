<?php

namespace Kirby\Form;

use Exception;
use Kirby\Form\Exceptions\PropertyException;
use Kirby\Util\Translate;

class Form extends Component
{

    use Translate;
    use Mixins\Model;

    protected $errors;
    protected $fields;
    protected $values;

    protected function defaultFields(): array
    {
        return [];
    }

    protected function defaultValues(): array
    {
        return [];
    }

    public function errors(): array
    {
        $errors = [];

        foreach ($this->fields() as $field) {
            if (method_exists($field, 'error') === true) {
                if ($error = $field->error()) {
                    $errors[$field->name()] = $error;
                }
            }
        }

        return $errors;
    }

    public function isValid(): bool
    {
        foreach ($this->fields() as $field) {
            if (method_exists($field, 'isValid') === true) {
                $field->isValid();
            }
        }

        return true;
    }

    public function fields()
    {
        if (is_a($this->fields, Fields::class) === true) {
            return $this->fields;
        }

        $model  = $this->model();
        $locale = $this->locale();
        $values = array_change_key_case($this->values);
        $fields = [];

        foreach ($this->fields as $name => $field) {
            $field['locale']    = $locale;
            $field['model']     = $model;
            $field['name']      = $name              = $field['name'] ?? $name;
            $field['value']     = $values[$lowerName = strtolower($name)] ?? null;
            $field['undefined'] = isset($values[$lowerName]) === false;

            $fields[$name] = $field;
        }

        return $this->fields = new Fields($fields);

    }

    public function stringValues(): array
    {
        $values = [];

        foreach ($this->fields() as $field) {
            if (method_exists($field, 'stringValue') === true) {
                $values[$field->name()] = $field->stringValue();
            }
        }

        return $values;
    }

    public function hasErrors(): bool
    {
        return empty($this->errors()) === false;
    }

    protected function setFields(array $fields)
    {
        $this->fields = $fields;
        return $this;
    }

    protected function setValues(array $values = [])
    {
        $this->values = $values;
        return $this;
    }

    public function values(array $data = null): array
    {
        $result = [];

        foreach ($this->fields() as $field) {
            if (method_exists($field, 'value') === true) {
                $result[$field->name()] = $field->value();
            }
        }

        return $result;
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        // convert all field objects to arrays
        $array['fields'] = $this->fields()->toArray();

        // remove the model from the result array
        unset($array['model']);

        return $array;

    }

}
