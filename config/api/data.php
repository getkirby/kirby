<?php

use Kirby\Exception\NotFoundException;

/**
 * Api Data Definitions
 */
return [
    'session' => function (array $options = []) {
        $options = array_merge([
            'detect' => true
        ], $options);

        return $this->kirby()->session($options);
    },
    'file' => function (string $id = null, string $filename) {

        $filename = urldecode($filename);
        $parent   = $id === null ? $this->site(): $this->page($id);

        if ($file = $parent->file($filename)) {
            return $file;
        }

        throw new NotFoundException([
            'key'  => 'file.notFound',
            'data' => [
                'filename' => $filename
            ]
        ]);

    },
    'kirby' => function () {
        $kirby = kirby();

        if ($language = $this->language()) {
            $kirby->localize($kirby->languages()->find($language));
        }

        return $kirby;
    },
    'language' => function () {
        return $this->requestHeaders('x-language');
    },
    'page' => function (string $id) {
        $id   = str_replace('+', '/', $id);
        $page = $this->kirby()->page($id);

        if ($page && $page->isReadable()) {
            return $page;
        }

        throw new NotFoundException([
            'key'  => 'page.notFound',
            'data' => [
                'slug' => $id
            ]
        ]);
    },
    'site' => function () {
        return $this->kirby()->site();
    },
    'user' => function (string $id = null, array $sessionOptions = []) {

        // get the authenticated user
        if ($id === null) {
            return $this->kirby()->user(null, $this->session($sessionOptions));
        }

        // get a specific user by id
        if ($user = $this->users()->find($id)) {
            return $user;
        }

        throw new NotFoundException([
            'key'  => 'user.notFound',
            'data' => [
                'name' => $id
            ]
        ]);

    },
    'users' => function () {
        return kirby()->users();
    }
];
