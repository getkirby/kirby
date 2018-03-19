<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Toolkit\V;

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

    use UserActions;

    use HasContent;
    use HasSiblings;
    use HasStore;

    /**
     * Those properties should be
     * converted to an array in User::toArray
     *
     * @var array
     */
    protected static $toArray = [
        'avatar',
        'content',
        'email',
        'id',
        'language',
        'role'
    ];

    /**
     * @var Avatar
     */
    protected $avatar;

    /**
     * @var UserBlueprint
     */
    protected $blueprint;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $language;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $password;

    /**
     * The user role
     *
     * @var string
     */
    protected $role;

    /**
     * Creates a new User object
     *
     * @param array $props
     */
    public function __construct(array $props)
    {
        $this->setProperties($props);
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
    public function blueprint()
    {
        if (is_a($this->blueprint, Blueprint::class) === true) {
            return $this->blueprint;
        }

        try {
            return $this->blueprint = UserBlueprint::factory('users/' . $this->role(), 'users/default', $this);
        } catch (Exception $e) {
            return $this->blueprint = new UserBlueprint([
                'model' => $this,
                'name'  => 'default',
                'title' => 'Default',
            ]);
        }
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

        return $this->collection = $this->kirby()->users();
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

    protected function defaultStore()
    {
        return UserStoreDefault::class;
    }

    /**
     * Returns the user email address
     *
     * @return string
     */
    public function email(): string
    {
        return $this->email;
    }

    /**
     * Checks if the user exists in the store
     *
     * @return boolean
     */
    public function exists(): bool
    {
        return $this->store()->exists();
    }

    /**
     * Returns the user id
     *
     * @return string
     */
    public function id(): string
    {
        if ($this->id !== null) {
            return $this->id;
        }

        return $this->id = sha1($this->email());
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
     * Checks if this user has the admin role
     *
     * @return boolean
     */
    public function isAdmin(): bool
    {
        return $this->role() === 'admin';
    }

    /**
     * Checks if the current user is this user
     *
     * @return boolean
     */
    public function isLoggedIn(): bool
    {
        return $this->is($this->kirby()->user());
    }

    /**
     * Checks if the user is the last one
     * with the admin role
     *
     * @return boolean
     */
    public function isLastAdmin(): bool
    {
        return $this->role() === 'admin' && $this->kirby()->users()->filterBy('role', 'admin')->count() <= 1;
    }

    /**
     * Checks if the user is the last user
     *
     * @return boolean
     */
    public function isLastUser(): bool
    {
        return $this->kirby()->users()->count() === 1;
    }

    /**
     * Returns the user language
     *
     * @return string
     */
    public function language(): string
    {
        return $this->language ?? $this->language = $this->store()->language();
    }

    /**
     * Returns the media url for the user object
     *
     * @return string
     */
    public function mediaUrl(): string
    {
        return $this->kirby()->media()->url($this);
    }

    /**
     * Returns the user's name
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name ?? $this->name = $this->content()->get('name')->or($this->email())->value();
    }

    /**
     * Returns the encrypted user password
     *
     * @return string
     */
    public function password()
    {
        return $this->password ?? $this->store()->password();
    }

    /**
     * Returns the user role
     *
     * @return string
     */
    public function role(): string
    {
        return $this->role ?? $this->role = $this->store()->role();
    }

    /**
     * Returns the UserRules class to
     * validate any important action.
     *
     * @return UserRules
     */
    protected function rules()
    {
        return new UserRules();
    }

    /**
     * Sets the user email
     *
     * @param string $email
     * @return self
     */
    protected function setEmail(string $email): self
    {
        $email = strtolower(trim($email));

        $this->email = $email;

        return $this;
    }

    /**
     * Sets the user language
     *
     * @param string $language
     * @return self
     */
    protected function setLanguage(string $language = null): self
    {
        $this->language = $language !== null ? trim($language) : null;
        return $this;
    }

    /**
     * Sets the user name
     *
     * @param string $name
     * @return self
     */
    protected function setName(string $name = null): self
    {
        $this->name = $name !== null ? trim($name) : null;
        return $this;
    }

    /**
     * Sets and hashes a new user password
     *
     * @param string $password
     * @return self
     */
    protected function setPassword(string $password = null): self
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Sets the user role
     *
     * @param string $role
     * @return self
     */
    protected function setRole(string $role = null): self
    {
        $this->role = $role !== null ? strtolower(trim($role)) : null;
        return $this;
    }

    /**
     * Compares the given password with the stored one
     *
     * @param string $password
     * @return boolean
     */
    public function validatePassword(string $password = null): bool
    {
        if (empty($this->password()) === true) {
            throw new Exception('The user has no password');
        }

        if ($password === null) {
            throw new Exception('Invalid password');
        }

        if (password_verify($password, $this->password()) !== true) {
            throw new Exception('Invalid password');
        }

        return true;
    }

    /**
     * Hashes user password
     */
    public function hashPassword($password)
    {
        if ($password !== null) {
            $info = password_get_info($password);

            if ($info['algo'] === 0) {
                $password = password_hash($password, PASSWORD_DEFAULT);
            }
        }

        return $password;
    }

}
