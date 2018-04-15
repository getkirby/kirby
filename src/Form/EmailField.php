<?php

namespace Kirby\Form;

class EmailField extends TextField
{
    use Mixins\Email;

    protected function defaultAutocomplete()
    {
        return 'email';
    }

    protected function defaultIcon()
    {
        return 'email';
    }

    protected function defaultLabel()
    {
        return 'Email';
    }

    protected function defaultName(): string
    {
        return 'email';
    }

    protected function defaultPlaceholder()
    {
        return 'mail@example.com';
    }

    protected function validate($value): bool
    {
        $this->validateRequired($value);
        $this->validateLength($value);
        $this->validateEmail($value);

        return true;
    }
}
