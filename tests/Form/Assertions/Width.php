<?php

namespace Kirby\Form\Assertions;

trait Width
{

    public function assertWidthProperty(string $default = '1/1')
    {
        $this->assertPropertyDefault('width', $default);
        $this->assertPropertyValue('width', '1/2');
        $this->assertPropertyIsOptional('width');
    }

}
