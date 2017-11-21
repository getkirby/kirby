<?php

require '../vendor/autoload.php';

use Kirby\Toolkit\Traits\SetterGetter;

class User
{
    use SetterGetter;

    protected $data = [];

    public function set(string $key, $value)
    {
        return $this->data[$key] = $value;
    }

    public function get(string $key)
    {
        return $this->data[$key] ?? null;
    }
}

$user = new User;
$user->username('homer');
$user->email('homer@simpson.com');

var_dump($user->username());
var_dump($user->email());
