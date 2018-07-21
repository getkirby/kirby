<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Exception\PermissionException;
use Kirby\Session\Session;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;
use Throwable;

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

    /**
     * @var Avatar
     */
    protected $avatar;

    /**
     * @var UserBlueprint
     */
    protected $blueprint;

    /**
     * @var array
     */
    protected $data;

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
     * Improved var_dump() output
     *
     * @return array
     */
    public function __debuginfo(): array
    {
        return array_merge($this->toArray(), [
            'avatar'  => $this->avatar(),
            'content' => $this->content(),
            'role'    => $this->role()
        ]);
    }

    /**
     * Returns the Avatar object
     *
     * @return Avatar
     */
    public function avatar(): Avatar
    {
        return $this->avatar = $this->avatar ?? new Avatar([
            'url'   => $this->mediaUrl() . '/profile.jpg',
            'user'  => $this
        ]);
    }

    /**
     * Returns the UserBlueprint object
     *
     * @return UserBlueprint
     */
    public function blueprint()
    {
        if (is_a($this->blueprint, 'Kirby\Cms\Blueprint') === true) {
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
        if (is_a($this->collection, 'Kirby\Cms\Users') === true) {
            return $this->collection;
        }

        return $this->collection = $this->kirby()->users();
    }

    /**
     * Returns the content
     *
     * @return Content
     */
    public function content(): Content
    {
        if ($this->content !== null) {
            return $this->content;
        }

        $data = $this->data();

        // remove unwanted stuff from the content object
        unset($data['email']);
        unset($data['language']);
        unset($data['password']);
        unset($data['role']);

        return $this->setContent($data)->content();
    }

    /**
     * Returns the absolute path to the user content file
     *
     * @return string
     */
    public function contentFile(): string
    {
        return $this->root() . '/user.txt';
    }

    /**
     * Reads all user data from disk
     *
     * @return array
     */
    protected function data(): array
    {
        if ($this->data !== null) {
            return $this->data;
        }

        try {
            return $this->data = Data::read($this->contentFile());
        } catch (Throwable $e) {
            return $this->data = [];
        }
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
     * Returns all content validation errors
     *
     * @return array
     */
    public function errors(): array
    {
        $errors = [];

        foreach ($this->blueprint()->sections() as $section) {
            $errors = array_merge($errors, $section->errors());
        }

        return $errors;
    }

    /**
     * Checks if the user exists
     *
     * @return boolean
     */
    public function exists(): bool
    {
        return is_file($this->root() . '/user.txt') === true;
    }

    /**
     * Hashes user password
     *
     * @param string|null $password
     * @return string|null
     */
    public static function hashPassword($password)
    {
        if ($password !== null) {
            $info = password_get_info($password);

            if ($info['algo'] === 0) {
                $password = password_hash($password, PASSWORD_DEFAULT);
            }
        }

        return $password;
    }

    /**
     * Returns the user id
     *
     * @return string
     */
    public function id(): string
    {
        return $this->id = $this->id ?? sha1($this->email());
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
        return $this->role()->isAdmin() === true && $this->kirby()->users()->filterBy('role', 'admin')->count() <= 1;
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
        return $this->language ?? $this->language = $this->data()['language'] ?? 'en';
    }

    /**
     * Logs the user in
     *
     * @param string $password
     * @param Session|array $session Session options or session object to set the user in
     * @return void
     */
    public function login(string $password, $session = null)
    {
        if ($this->role()->permissions()->for('access', 'panel') === false) {
            throw new PermissionException(['key' => 'access.panel']);
        }

        if ($this->validatePassword($password) !== true) {
            throw new PermissionException(['key' => 'access.login']);
        }

        $this->loginPasswordless($session);
    }

    /**
     * Logs the user in without checking the password
     *
     * @param Session|array $session Session options or session object to set the user in
     * @return void
     */
    public function loginPasswordless($session = null)
    {
        $session = $this->sessionFromOptions($session);

        $session->regenerateToken(); // privilege change
        $session->data()->set('user.id', $this->id());
    }

    /**
     * Logs the user out
     *
     * @param Session|array $session Session options or session object to unset the user in
     * @return void
     */
    public function logout($session = null)
    {
        $session = $this->sessionFromOptions($session);

        $session->data()->remove('user.id');

        if ($session->data()->get() === []) {
            // session is now empty, we might as well destroy it
            $session->destroy();
        } else {
            // privilege change
            $session->regenerateToken();
        }
    }

    /**
     * Returns the root to the media folder for the user
     *
     * @return string
     */
    public function mediaRoot(): string
    {
        return $this->kirby()->root('media') . '/users/' . $this->id();
    }

    /**
     * Returns the media url for the user object
     *
     * @return string
     */
    public function mediaUrl(): string
    {
        return $this->kirby()->url('media') . '/users/' . $this->id();
    }

    /**
     * Returns the user's name
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->name = $this->name ?? $this->data()['name'] ?? null;
    }

    /**
     * Returns the username
     * which is the given name or the email
     * as a fallback
     *
     * @return string
     */
    public function username(): string
    {
        return empty($this->name()) ? $this->email() : $this->name();
    }

    /**
     * Returns the url to the editing view
     * in the panel
     *
     * @return string
     */
    public function panelUrl(): string
    {
        return $this->kirby()->url('panel') . '/users/' . $this->id();
    }

    /**
     * Returns the encrypted user password
     *
     * @return string|null
     */
    public function password(): ?string
    {
        return $this->password = $this->password ?? $this->data()['password'] ?? null;
    }

    /**
     * @return UserBlueprintOptions
     */
    public function permissions(): UserBlueprintOptions
    {
        return $this->blueprint()->options();
    }

    /**
     * Returns the user role
     *
     * @return string
     */
    public function role(): Role
    {
        if (is_a($this->role, 'Kirby\Cms\Role') === true) {
            return $this->role;
        }

        $roleName = $this->role ?? $this->data()['role'] ?? 'visitor';

        if ($role = $this->kirby()->roles()->find($roleName)) {
            return $this->role = $role;
        }

        return $this->role = Role::nobody();
    }

    /**
     * The absolute path to the user directory
     *
     * @return string
     */
    public function root(): string
    {
        return $this->kirby()->root('accounts') . '/' . $this->email();
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
        $this->email = strtolower(trim($email));
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
            throw new NotFoundException(['key' => 'user.password.undefined']);
        }

        if ($password === null) {
            throw new InvalidArgumentException(['key' => 'user.password.invalid']);
        }

        if (password_verify($password, $this->password()) !== true) {
            throw new InvalidArgumentException(['key' => 'user.password.invalid']);
        }

        return true;
    }

    /**
     * Converts session options into a session object
     *
     * @param Session|array $session Session options or session object to unset the user in
     * @return Session
     */
    protected function sessionFromOptions($session): Session
    {
        // use passed session options or session object if set
        if (is_array($session) === true) {
            $session = $this->kirby()->session($session);
        } elseif (is_a($session, 'Kirby\Session\Session') === false) {
            $session = $this->kirby()->session(['detect' => true]);
        }

        return $session;
    }

    /**
     * Converts the most important user properties
     * to an array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'avatar'   => $this->avatar()->toArray(),
            'content'  => $this->content()->toArray(),
            'email'    => $this->email(),
            'id'       => $this->id(),
            'language' => $this->language(),
            'role'     => $this->role()->name(),
            'username' => $this->username()
        ];
    }

    /**
     * String template builder
     *
     * @param string|null $template
     * @return string
     */
    public function toString(string $template = null): string
    {
        if ($template === null) {
            return $this->email();
        }

        return Str::template($template, [
            'user'  => $this,
            'site'  => $this->site(),
            'kirby' => $this->kirby()
        ]);
    }
}
