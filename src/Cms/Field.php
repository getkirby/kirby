<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Util\Component;

class Field extends Component
{

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
