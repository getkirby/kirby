<?php

/**
 * Api Data Definitions
 */
return [
    'file' => function (string $id = null, string $filename) {

        $parent = $id === null ? $this->site() : $this->page($id);

        if ($file = $parent->file($filename)) {
            return $file;
        }

        throw new Exception(sprintf('The file "%s" cannot be found', $filename));

    },
    'kirby' => function () {
        return kirby();
    },
    'page' => function (string $id) {

        $id = str_replace('+', '/', $id);

        if ($page = $this->site()->find($id)) {
            return $page;
        }

        throw new Exception(sprintf('The page "%s" cannot be found', $id));

    },
    'site' => function () {
        return $this->kirby()->site();
    },
    'user' => function (string $id) {

        if ($user = $this->users()->find($id)) {
            return $user;
        }

        throw new Exception(sprintf('The user "%s" cannot be found', $id));

    },
    'users' => function () {
        return kirby()->users();
    }
];
