<?php

use Kirby\Toolkit\F;

/**
 * User Routes
 */
return [

    [
        'pattern' => 'users',
        'method'  => 'GET',
        'action'  => function () {
            return $this->users()->sort('username', 'asc', 'email', 'asc');
        }
    ],
    [
        'pattern' => 'users',
        'method'  => 'POST',
        'action'  => function () {
            return $this->users()->create($this->requestBody());
        }
    ],
    [
        'pattern' => 'users/search',
        'method'  => 'GET|POST',
        'action'  => function () {
            if ($this->requestMethod() === 'GET') {
                return $this->users()->search($this->requestQuery('q'));
            } else {
                return $this->users()->query($this->requestBody());
            }
        }
    ],
    [
        'pattern' => 'users/(:any)',
        'method'  => 'GET',
        'action'  => function (string $id) {
            return $this->user($id);
        }
    ],
    [
        'pattern' => 'users/(:any)',
        'method'  => 'PATCH',
        'action'  => function (string $id) {
            return $this->user($id)->update($this->requestBody(), $this->language(), true);
        }
    ],
    [
        'pattern' => 'users/(:any)',
        'method'  => 'DELETE',
        'action'  => function (string $id) {
            return $this->user($id)->delete();
        }
    ],
    [
        'pattern' => 'users/(:any)/avatar',
        'method'  => 'GET',
        'action'  => function (string $id) {
            return $this->user($id)->avatar();
        }
    ],
    [
        'pattern' => 'users/(:any)/avatar',
        'method'  => 'POST',
        'action'  => function (string $id) {
            if ($avatar = $this->user($id)->avatar()) {
                $avatar->delete();
            }

            return $this->upload(function ($source, $filename) use ($id) {
                return $this->user($id)->createFile([
                    'filename' => 'profile.' . F::extension($filename),
                    'template' => 'avatar',
                    'source'   => $source
                ]);
            }, $single = true);
        }
    ],
    [
        'pattern' => 'users/(:any)/avatar',
        'method'  => 'DELETE',
        'action'  => function (string $id) {
            return $this->user($id)->avatar()->delete();
        }
    ],
    [
        'pattern' => 'users/(:any)/blueprint',
        'method'  => 'GET',
        'action'  => function (string $id) {
            return $this->user($id)->blueprint();
        }
    ],
    [
        'pattern' => 'users/(:any)/blueprints',
        'method'  => 'GET',
        'action'  => function (string $id) {
            return $this->user($id)->blueprints($this->requestQuery('section'));
        }
    ],
    [
        'pattern' => 'users/(:any)/email',
        'method'  => 'PATCH',
        'action'  => function (string $id) {
            return $this->user($id)->changeEmail($this->requestBody('email'));
        }
    ],
    [
        'pattern' => 'users/(:any)/fields/(:any)/(:all?)',
        'method'  => 'ALL',
        'action'  => function (string $id, string $fieldName, string $path = null) {
            if ($user = $this->user($id)) {
                return $this->fieldApi($user, $fieldName, $path);
            }
        }
    ],
    [
        'pattern' => 'users/(:any)/language',
        'method'  => 'PATCH',
        'action'  => function (string $id) {
            return $this->user($id)->changeLanguage($this->requestBody('language'));
        }
    ],
    [
        'pattern' => 'users/(:any)/name',
        'method'  => 'PATCH',
        'action'  => function (string $id) {
            return $this->user($id)->changeName($this->requestBody('name'));
        }
    ],
    [
        'pattern' => 'users/(:any)/password',
        'method'  => 'PATCH',
        'action'  => function (string $id) {
            return $this->user($id)->changePassword($this->requestBody('password'));
        }
    ],
    [
        'pattern' => 'users/(:any)/role',
        'method'  => 'PATCH',
        'action'  => function (string $id) {
            return $this->user($id)->changeRole($this->requestBody('role'));
        }
    ],
    [
        'pattern' => 'users/(:any)/roles',
        'action'  => function (string $id) {
            return $this->user($id)->roles();
        }
    ],
    [
        'pattern' => 'users/(:any)/sections/(:any)',
        'method'  => 'GET',
        'action'  => function (string $id, string $sectionName) {
            if ($section = $this->user($id)->blueprint()->section($sectionName)) {
                return $section->toResponse();
            }
        }
    ],
];
