<?php

namespace Kirby\Form\Assertions;

trait Placeholder
{

    public function assertPlaceholderProperty(string $default = null)
    {
        if ($default === null) {
            $this->assertPropertyCanBeNull('placeholder');
        }

        $this->assertPropertyDefault('placeholder', $default);
        $this->assertPropertyTranslate('placeholder');
        $this->assertPropertyIsOptional('placeholder');
    }

}
