<?php

namespace Kirby\Form\Assertions;

trait Help
{

    public function assertHelpProperty($default = null)
    {
        $this->assertPropertyCanBeNull('help');
        $this->assertPropertyDefault('help', $default);
        $this->assertPropertyI18n('help');
        $this->assertPropertyIsOptional('help');
    }

}
