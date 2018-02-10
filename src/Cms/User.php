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
    protected $language = 'en_US';

    /**
     * @var string
     */
    protected $password;

    /**
     * The user role
     *
     * @var string
     */
    protected $role = 'visitor';

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

        return $this->blueprint = $this->store()->blueprint();
    }

    /**
     * Changes the user email address
     *
     * @param string $email
     * @return self
     */
    public function changeEmail(string $email): self
    {
        $this->rules()->changeEmail($this, $email);

        return $this->store()->changeEmail($email);
    }

    /**
     * Changes the user language
     *
     * @param string $language
     * @return self
     */
    public function changeLanguage(string $language): self
    {
        $this->rules()->changeLanguage($this, $language);

        return $this->store()->changeLanguage($language);
    }

    /**
     * Changes the user password
     *
     * @param string $password
     * @return self
     */
    public function changePassword(string $password): self
    {
        $this->rules()->changePassword($this, $password);

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
        $this->rules()->changeRole($this, $role);

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

    /**
     * @return self
     */
    public function create(): self
    {
        // stop if the user already exists
        if ($this->exists() === true) {
            throw new Exception('The user already exists');
        }

        // form validation
        $form = Form::for($this);
        $form->isValid();

        // rule validation
        $this->rules()->create($this, $form);

        // run the store action
        return $this->store()->create($form);
    }

    protected function defaultStore()
    {
        return UserStoreDefault::class;
    }

    /**
     * Deletes the user
     *
     * @return bool
     */
    public function delete(): bool
    {
        $this->rules()->delete($this);

        return $this->store()->delete();
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
        return $this->content()->get('name')->or($this->email())->value();
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
        $this->id    = sha1($this->email);

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
        $this->language = trim($language);
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
        if ($password !== null) {

            $info = password_get_info($password);

            if ($info['algo'] === 0) {
                $password = password_hash($password, PASSWORD_DEFAULT);
            }

        }

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
        $this->role = strtolower(trim($role));
        return $this;
    }

    /**
     * Compares the given password with the stored one
     *
     * @param string $password
     * @return boolean
     */
    public function validatePassword(string $password): bool
    {
        if (empty($this->password()) === true) {
            throw new Exception('The user has no password');
        }

        if (password_verify($password, $this->password()) !== true) {
            throw new Exception('Invalid password');
        }

        return true;
    }

}
