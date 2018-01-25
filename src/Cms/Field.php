<?php

namespace Kirby\Cms;

use Exception;

abstract class Field extends Object
{

    use HasI18n;

    protected static $toArray = [
        'default',
        'disabled',
        'help',
        'icon',
        'label',
        'name',
        'required',
        'save',
        'type',
        'value'
    ];

    protected $default;
    protected $disabled = false;
    protected $help;
    protected $icon;
    protected $label;
    protected $model;
    protected $name;
    protected $required = false;
    protected $save = true;
    protected $type;
    protected $value;

    public function __construct(array $props)
    {
        $this->setRequiredProperties($props, ['name']);
        $this->setOptionalProperties($props, [
            'default',
            'disabled',
            'help',
            'icon',
            'label',
            'model',
            'required',
            'value'
        ]);
    }

    public function createDataValue($value)
    {
        return $value;
    }

    public function createTextValue($value): string
    {
        return (string)$value;
    }

    public function default()
    {
        return $this->createDataValue($this->default);
    }

    public function emptyValues(): array
    {
        return [null, '', []];
    }

    public function error()
    {
        try {
            $this->submit($this->value());
            return false;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function exception(string $message): Exception
    {
        return new Exception($this->exceptionMessage($message));
    }

    public function exceptionMessage(string $message): string
    {
        return sprintf($message . ' in the %s field "%s"', $this->type(), $this->name());
    }

    public function help()
    {
        return $this->help;
    }

    public function isEmpty(): bool
    {
        $args  = func_get_args();
        $value = count($args) === 0 ? $this->value() : $args[0];

        return in_array($value, $this->emptyValues(), true) === true;
    }

    public function label()
    {
        return $this->label;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function submit()
    {
        if ($this->save === false) {
            return null;
        }

        $args  = func_get_args();
        $value = count($args) === 0 ? $this->value() : $this->createDataValue($args[0]);

        if ($this->isEmpty($value) === true) {
            if ($this->required() === true) {
                throw $this->exception('Missing value');
            }
        } else {
            if ($this->validate($value) === false) {
                throw $this->exception('Invalid value');
            }
        }

        return $this->createTextValue($value);
    }

    public function type(): string
    {
        return $this->type;
    }

    public function value()
    {
        return $this->value;
    }

    public function validate(): bool
    {
        return true;
    }

    protected static function abstract(): array
    {
        return [
            'type'  => null,
            'save'  => true,
            'props' => [
                'disabled' => [
                    'type'    => 'boolean',
                    'default' => false
                ],
                'default' => [

                ],
                'help' => [

                ],
                'i18n' => [
                    'default' => 'en',
                    'freeze'  => true
                ],
                'icon' => [
                    'type' => 'string'
                ],
                'label' => [

                ],
                'model' => [
                    'type' => Object::class
                ],
                'name' => [
                    'type'     => 'string',
                    'required' => true
                ],
                'required' => [
                    'type'    => 'boolean',
                    'default' => false
                ],
                'value' => [
                    'type' => 'scalar'
                ],
            ],
            'beforeCreate' => function () {
                $this->_props->value = $this->createDataValue($this->_props->value ?? $this->_props->default ?? null);
            },
            'computed' => [
                'default' => function () {
                    return $this->createDataValue($this->_props->default);
                },
                'error' => function () {
                    try {
                        $this->submit($this->value());
                        return false;
                    } catch (Exception $e) {
                        return $e->getMessage();
                    }
                },
                'help' => [
                    'set' => function ($value) {
                        return $this->i18n($value);
                    }
                ],
                'label' => [
                    'set' => function ($value) {
                        return $this->i18n($value);
                    }
                ],
                'value' => [
                    'set' => function ($value) {
                        if ($this->isEmpty($value) === true) {
                            return $this->default();
                        }

                        return $this->createDataValue($value);
                    }
                ]
            ],
            'methods' => [
                'validate' => function ($value) {
                    return true;
                },
                'createDataValue' => function ($value) {
                    return $value;
                },
                'createTextValue' => function ($value) {
                    return (string)$value;
                },
                'emptyValues' => function () {
                    return [null, '', []];
                },
                'exception' => function ($message) {
                    return new Exception($this->exceptionMessage($message));
                },
                'exceptionMessage' => function ($message) {
                    return sprintf($message . ' in the %s field "%s"', $this->type, $this->name);
                },
                'i18n' => function ($value = null) {

                    if ($value === null) {
                        return null;
                    }

                    if (is_array($value) === true) {
                        return $value[$this->i18n] ?? '';
                    }

                    if (is_scalar($value) === true) {
                        return $value;
                    }

                    throw $this->exception('Untranslatable value');
                },
                'isEmpty' => function () {
                    $args = func_get_args();

                    if (count($args) === 0) {
                        $value = $this->value();
                    } else {
                        $value = $args[0];
                    }

                    return in_array($value, $this->emptyValues(), true) === true;
                },
                'submit' => function ($value = null) {

                    if ($this->save === false) {
                        return null;
                    }

                    if ($value !== null) {
                        $this->value = $value;
                    }

                    $value = $this->value;

                    if ($this->isEmpty($value) === true) {
                        if ($this->required() === true) {
                            throw $this->exception('Missing value');
                        }
                    } else {
                        if ($this->validate($value) === false) {
                            throw $this->exception('Invalid value');
                        }
                    }

                    return $this->createTextValue($value);

                },
            ]
        ];
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        unset($array['model']);

        // just in case, those have been removed…
        $array['name'] = $this->name;
        $array['type'] = $this->type;

        ksort($array);

        return $array;
    }

}
