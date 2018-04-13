<?php

namespace Kirby\Form\Assertions;

use Kirby\Util\Str;

trait MinLength
{

    public function assertMinLengthProperty(int $default = null, $testValue = 'test')
    {
        $this->assertPropertyDefault('minLength', $default);
        $this->assertPropertyValue('minLength', 10);
        $this->assertPropertyIsOptional('minLength');

        if ($testValue !== null) {

            $this->assertValueIsValid([
                'minLength' => Str::length($testValue),
                'value'     => $testValue
            ]);

            $this->assertValueIsInvalid([
                'minLength' => Str::length($testValue) + 1,
                'value'     => $testValue
            ], 'error.form.minLength.invalid');

        }

    }

}
