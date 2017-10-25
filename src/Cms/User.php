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

    public static function create(array $content = []): self
    {
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
        return $this->store()->commit('user.update', $this, $content);
    }

    public function delete(): bool
    {
        return $this->store()->commit('user.delete', $this);
    }

}
