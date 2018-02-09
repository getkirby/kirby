<?php

/**
 * Session Routes
 */
return [

    [
        'pattern' => 'session',
        'method'  => 'GET',
        'action'  => function () {
            return $this->users()->first();
        }
    ],
    [
        'pattern' => 'session',
        'method'  => 'POST',
        'action'  => function () {

            $email    = $this->requestBody('email');
            $password = $this->requestBody('password');
            $user     = $this->user(sha1($email));

            if ($user->validatePassword($password) === true) {
                return $user;
            }

            throw new Exception('The session could not be created');
        }
    ],
    [
        'pattern' => 'session',
        'method'  => 'DELETE',
        'action'  => function () {
            return true;
        }
    ],

];
