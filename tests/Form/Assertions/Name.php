<?php

namespace Kirby\Form\Assertions;

trait Name
{

    public function assertNameProperty($default = null)
    {
        if ($default !== null) {
            $this->assertPropertyDefault('name', $default);
        }

        $this->assertPropertyValue('name', 'test');
    }

}
