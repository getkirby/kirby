<?php

namespace Kirby\Form\Assertions;

trait Disabled
{

    public function assertDisabledProperty(bool $default = false)
    {
        $this->assertPropertyDefault('disabled', $default);
        $this->assertPropertyIsBool('disabled');
    }

}
