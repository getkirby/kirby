<?php

namespace Kirby\Form\Mixins;

use Kirby\Form\Exceptions\MissingValueException;

trait Required
{

    protected $required;

    protected function defaultRequired(): bool
    {
        return false;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function required(): bool
    {
        return $this->required;
    }

    /**
     * @param boolean $required
     * @return self
     */
    protected function setRequired(bool $required = false): self
    {
        $this->required = $required;
        return $this;
    }

    protected function validateRequired($value): bool
    {
        if ($this->isEmpty($value) === true) {
            if ($this->isRequired() === true) {
                throw new MissingValueException();
            }
        }

        return true;
    }

}
