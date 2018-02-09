<?php

namespace Kirby\Form\Assertions;

trait Label
{

    public function assertLabelProperty($default = null)
    {
        if ($default === null) {
            $this->assertPropertyCanBeNull('label');
        }

        $this->assertPropertyDefault('label', $default);
        $this->assertPropertyI18n('label');
        $this->assertPropertyIsOptional('label');
    }

}
