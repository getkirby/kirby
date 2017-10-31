<?php

namespace Kirby\Panel\Options;

use Exception;
use Kirby\Cms\Object;

class Url extends Object
{

    protected $attributes;

    public function __construct(array $props = [])
    {

        parent::__construct($props, [
            'url' => [
                'type'     => 'string',
                'required' => true
            ]
        ]);
    }

    public function collection()
    {
        // TODO: replace with remote class call
        if ($response = file_get_contents($this->url())) {
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
