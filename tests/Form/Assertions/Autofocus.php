<?php

namespace Kirby\Form\Assertions;

trait Autofocus
{

    public function assertAutofocusProperty(bool $default = false)
    {
        $this->assertPropertyDefault('autofocus', $default);
        $this->assertPropertyIsBool('autofocus');
        $this->assertPropertyIsOptional('autofocus');
    }

}
