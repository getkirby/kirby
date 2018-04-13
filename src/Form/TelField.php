<?php

namespace Kirby\Form;

class TelField extends TextField
{

    protected function defaultAutocomplete()
    {
        return 'tel';
    }

    protected function defaultIcon()
    {
        return 'phone';
    }

    protected function defaultLabel()
    {
        return 'Phone';
    }

    protected function defaultName(): string
    {
        return 'phone';
    }

}
