<?php

require '../vendor/autoload.php';

use Kirby\Object\Attributes;

class User
{

    protected $attributes;

    public function __construct(array $attributes = [])
    {
        $this->attributes = new Attributes($attributes, [
            'id' => [
                'type'     => 'string',
                'required' => true,
                'validate' => function ($attribute) {

                }
            ]
        ]);
    }

    public function __call($method, $arguments)
    {
        if (isset($this->attributes->$method)) {
            return $this->attributes->$method;
        } else {
            return null;
        }
    }

}



$user = new User([
    'id'  => 'homer',
    'url' => 'https://getkirby.com/authors/homer'
]);


var_dump($user->url());

var_dump($user->test());
