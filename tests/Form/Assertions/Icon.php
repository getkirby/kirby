<?php

namespace Kirby\Form\Assertions;

trait Icon
{

    public function assertIconProperty($default = null)
    {
        if ($default === null) {
            $this->assertPropertyCanBeNull('icon');
        }

        $this->assertPropertyDefault('icon', $default);
    }

}
