<?php

namespace Kirby\Cms;

use Exception;

/**
 * The User class represents
 * panel users as well as frontend users.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 */
class User extends Model
{

    use HasContent;
    use HasSiblings;

    /**
     * Those properties should be
     * converted to an array in User::toArray
     *
     * @var array
     */
    protected static $toArray = [
        'avatar',
        'content',
        'hash',
        'id',
        'language',
        'root',
        'role'
    ];

    /**
     * The user's avatar object
     *
     * @var Avatar
     */
    protected $avatar;

    /**
     * The UserBlueprint object
     *
     * @var UserBlueprint
     */
    protected $blueprint;

    /**
     * The user id
     *
     * @var string
     */
    protected $id;

    /**
     * The absolute path to the user directory
     *
     * @var string
     */
    protected $root;

    /**
     * Creates a new User object
     *
     * @param array $props
     */
    public function __construct(array $props)
    {
        $this->setRequiredProperties($props, ['id']);
        $this->setOptionalProperties($props, ['avatar', 'blueprint', 'collection', 'content', 'root']);
    }

    /**
     * Returns the Avatar object
     *
     * @return Avatar
     */
    public function avatar(): Avatar
    {
        if (is_a($this->avatar, Avatar::class) === true) {
            return $this->avatar;
        }

        return $this->avatar = $this->store()->avatar();
    }

    /**
     * Returns the UserBlueprint object
     *
     * @return UserBlueprint
     */
    public function blueprint(): UserBlueprint
    {
        if (is_a($this->blueprint, Blueprint::class) === true) {
            return $this->blueprint;
        }

        return $this->blueprint = $this->store()->blueprint();
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

        return $this->store()->changePassword($password);
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

        return $this->store()->changeRole($role);
    }

    /**
     * Returns the parent Users collection
     *
     * @return Users
     */
    public function collection(): Users
    {
        if (is_a($this->collection, Users::class) === true) {
            return $this->collection;
        }

        return $this->collection = App::instance()->users();
    }

    /**
     * Prepares the avatar object for the
     * User::toArray method
     *
     * @return array
     */
    protected function convertAvatarToArray(): array
    {
        return $this->avatar()->toArray();
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

        return App::instance()->component('UsersStore')->create($content);
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

        return $this->store()->delete();
    }

    /**
     * Returns the User's content
     *
     * @return Content
     */
    public function content(): Content
    {
        if (is_a($this->content, Content::class) === true) {
            return $this->content;
        }

        return $this->store()->content();
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
     * Returns the user id
     *
     * @return string
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * Compares the current object with the given user object
     *
     * @param User $user
     * @return bool
     */
    public function is(User $user): bool
    {
        return $this->id() === $user->id();
    }

    /**
     * Returns the user language
     *
     * @return string
     */
    public function language(): string
    {
        $language = $this->content()->get('language')->toString();
        return empty($language) === false ? $language : 'en';
    }

    /**
     * Returns the user role
     *
     * @return string
     */
    public function role(): string
    {
        $role = $this->content()->get('role')->toString();
        return empty($role) === false ? $role : 'visitor';
    }

    /**
     * Returns the user directory root
     *
     * @return string|null
     */
    public function root()
    {
        return $this->root;
    }

    /**
     * Sets the parent avatar object
     *
     * @param Avatar $avatar
     * @return self
     */
    protected function setAvatar(Avatar $avatar = null): self
    {
        $this->avatar = $avatar;
        $this->avatar->setUser($this);
        return $this;
    }

    /**
     * Sets the blueprint object
     *
     * @param UserBlueprint $blueprint
     * @return self
     */
    protected function setBlueprint(UserBlueprint $blueprint = null): self
    {
        $this->blueprint = $blueprint;
        return $this;
    }

    /**
     * Sets the user id
     *
     * @param string $id
     * @return self
     */
    protected function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Sets the user directory root
     *
     * @param string $root
     * @return self
     */
    protected function setRoot(string $root = null): self
    {
        $this->root = $root;
        return $this;
    }

    /**
     * @return UserStore
     */
    protected function store(): UserStore
    {
        return App::instance()->component('UserStore', $this);
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

        return $this->store()->update($content);
    }

}
