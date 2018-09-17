<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Exception\PermissionException;
use Kirby\Session\Session;
use Kirby\Toolkit\F;
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
class User extends ModelWithContent
{
    use UserActions;
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
     * Modified getter to also return fields
     * from the content
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $method, array $arguments = [])
    {
        // public property access
        if (isset($this->$method) === true) {
            return $this->$method;
        }

        // return site content otherwise
        return $this->content()->get($method, $arguments);
    }

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
     * Returns the url to the api endpoint
     *
     * @param bool $relative
     * @return string
     */
    public function apiUrl(bool $relative = false): string
    {
        if ($relative === true) {
            return 'users/' . $this->id();
        } else {
            return $this->kirby()->url('api') . '/users/' . $this->id();
        }
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
     * @param string|null $languageCode
     * @return Content
     */
    public function content(?string $languageCode = null): Content
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
     * Prepares the content for the text file
     *
     * @return array
     */
    public function contentFileData(): array
    {
        $content = $this->content()->toArray();

        // store main information in the content file
        $content['language'] = $this->language();
        $content['name']     = $this->name();
        $content['password'] = $this->hashPassword($this->password());
        $content['role']     = $this->role()->id();

        // remove the email. It's already stored in the directory
        unset($content['email']);

        return $content;
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
     * Checks if the current user is the virtual
     * Kirby user
     *
     * @return boolean
     */
    public function isKirby(): bool
    {
        return $this->email() === 'kirby@getkirby.com';
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
     * @return bool
     */
    public function login(string $password, $session = null): bool
    {
        if ($this->role()->permissions()->for('access', 'panel') === false) {
            throw new PermissionException(['key' => 'access.panel']);
        }

        if ($this->validatePassword($password) !== true) {
            throw new PermissionException(['key' => 'access.login']);
        }

        $this->loginPasswordless($session);

        return true;
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
     * Returns the last modification date of the user
     *
     * @param string $format
     * @param string|null $handler
     * @return int|string
     */
    public function modified(string $format = 'U', string $handler = null)
    {
        return F::modified($this->contentFile(), $format, $handler ?? $this->kirby()->option('date.handler', 'date'));
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
     * Create a dummy nobody
     *
     * @return self
     */
    public static function nobody(): self
    {
        return new static([
            'email' => 'nobody@getkirby.com',
            'role'  => 'nobody'
        ]);
    }

    /**
     * Returns the url to the editing view
     * in the panel
     *
     * @param bool $relative
     * @return string
     */
    public function panelUrl(bool $relative = false): string
    {
        if ($relative === true) {
            return '/users/' . $this->id();
        } else {
            return $this->kirby()->url('panel') . '/users/' . $this->id();
        }
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
     * @return UserPermissions
     */
    public function permissions()
    {
        return new UserPermissions($this);
    }

    /**
     * Creates a string query, starting from the model
     *
     * @param string|null $query
     * @param string|null $expect
     * @return mixed
     */
    public function query(string $query = null, string $expect = null)
    {
        if ($query === null) {
            return null;
        }

        $result = Str::query($query, [
            'kirby' => $kirby = $this->kirby(),
            'site'  => $kirby->site(),
            'user'  => $this
        ]);

        if ($expect !== null && is_a($result, $expect) !== true) {
            return null;
        }

        return $result;
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
     * Sets the Blueprint object
     *
     * @param array|null $blueprint
     * @return self
     */
    protected function setBlueprint(array $blueprint = null): self
    {
        if ($blueprint !== null) {
            $blueprint['model'] = $this;
            $this->blueprint = new UserBlueprint($blueprint);
        }

        return $this;
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
}
