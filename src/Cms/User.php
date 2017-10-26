<?php

namespace Kirby\Cms;

class User extends Object
{

    use HasContent;
    use HasSiblings;

    public function __construct(array $props = []) {

        parent::__construct($props, [
            'avatar' => [
                'type'    => Avatar::class,
                'default' => function () {
                    return new Avatar([
                        'root' => $this->root() . '/profile.jpg',
                        'url'  => $this->plugin('media')->url($this) . '/profile.jpg',
                        'user' => $this,
                    ]);
                }
            ],
            'collection' => [
                'type'    => Users::class,
                'default' => function () {
                    return $this->store()->commit('users');
                }
            ],
            'content' => [
                'type'    => Content::class,
                'default' => function () {
                    return $this->store()->commit('user.content', $this);
                }
            ],
            'id' => [
                'type'     => 'string',
                'required' => true
            ],
            'root' => [
                'type' => 'string'
            ],
        ]);

    }

    public function changePassword(string $password): self
    {
        $this->rules()->check('user.change.password', $this, $password);
        $this->perms()->check('user.change.password', $this, $password);

        return $this->store()->commit('user.change.password', $this, $password);
    }

    public function changeRole(string $role): self
    {
        $this->rules()->check('user.change.role', $this, $role);
        $this->perms()->check('user.change.role', $this, $role);

        return $this->store()->commit('user.change.role', $this, $role);
    }

    public static function create(array $content = []): self
    {
        static::rules()->check('user.create', $content);
        static::perms()->check('user.create', $content);

        return static::store()->commit('user.create', $content);
    }

    public function hash(): string
    {
        return sha1($this->id());
    }

    public function language(): string
    {
        return $this->content()->get('language')->or('en')->toString();
    }

    public function role(): string
    {
        return $this->content()->get('role')->or('visitor')->toString();
    }

    public function update(array $content = []): self
    {
        $this->rules()->check('user.update', $this, $content);
        $this->perms()->check('user.update', $this, $content);

        return $this->store()->commit('user.update', $this, $content);
    }

    public function delete(): bool
    {
        $this->rules()->check('user.delete', $this);
        $this->perms()->check('user.delete', $this);

        return $this->store()->commit('user.delete', $this);
    }

}
