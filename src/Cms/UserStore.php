<?php

namespace Kirby\Cms;

use Kirby\Util\Dir;

use Kirby\Exception\DuplicateException;
use Kirby\Exception\LogicException;

class UserStore extends UserStoreDefault
{
    protected $base;
    protected $data;

    /**
     * @return Avatar
     */
    public function avatar()
    {
        return new Avatar([
            'url'   => $this->media()->url($this->user()) . '/profile.jpg',
            'user'  => $this->user(),
            'store' => AvatarStore::class
        ]);
    }

    public function base()
    {
        if (is_a($this->base, Base::class) === true) {
            return $this->base;
        }

        return $this->base = new Base([
            'root'      => $this->root(),
            'extension' => 'txt',
            'type'      => 'user'
        ]);
    }

    public function changeEmail(string $email)
    {
        $user = parent::changeEmail($email);

        if ($this->exists() === false) {
            return $user;
        }

        $this->media()->delete($this->user());

        $oldRoot = $this->root();
        $newRoot = dirname($this->root()) . '/' . $user->email();

        if (is_dir($newRoot) === true) {
            throw new DuplicateException([
                'key'  => 'user.duplicate',
                'data' => ['email' => $email]
            ]);
        }

        if (Dir::move($oldRoot, $newRoot) !== true) {
            throw new LogicException('The user directory for "' . $email . '" could not be moved');
        }

        return $this->save($user);
    }

    public function changeLanguage(string $language)
    {
        $user = parent::changeLanguage($language);

        if ($this->exists() === false) {
            return $user;
        }

        // save the user
        return $this->save($user);
    }

    public function changeName(string $name)
    {
        $user = parent::changeName($name);

        if ($this->exists() === false) {
            return $user;
        }

        // save the user
        return $this->save($user);
    }

    public function changePassword(string $password)
    {
        $user = parent::changePassword($password);

        if ($this->exists() === false) {
            return $user;
        }

        // save the user
        return $this->save($user);
    }

    public function changeRole(string $role)
    {
        $user = parent::changeRole($role);

        if ($this->exists() === false) {
            return $user;
        }

        // save the user
        return $this->save($user);
    }

    public function content(): array
    {
        $data = $this->data();

        // remove unwanted stuff from the content object
        unset($data['email']);
        unset($data['language']);
        unset($data['password']);
        unset($data['role']);

        return $data;
    }

    public function create(User $user)
    {
        // try to create the directory
        if (Dir::make($this->root()) !== true) {
            throw new LogicException('The user directory for "' . $user->email() . '" could not be created');
        }

        // create an empty storage file
        touch($this->root() . '/user.txt');

        // write the user data
        return $this->save($user);
    }

    public function data()
    {
        if (is_array($this->data) === true) {
            return $this->data;
        }

        return $this->data = $this->base()->read();
    }

    public function delete(): bool
    {
        if ($this->exists() === false) {
            return true;
        }

        // delete all public assets for this user
        $this->media()->delete($this->user());

        // delete the user directory
        if (Dir::remove($this->root()) !== true) {
            throw new LogicException('The user directory for "' . $this->user()->email() . '" could not be deleted');
        }

        return true;
    }

    public function exists(): bool
    {
        return is_dir($this->root()) === true && file_exists($this->base()->storage()) === true;
    }

    public function id()
    {
        return $this->root();
    }

    public function language(): string
    {
        return $this->data()['language'] ?? parent::language();
    }

    public function password()
    {
        return $this->data()['password'] ?? null;
    }

    public function role(): string
    {
        return $this->data()['role'] ?? parent::role();
    }

    public function root(): string
    {
        return $this->kirby()->root('accounts') . '/' . $this->user()->email();
    }

    public function update(array $values = [], array $strings = [])
    {
        $user = parent::update($values, $strings);

        if ($this->exists() === false) {
            return $user;
        }

        return $this->save($user);
    }

    public function save(User $user): User
    {
        $content = $user->content()->toArray();

        // store main information in the content file
        $content['email']    = $user->email();
        $content['language'] = $user->language();
        $content['name']     = $user->name();
        $content['password'] = $user->hashPassword($user->password());
        $content['role']     = $user->role();

        if ($this->base()->write($content) !== true) {
            throw new LogicException('The user information for "' . $user->email() . '" could not be saved');
        }

        return $user;
    }

    public function user()
    {
        return $this->model();
    }
}
