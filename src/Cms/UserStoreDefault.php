<?php

namespace Kirby\Cms;

use Kirby\Exception\LogicException;

class UserStoreDefault extends Store
{

    /**
     * @return Avatar
     */
    public function avatar()
    {
        return new Avatar([
            'url'  => $this->user()->mediaUrl() . '/profile.jpg',
            'user' => $this->user(),
        ]);
    }

    public function changeEmail(string $email)
    {
        return $this->user()->clone([
            'email' => $email
        ]);
    }

    public function changeLanguage(string $language)
    {
        return $this->user()->clone([
            'language' => $language
        ]);
    }

    public function changeName(string $name)
    {
        return $this->user()->clone([
            'name' => $name
        ]);
    }

    public function changePassword(string $password)
    {
        return $this->user()->clone([
            'password' => $password
        ]);
    }

    public function changeRole(string $role)
    {
        return $this->user()->clone([
            'role' => $role
        ]);
    }

    public function content(): array
    {
        return [];
    }

    public function create(User $user)
    {
        return $user;
    }

    public function delete(): bool
    {
        throw new LogicException([
            'key'  => 'user.delete',
            'data' => ['name' => $this->user()->name()]
        ]);
    }

    public function exists(): bool
    {
        return false;
    }

    public function id()
    {
        return $this->user()->id();
    }

    public function language(): string
    {
        return 'en';
    }

    public function password()
    {
        return null;
    }

    public function role(): string
    {
        return 'visitor';
    }

    public function update(array $values = [], array $strings = [])
    {
        return $this->user()->clone([
            'content' => $this->user()->content()->update($strings)->toArray()
        ]);
    }

    public function user()
    {
        return $this->model();
    }
}
