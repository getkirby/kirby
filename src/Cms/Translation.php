<?php

namespace Kirby\Cms;

use Kirby\Data\Data;

class Translation extends Component
{

    protected $code;
    protected $data;

    public function __construct($code)
    {
        $this->code = $code;
        $this->root = dirname(dirname(__DIR__)) . '/translations/' . $this->code . '.json';
    }

    public function data()
    {
        if (is_array($this->data) === true) {
            return $this->data;
        }

        return $this->data = Data::read($this->root());
    }

    public function direction(): string
    {
        return $this->get('translation.direction', 'ltr');
    }

    public function get(string $key, $default = null)
    {
        return $this->data()[$key] ?? $default;
    }

    public function id(): string
    {
        return $this->code;
    }

    public function name(): string
    {
        return $this->get('translation.name');
    }

    public function root(): string
    {
        return $this->root;
    }

    public function toArray(): array
    {
        return [
            'data'       => $this->data(),
            'id'         => $this->id(),
            'name'       => $this->get('translation.name'),
            'translator' => $this->get('translation.translator'),
        ];
    }

    public function translator(): string
    {
        return $this->get('translation.translator');
    }

}

