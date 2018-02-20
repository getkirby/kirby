<?php

namespace Kirby\Cms;

use Exception;

class UserStoreDefault extends Store
{

    /**
     * @return Avatar
     */
    public function avatar()
    {
        return new Avatar([
            'url'  => $this->media()->url($this->user()) . '/profile.jpg',
            'user' => $this->user(),
        ]);
    }

    /**
     * @return UserBlueprint|null
     */
    public function blueprint()
    {
        $root = $this->kirby()->root('blueprints') . '/users';

        try {
            return UserBlueprint::load($root . '/' . $this->user()->role() . '.yml', $this->user());
        } catch (Exception $e) {
            try {
                return UserBlueprint::load($root . '/default.yml', $this->user());
            } catch (Exception $e) {
                return null;
            }
        }
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

    public function create(array $values, Form $form)
    {
        throw new Exception('The user cannot be saved');
    }

    public function delete(): bool
    {
        throw new Exception('The user cannot be deleted');
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
        return 'en_US';
    }

    public function password()
    {
        return null;
    }

    public function role(): string
    {
        return 'visitor';
    }

    public function update(array $values, Form $form)
    {
        return $this->user()->clone([
            'content' => $form->stringValues()
        ]);
    }

    public function user()
    {
        return $this->model();
    }

}
