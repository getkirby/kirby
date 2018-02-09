<?php

namespace Kirby\Form\Mixins;

use Kirby\Form\Exceptions\PropertyException;
use Kirby\Util\Str;

trait Converter
{

    protected $converter;

    protected function convert($value)
    {
        if ($this->converter() === null) {
            return $value;
        }

        $converter = $this->converters()[$this->converter()];

        if (is_array($value) === true) {
            return array_map($converter, $value);
        }

        return $converter($value);
    }

    public function converter()
    {
        return $this->converter;
    }

    protected function converters(): array
    {
        return [
            'lower' => function ($value) {
                return Str::lower($value);
            },
            'slug' => function ($value) {
                return Str::slug($value);
            },
            'ucfirst' => function ($value) {
                return Str::ucfirst($value);
            },
            'upper' => function ($value) {
                return Str::upper($value);
            },
        ];
    }

    protected function defaultConverter()
    {
        return null;
    }

    protected function setConverter(string $converter = null)
    {
        if ($converter !== null && in_array($converter, array_keys($this->converters())) === false) {
            throw new PropertyException('Invalid converter');
        }

        $this->converter = $converter;
        return $this;
    }

}
