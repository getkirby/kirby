<?php

namespace Kirby\Panel\Options;

use Exception;
use Kirby\Object\Attributes;

class Url
{

    protected $attributes;

    public function __construct(array $attributes)
    {
        $this->attributes = Attributes::create($attributes, [
            'url' => [
                'type'     => 'string',
                'required' => true
            ]
        ]);
    }

    public function collection()
    {
        // TODO: replace with remote class call
        if ($response = file_get_contents($this->attributes['url'])) {
            $output = [];

            foreach ((array)json_decode($response, true) as $value => $text) {
                $output[] = [
                    'value' => $value,
                    'text'  => $text
                ];
            }

            return $output;
        }

        throw new Exception('The remote request failed');
    }

    public function toArray(): array
    {
        return $this->collection();
    }

}
