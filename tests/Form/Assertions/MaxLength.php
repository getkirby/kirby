<?php

namespace Kirby\Form\Assertions;

use Kirby\Util\Str;

trait MaxLength
{

    public function assertMaxLengthProperty(int $default = null, $testValue = 'test')
    {
        $this->assertPropertyDefault('maxLength', $default);
        $this->assertPropertyValue('maxLength', 10);

        if ($testValue !== null) {
            $this->assertValueIsValid([
                'maxLength' => Str::length($testValue),
                'value'     => $testValue
            ]);

            $this->assertValueIsInvalid([
                'maxLength' => Str::length($testValue) - 1,
                'value'     => $testValue
            ], 'error.form.maxLength.invalid');
        }

    }

}
