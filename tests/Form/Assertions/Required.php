<?php

namespace Kirby\Form\Assertions;

trait Required
{

    public function assertRequiredProperty(bool $default = false)
    {
        $this->assertPropertyDefault('required', $default);
        $this->assertPropertyIsBool('required');
        $this->assertPropertyIsOptional('required');
    }

}
