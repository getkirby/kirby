<?php

namespace Kirby\Users;

use Kirby\Collection\Collection;

class Users extends Collection
{

    public function indexOf($user)
    {
        return array_search($user->id(), $this->keys());
    }

    public function __set(string $id, $user)
    {

        if (is_array($user)) {
            $user = new User($user);
        }

        if (!is_a($user, User::class)) {
            throw new Exception('Invalid User object in Users collection');
        }

        $user->collection($this);

        return parent::__set($user->id(), $user);

    }

    public function getAttribute($item, $attribute)
    {
        return (string)$item->$attribute();
    }

}
