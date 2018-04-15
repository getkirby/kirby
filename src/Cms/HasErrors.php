<?php

namespace Kirby\Cms;

trait HasErrors
{

    /**
     * Returns all content validation errors
     *
     * @return array
     */
    public function errors(): array
    {
        $errors = [];

        foreach ($this->blueprint()->sections() as $section) {
            $errors = array_merge($errors, $section->errors());
        }

        return $errors;
    }
}
