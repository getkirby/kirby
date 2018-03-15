<?php

use Firebase\JWT\JWT;

/**
 * Api Data Definitions
 */
return [
    'token' => function () {
        return $this->kirby()->authToken();
    },
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

        $parentId = dirname($id);
        $draftId  = basename($id);
        $parent   = $parentId === '.' ? $this->site() : $this->site()->find($parentId);

        if ($parent && $draft = $parent->drafts()->find($draftId)) {
            return $draft;
        }

        throw new Exception(sprintf('The page "%s" cannot be found', $id));

    },
    'site' => function () {
        return $this->kirby()->site();
    },
    'user' => function (string $id = null) {

        // get the authenticated user
        if ($id === null) {
            return $this->users()->findBy('id', $this->token()['uid']);
        }

        // get a specific user by id
        if ($user = $this->users()->find($id)) {
            return $user;
        }

        throw new Exception(sprintf('The user "%s" cannot be found', $id));

    },
    'users' => function () {
        return kirby()->users();
    }
];
