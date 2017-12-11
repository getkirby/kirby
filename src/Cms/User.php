<?php

namespace Kirby\Cms;

/**
 * The User class represents
 * panel users as well as frontend users.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 */
class User extends Object
{

    use HasContent;
    use HasSiblings;

    /**
     * Creates a new User object
     *
     * @param array $props
     */
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
            'store' => [
                'type'    => Store::class,
                'default' => function () {
                    return $this->plugin('store');
                }
            ],
        ]);

    }

    /**
     * Changes the user password
     *
     * @param string $password
     * @return self
     */
    public function changePassword(string $password): self
    {
        $this->rules()->check('user.change.password', $this, $password);
        $this->perms()->check('user.change.password', $this, $password);

        return $this->store()->commit('user.change.password', $this, $password);
    }

    /**
     * Changes the user role
     *
     * @param string $role
     * @return self
     */
    public function changeRole(string $role): self
    {
        $this->rules()->check('user.change.role', $this, $role);
        $this->perms()->check('user.change.role', $this, $role);

        return $this->store()->commit('user.change.role', $this, $role);
    }

    /**
     * Static method to create new Users and
     * return the User object
     *
     * @param  array $content
     * @return self
     */
    public static function create(array $content = []): self
    {
        static::rules()->check('user.create', $content);
        static::perms()->check('user.create', $content);

        return static::store()->commit('user.create', $content);
    }

    /**
     * Returns the hashed id
     * This is being used to create
     * media Urls without exposing
     * the email address for example
     *
     * @return string
     */
    public function hash(): string
    {
        return sha1($this->id());
    }

    /**
     * Returns the user language
     *
     * @return string
     */
    public function language(): string
    {
        return $this->content()->get('language')->or('en')->toString();
    }

    /**
     * Returns the user role
     *
     * @return string
     */
    public function role(): string
    {
        return $this->content()->get('role')->or('visitor')->toString();
    }

    /**
     * Updates User data
     *
     * @param array $content
     * @return self
     */
    public function update(array $content = []): self
    {
        $this->rules()->check('user.update', $this, $content);
        $this->perms()->check('user.update', $this, $content);

        return $this->store()->commit('user.update', $this, $content);
    }

    /**
     * Deletes the user
     *
     * @return bool
     */
    public function delete(): bool
    {
        $this->rules()->check('user.delete', $this);
        $this->perms()->check('user.delete', $this);

        return $this->store()->commit('user.delete', $this);
    }

}
