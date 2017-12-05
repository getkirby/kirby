<?php

namespace Kirby\Form\Input;

use Kirby\Toolkit\V;

class DateTime extends Date
{

    public function schema(...$extend): array
    {
        return parent::schema([
            'pattern' => [
                'default'   => '[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}',
            ],
            'type' => [
                'default'   => 'datetime-local',
            ],
        ], ...$extend);
    }

    public function fill($value)
    {
        if ($ts = strtotime($value)) {
            return $this->set('value', date('Y-m-d\TH:i:s', $ts));
        }

        return $this->set('value', null);
    }

    public function validate($input): bool
    {
        return V::date($input);
    }

}
