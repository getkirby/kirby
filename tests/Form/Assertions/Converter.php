<?php

namespace Kirby\Form\Assertions;

trait Converter
{

    public function assertConverterProperty($default = null)
    {
        $this->assertPropertyCanBeNull('converter');
        $this->assertPropertyDefault('converter', $default);
        $this->assertPropertyIsOptional('converter');
        $this->assertPropertyValues('converter', [
            'lower',
            'slug',
            'ucfirst',
            'upper'
        ]);
    }

}
