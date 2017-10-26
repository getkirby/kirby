<?php

use Kirby\Cms\Avatar;
use Kirby\Cms\Content;
use Kirby\Cms\User;
use Kirby\Data\Data;
use Kirby\FileSystem\Folder;
use Kirby\Toolkit\V;

return [
    'user.change.password' => function (User $user, string $password): User {
        return $user->store()->commit('user.write', $user, [
            'password' => $password
        ]);
    },
    'user.change.role' => function (User $user, string $role): User {
        return $user->store()->commit('user.write', $user, [
            'role' => $role
        ]);
    },
    'user.create' => function (array $content): User {

        if (V::email($content['email'] ?? null) === false) {
            throw new Exception('Invalid email address');
        }

        // create the user object + avatar
        $user = new User([
            'id'   => $id   = $content['email'],
            'root' => $root = $this->root('accounts') . '/' . $id,
        ]);

        // create the folder
        $folder = new Folder($user->root());
        $folder->make(true);

        // add the content
        $user->update($content);

        return $user;

    },
    'user.delete' => function (User $user): bool {

        $this->media()->delete($user);

        $folder = new Folder($user->root());
        $folder->delete();

        return true;

    },
    'user.content' => function (User $user): Content {

        $content = Data::read($user->root() . '/user.txt');

        return new Content($content, $user);

    },
    'user.update' => function (User $user, array $content): User {
        return $user->store()->commit('user.write', $user, $content);
    },
    'user.write' => function (User $user, array $content): User {

        // always hash passwords
        if (isset($content['password'])) {
            $info = password_get_info($content['password']);

            if ($info['algo'] === 0) {
                $content['password'] = password_hash($content['password'], PASSWORD_DEFAULT);
            }
        }

        $content = $user->content()->update($content);

        Data::write($user->root() . '/user.txt', $content->toArray());

        return $user->set('content', $content);

    }
];
