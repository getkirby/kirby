<?php

namespace Kirby\Form;

use Exception;
use Kirby\Html\Element;

class Field extends Component
{

    protected $input;

    public function __construct(array $props)
    {
        parent::__construct($props);

        $this->input = $this->inputObject();
    }

    public function schema(...$extend): array
    {
        return parent::schema([
            'class' => [
                'type' => 'string',
            ],
            'label' => [
                'type' => 'string',
            ],
            'name' => [
                'type'     => 'string',
                'required' => true,
            ],
            'wrapper' => [
                'default' => 'div',
            ],
            'type' => [
                'type'     => 'string',
                'required' => true,
                'default'  => 'text',
            ],
        ], ...$extend);
    }

    protected function inputClassName()
    {
        return 'Kirby\\Form\\Input\\' . ucfirst($this->type());
    }

    protected function inputObject()
    {
        $className = $this->inputClassName();

        if (class_exists($className) === false) {
            throw new Exception(sprintf('The field type "%s" does not exist', $this->type()));
        }

        return new $className($this->props());
    }

    public function input()
    {
        return $this->input;
    }

    public function labelElement()
    {
        $label = $this->prop('label');

        if (is_string($label)) {
            $element = Element::create('label', $label);
        } else {
            $element = Element::create($label);
        }

        if ($element !== null) {
            $element->attr('for', $this->id());
        }

        return $element;
    }

    public function wrapperElement()
    {
        return Element::create($this->prop('wrapper'));
    }

    public function inputElement()
    {
        return $this->input()->toHtml();
    }

    public function toArray(): array
    {
        $array = array_merge($this->input()->toArray(), [
            'label' => $this->prop('label'),
            'type'  => $this->prop('type')
        ]);

        ksort($array);

        return $array;
    }

    public function element()
    {
        return $this->wrapperElement()->html([
            $this->labelElement(),
            $this->inputElement()
        ]);
    }

    public function __call($method, $args)
    {
        if ($this->hasProp($method)) {
            return $this->prop($method);
        }

        return $this->input = $this->input()->$method(...$args);
    }

}
