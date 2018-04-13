<?php

namespace Kirby\Form\Mixins;

trait MinMax
{
    use Max;
    use Min;

    protected function validateMinMax($value, array $messages = []): bool
    {
        return
            $this->validateMax($value, $messages['max'] ?? null) === true &&
            $this->validateMin($value, $messages['min'] ?? null) === true;
    }
}
