<?php

use Kirby\Toolkit\V;

return array_replace_recursive(
    require __DIR__ . '/maxLength.php',
    require __DIR__ . '/minLength.php'
, [
    'methods' => [
        'validate' => function ($value) {
            if ($this->isTooShort($value)) {
                throw $this->exception('The text is too short');
            }

            if ($this->isTooLong($value)) {
                throw $this->exception('The text is too long');
            }

            return true;
        }
    ]
]);
