<?php

namespace Kirby\Form\Assertions;

trait Autocomplete
{

    public function assertAutocompleteProperty($default = null)
    {
        if ($default === null) {
            $this->assertPropertyCanBeNull('autocomplete');
        }

        $this->assertPropertyDefault('autocomplete', $default);
        $this->assertPropertyIsOptional('autocomplete');
    }

}
