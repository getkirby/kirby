<?php

namespace Kirby\Form;

class UrlField extends TextField
{

    use Mixins\Url;

    protected function defaultAutocomplete()
    {
        return 'url';
    }

    protected function defaultIcon()
    {
        return 'url';
    }

    protected function defaultLabel()
    {
        return 'Url';
    }

    protected function defaultName(): string
    {
        return 'url';
    }

    protected function defaultPlaceholder()
    {
        return [
            'en_US' => 'https://example.com'
        ];
    }

    protected function validate($value): bool
    {
        $this->validateRequired($value);
        $this->validateLength($value);
        $this->validateUrl($value);

        return true;
    }

}
