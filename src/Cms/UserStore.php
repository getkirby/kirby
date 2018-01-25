<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Data\Data;
use Kirby\FileSystem\Folder;

class UserStore
{

    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function avatar(): Avatar
    {
        return $this->avatar = new Avatar([
            'root' => $this->user->root() . '/profile.jpg',
            'url'  => App::instance()->media()->url($this->user),
            'user' => $this->user,
        ]);
    }

    /**
     * @return UserBlueprint|null
     */
    public function blueprint()
    {
        $root = App::instance()->root('blueprints') . '/users';

        try {
            return UserBlueprint::load($root . '/' . $this->user->role() . '.yml');
        } catch (Exception $e) {
            try {
                return UserBlueprint::load($root . '/default.yml');
            } catch (Exception $e) {
                return null;
            }
        }
    }

    public function changePassword(string $password)
    {
        return $this->write([
            'password' => $password
        ]);
    }

    public function changeRole(string $role)
    {
        return $this->write([
            'role' => $role
        ]);
    }

    public function content(): Content
    {
        $content = Data::read($this->user->root() . '/user.txt');
        return new Content($content, $this->user);
    }

    public function delete(): bool
    {
        App::instance()->media()->delete($user);

        $folder = new Folder($user->root());
        $folder->delete();

        return true;
    }

    public function update(array $content)
    {
        return $this->write($content);
    }

    protected function write(array $content)
    {
        // always hash passwords
        if (isset($content['password'])) {
            $info = password_get_info($content['password']);

            if ($info['algo'] === 0) {
                $content['password'] = password_hash($content['password'], PASSWORD_DEFAULT);
            }
        }

        $content = $this->user->content()->update($content);

        Data::write($this->user->root() . '/user.txt', $content->toArray());

        return $this->user->setContent($content);
    }


}
