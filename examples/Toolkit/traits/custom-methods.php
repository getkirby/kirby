<?php

require '../vendor/autoload.php';

use Kirby\Toolkit\Traits\CustomMethods;

class User
{
    use CustomMethods;

    public function username()
    {
        return 'homer';
    }

    public function email()
    {
        return 'homer@simpson.com';
    }
}

User::addCustomMethod('hello', function () {
    return 'Hello ' . $this->username() . '! Your email address is ' . $this->email();
});

$user = new User;

var_dump($user->hello());
