<?php

namespace Kirby\Form;

use Kirby\Data\Yaml;
use Throwable;

/**
 * The main form class, that is being
 * used to create a list of form fields
 * and handles global form validation
 * and submission
 *
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
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
        $strict = $props['strict'] ?? false;
        $inject = $props;

        // lowercase all value names
        $values = array_change_key_case($values);
        $input  = array_change_key_case($input);

        unset($inject['fields'], $inject['values'], $inject['input']);

        $this->fields = new Fields();
        $this->values = [];

        foreach ($fields as $name => $props) {

            // inject stuff from the form constructor (model, etc.)
            $props = array_merge($inject, $props);

            // inject the name
            $props['name'] = $name = strtolower($name);

            // check if the field is disabled
            $disabled = $props['disabled'] ?? false;

            // overwrite the field value if not set
            if ($disabled === true) {
                $props['value'] = $values[$name] ?? null;
            } else {
                $props['value'] = $input[$name] ?? $values[$name] ?? null;
            }

            try {
                $field = new Field($props['type'], $props);
            } catch (Throwable $e) {
                $field = static::exceptionField($e, $props);
            }

            if ($field->save() !== false) {
                $this->values[$name] = $field->value();
            }

            $this->fields->append($name, $field);
        }

        if ($strict !== true) {

            // use all given values, no matter
            // if there's a field or not.
            $input = array_merge($values, $input);

            foreach ($input as $key => $value) {
                if (isset($this->values[$key]) === false) {
                    $this->values[$key] = $value;
                }
            }
        }
    }

    public function data($defaults = false): array
    {
        $data = $this->values;

        foreach ($this->fields as $field) {
            if ($field->save() === false || $field->unset() === true) {
                $data[$field->name()] = null;
            } else {
                $data[$field->name()] = $field->data($defaults);
            }
        }

        return $data;
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
                    'label'   => $field->label(),
                    'message' => $field->errors()
                ];
            }
        }

        return $this->errors;
    }

    public static function exceptionField(Throwable $exception, array $props = [])
    {
        $props = array_merge($props, [
            'label' => 'Error in "' . $props['name'] . '" field',
            'theme' => 'negative',
            'text'  => strip_tags($exception->getMessage()),
        ]);

        return new Field('info', $props);
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

    public function strings($defaults = false): array
    {
        $strings = [];

        foreach ($this->data($defaults) as $key => $value) {
            if ($value === null) {
                $strings[$key] = null;
            } elseif (is_array($value) === true) {
                $strings[$key] = Yaml::encode($value);
            } else {
                $strings[$key] = $value;
            }
        }

        return $strings;
    }

    public function toArray(): array
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
