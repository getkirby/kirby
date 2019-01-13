<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Component;

class Section extends Component
{

    /**
     * Registry for all component mixins
     *
     * @var array
     */
    public static $mixins = [];

    /**
     * Registry for all component types
     *
     * @var array
     */
    public static $types = [];


    public function __construct(string $type, array $attrs = [])
    {
        if (isset($attrs['model']) === false) {
            throw new InvalidArgumentException('Undefined section model');
        }

        // use the type as fallback for the name
        $attrs['name'] = $attrs['name'] ?? $type;
        $attrs['type'] = $type;

        parent::__construct($type, $attrs);
    }

    public function kirby()
    {
        return $this->model->kirby();
    }

    public function model()
    {
        return $this->model;
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        unset($array['model']);

        return $array;
    }

    public function toResponse(): array
    {
        return array_merge([
            'status' => 'ok',
            'code'   => 200,
            'name'   => $this->name,
            'type'   => $this->type
        ], $this->toArray());
    }
}
