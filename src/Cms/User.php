<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Str;

/**
 * The `$user` object represents a
 * single Panel or frontend user.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class User extends ModelWithContent
{
    const CLASS_ALIAS = 'user';

    use HasFiles;
    use HasMethods;
    use HasSiblings;
    use UserActions;

    /**
     * @var UserBlueprint
     */
    protected $blueprint;

    /**
     * @var array
     */
    protected $credentials;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $hash;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var array|null
     */
    protected $inventory;

    /**
     * @var string
     */
    protected $language;

    /**
     * All registered user methods
     *
     * @var array
     */
    public static $methods = [];

    /**
     * Registry with all User models
     *
     * @var array
     */
    public static $models = [];

    /**
     * @var \Kirby\Cms\Field
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

        // user methods
        if ($this->hasMethod($method)) {
            return $this->callMethod($method, $arguments);
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
        $props['id'] = $props['id'] ?? $this->createId();
        $this->setProperties($props);
    }

    /**
     * Improved `var_dump` output
     *
     * @return array
     */
    public function __debugInfo(): array
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
     * @internal
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
     * Returns the File object for the avatar or null
     *
     * @return \Kirby\Cms\File|null
     */
    public function avatar()
    {
        return $this->files()->template('avatar')->first();
    }

    /**
     * Returns the UserBlueprint object
     *
     * @return \Kirby\Cms\Blueprint
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
     * Prepares the content for the write method
     *
     * @internal
     * @param array $data
     * @param string $languageCode|null Not used so far
     * @return array
     */
    public function contentFileData(array $data, string $languageCode = null): array
    {
        // remove stuff that has nothing to do in the text files
        unset(
            $data['email'],
            $data['language'],
            $data['name'],
            $data['password'],
            $data['role']
        );

        return $data;
    }

    /**
     * Filename for the content file
     *
     * @internal
     * @return string
     */
    public function contentFileName(): string
    {
        return 'user';
    }

    protected function credentials(): array
    {
        return $this->credentials = $this->credentials ?? $this->readCredentials();
    }

    /**
     * Returns the user email address
     *
     * @return string
     */
    public function email(): ?string
    {
        return $this->email = $this->email ?? $this->credentials()['email'] ?? null;
    }

    /**
     * Checks if the user exists
     *
     * @return bool
     */
    public function exists(): bool
    {
        return is_file($this->contentFile('default')) === true;
    }

    /**
     * Constructs a User object and also
     * takes User models into account.
     *
     * @internal
     * @param mixed $props
     * @return self
     */
    public static function factory($props)
    {
        if (empty($props['model']) === false) {
            return static::model($props['model'], $props);
        }

        return new static($props);
    }

    /**
     * Hashes the user's password unless it is `null`,
     * which will leave it as `null`
     *
     * @internal
     * @param string|null $password
     * @return string|null
     */
    public static function hashPassword($password): ?string
    {
        if ($password !== null) {
            $password = password_hash($password, PASSWORD_DEFAULT);
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
        return $this->id;
    }

    /**
     * Returns the inventory of files
     * children and content files
     *
     * @return array
     */
    public function inventory(): array
    {
        if ($this->inventory !== null) {
            return $this->inventory;
        }

        $kirby = $this->kirby();

        return $this->inventory = Dir::inventory(
            $this->root(),
            $kirby->contentExtension(),
            $kirby->contentIgnore(),
            $kirby->multilang()
        );
    }

    /**
     * Compares the current object with the given user object
     *
     * @param \Kirby\Cms\User|null $user
     * @return bool
     */
    public function is(User $user = null): bool
    {
        if ($user === null) {
            return false;
        }

        return $this->id() === $user->id();
    }

    /**
     * Checks if this user has the admin role
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role()->id() === 'admin';
    }

    /**
     * Checks if the current user is the virtual
     * Kirby user
     *
     * @return bool
     */
    public function isKirby(): bool
    {
        return $this->email() === 'kirby@getkirby.com';
    }

    /**
     * Checks if the current user is this user
     *
     * @return bool
     */
    public function isLoggedIn(): bool
    {
        return $this->is($this->kirby()->user());
    }

    /**
     * Checks if the user is the last one
     * with the admin role
     *
     * @return bool
     */
    public function isLastAdmin(): bool
    {
        return $this->role()->isAdmin() === true &&
               $this->kirby()->users()->filter('role', 'admin')->count() <= 1;
    }

    /**
     * Checks if the user is the last user
     *
     * @return bool
     */
    public function isLastUser(): bool
    {
        return $this->kirby()->users()->count() === 1;
    }

    /**
     * Checks if the current user is the virtual
     * Nobody user
     *
     * @return bool
     */
    public function isNobody(): bool
    {
        return $this->email() === 'nobody@getkirby.com';
    }

    /**
     * Returns the user language
     *
     * @return string
     */
    public function language(): string
    {
        return $this->language ?? $this->language = $this->credentials()['language'] ?? $this->kirby()->option('panel.language', 'en');
    }

    /**
     * Logs the user in
     *
     * @param string $password
     * @param \Kirby\Session\Session|array|null $session Session options or session object to set the user in
     * @return bool
     */
    public function login(string $password, $session = null): bool
    {
        $this->validatePassword($password);
        $this->loginPasswordless($session);

        return true;
    }

    /**
     * Logs the user in without checking the password
     *
     * @param \Kirby\Session\Session|array|null $session Session options or session object to set the user in
     * @return void
     */
    public function loginPasswordless($session = null): void
    {
        $kirby = $this->kirby();

        $session = $this->sessionFromOptions($session);

        $kirby->trigger('user.login:before', ['user' => $this, 'session' => $session]);

        $session->regenerateToken(); // privilege change
        $session->data()->set('kirby.userId', $this->id());
        $this->kirby()->auth()->setUser($this);

        $kirby->trigger('user.login:after', ['user' => $this, 'session' => $session]);
    }

    /**
     * Logs the user out
     *
     * @param \Kirby\Session\Session|array|null $session Session options or session object to unset the user in
     * @return void
     */
    public function logout($session = null): void
    {
        $kirby   = $this->kirby();
        $session = $this->sessionFromOptions($session);

        $kirby->trigger('user.logout:before', ['user' => $this, 'session' => $session]);

        // remove the user from the session for future requests
        $session->data()->remove('kirby.userId');

        // clear the cached user object from the app state of the current request
        $this->kirby()->auth()->flush();

        if ($session->data()->get() === []) {
            // session is now empty, we might as well destroy it
            $session->destroy();

            $kirby->trigger('user.logout:after', ['user' => $this, 'session' => null]);
        } else {
            // privilege change
            $session->regenerateToken();

            $kirby->trigger('user.logout:after', ['user' => $this, 'session' => $session]);
        }
    }

    /**
     * Returns the root to the media folder for the user
     *
     * @internal
     * @return string
     */
    public function mediaRoot(): string
    {
        return $this->kirby()->root('media') . '/users/' . $this->id();
    }

    /**
     * Returns the media url for the user object
     *
     * @internal
     * @return string
     */
    public function mediaUrl(): string
    {
        return $this->kirby()->url('media') . '/users/' . $this->id();
    }

    /**
     * Creates a user model if it has been registered
     *
     * @internal
     * @param string $name
     * @param array $props
     * @return \Kirby\Cms\User
     */
    public static function model(string $name, array $props = [])
    {
        if ($class = (static::$models[$name] ?? null)) {
            $object = new $class($props);

            if (is_a($object, 'Kirby\Cms\User') === true) {
                return $object;
            }
        }

        return new static($props);
    }

    /**
     * Returns the last modification date of the user
     *
     * @param string $format
     * @param string|null $handler
     * @param string|null $languageCode
     * @return int|string
     */
    public function modified(string $format = 'U', string $handler = null, string $languageCode = null)
    {
        $modifiedContent = F::modified($this->contentFile($languageCode));
        $modifiedIndex   = F::modified($this->root() . '/index.php');
        $modifiedTotal   = max([$modifiedContent, $modifiedIndex]);
        $handler         = $handler ?? $this->kirby()->option('date.handler', 'date');

        return $handler($format, $modifiedTotal);
    }

    /**
     * Returns the user's name
     *
     * @return \Kirby\Cms\Field
     */
    public function name()
    {
        if (is_string($this->name) === true) {
            return new Field($this, 'name', $this->name);
        }

        if ($this->name !== null) {
            return $this->name;
        }

        return $this->name = new Field($this, 'name', $this->credentials()['name'] ?? null);
    }

    /**
     * Returns the user's name or,
     * if empty, the email address
     *
     * @return \Kirby\Cms\Field
     */
    public function nameOrEmail()
    {
        $name = $this->name();
        return $name->isNotEmpty() ? $name : new Field($this, 'email', $this->email());
    }

    /**
     * Create a dummy nobody
     *
     * @internal
     * @return self
     */
    public static function nobody()
    {
        return new static([
            'email' => 'nobody@getkirby.com',
            'role'  => 'nobody'
        ]);
    }

    /**
     * Panel icon definition
     *
     * @internal
     * @param array $params
     * @return array
     */
    public function panelIcon(array $params = null): array
    {
        $params['type'] = 'user';

        return parent::panelIcon($params);
    }

    /**
     * Returns the image file object based on provided query
     *
     * @internal
     * @param string|null $query
     * @return \Kirby\Cms\File|\Kirby\Cms\Asset|null
     */
    protected function panelImageSource(string $query = null)
    {
        if ($query === null) {
            return $this->avatar();
        }

        return parent::panelImageSource($query);
    }

    /**
     * Returns the full path without leading slash
     *
     * @internal
     * @return string
     */
    public function panelPath(): string
    {
        return 'users/' . $this->id();
    }

    /**
     * Returns prepared data for the panel user picker
     *
     * @param array|null $params
     * @return array
     */
    public function panelPickerData(array $params = null): array
    {
        $image = $this->panelImage($params['image'] ?? []);
        $icon  = $this->panelIcon($image);

        return [
            'icon'     => $icon,
            'id'       => $this->id(),
            'image'    => $image,
            'email'    => $this->email(),
            'info'     => $this->toString($params['info'] ?? false),
            'link'     => $this->panelUrl(true),
            'text'     => $this->toString($params['text'] ?? '{{ user.username }}'),
            'username' => $this->username(),
        ];
    }

    /**
     * Returns the url to the editing view
     * in the panel
     *
     * @internal
     * @param bool $relative
     * @return string
     */
    public function panelUrl(bool $relative = false): string
    {
        if ($relative === true) {
            return '/' . $this->panelPath();
        } else {
            return $this->kirby()->url('panel') . '/' . $this->panelPath();
        }
    }

    /**
     * Returns the encrypted user password
     *
     * @return string|null
     */
    public function password(): ?string
    {
        if ($this->password !== null) {
            return $this->password;
        }

        return $this->password = $this->readPassword();
    }

    /**
     * @return \Kirby\Cms\UserPermissions
     */
    public function permissions()
    {
        return new UserPermissions($this);
    }

    /**
     * Returns the user role
     *
     * @return \Kirby\Cms\Role
     */
    public function role()
    {
        if (is_a($this->role, 'Kirby\Cms\Role') === true) {
            return $this->role;
        }

        $roleName = $this->role ?? $this->credentials()['role'] ?? 'visitor';

        if ($role = $this->kirby()->roles()->find($roleName)) {
            return $this->role = $role;
        }

        return $this->role = Role::nobody();
    }

    /**
     * Returns all available roles
     * for this user, that can be selected
     * by the authenticated user
     *
     * @return \Kirby\Cms\Roles
     */
    public function roles()
    {
        $kirby = $this->kirby();
        $roles = $kirby->roles();

        // a collection with just the one role of the user
        $myRole = $roles->filter('id', $this->role()->id());

        // if there's an authenticated user …
        if ($user = $kirby->user()) {

            // admin users can select pretty much any role
            if ($user->isAdmin() === true) {
                // except if the user is the last admin
                if ($this->isLastAdmin() === true) {
                    // in which case they have to stay admin
                    return $myRole;
                }

                // return all roles for mighty admins
                return $roles;
            }
        }

        // any other user can only keep their role
        return $myRole;
    }

    /**
     * The absolute path to the user directory
     *
     * @return string
     */
    public function root(): string
    {
        return $this->kirby()->root('accounts') . '/' . $this->id();
    }

    /**
     * Returns the UserRules class to
     * validate any important action.
     *
     * @return \Kirby\Cms\UserRules
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
    protected function setBlueprint(array $blueprint = null)
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
     * @param string $email|null
     * @return self
     */
    protected function setEmail(string $email = null)
    {
        if ($email !== null) {
            $this->email = Str::lower(trim($email));
        }
        return $this;
    }

    /**
     * Sets the user id
     *
     * @param string $id|null
     * @return self
     */
    protected function setId(string $id = null)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Sets the user language
     *
     * @param string $language|null
     * @return self
     */
    protected function setLanguage(string $language = null)
    {
        $this->language = $language !== null ? trim($language) : null;
        return $this;
    }

    /**
     * Sets the user name
     *
     * @param string $name|null
     * @return self
     */
    protected function setName(string $name = null)
    {
        $this->name = $name !== null ? trim(strip_tags($name)) : null;
        return $this;
    }

    /**
     * Sets the user's password hash
     *
     * @param string $password|null
     * @return self
     */
    protected function setPassword(string $password = null)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Sets the user role
     *
     * @param string $role|null
     * @return self
     */
    protected function setRole(string $role = null)
    {
        $this->role = $role !== null ? Str::lower(trim($role)) : null;
        return $this;
    }

    /**
     * Converts session options into a session object
     *
     * @param \Kirby\Session\Session|array $session Session options or session object to unset the user in
     * @return \Kirby\Session\Session
     */
    protected function sessionFromOptions($session)
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
     * Returns the parent Users collection
     *
     * @return \Kirby\Cms\Users
     */
    protected function siblingsCollection()
    {
        return $this->kirby()->users();
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
            'avatar'   => $this->avatar() ? $this->avatar()->toArray() : null,
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
     * @param array|null $data
     * @param string $fallback Fallback for tokens in the template that cannot be replaced
     * @return string
     */
    public function toString(string $template = null, array $data = [], string $fallback = ''): string
    {
        if ($template === null) {
            $template = $this->email();
        }

        return parent::toString($template, $data);
    }

    /**
     * Returns the username
     * which is the given name or the email
     * as a fallback
     *
     * @return string|null
     */
    public function username(): ?string
    {
        return $this->name()->or($this->email())->value();
    }

    /**
     * Compares the given password with the stored one
     *
     * @param string $password|null
     * @return bool
     *
     * @throws \Kirby\Exception\NotFoundException If the user has no password
     * @throws \Kirby\Exception\InvalidArgumentException If the entered password is not valid
     *                                                   or does not match the user password
     */
    public function validatePassword(string $password = null): bool
    {
        if (empty($this->password()) === true) {
            throw new NotFoundException(['key' => 'user.password.undefined']);
        }

        if (Str::length($password) < 8) {
            throw new InvalidArgumentException(['key' => 'user.password.invalid']);
        }

        if (password_verify($password, $this->password()) !== true) {
            throw new InvalidArgumentException(['key' => 'user.password.notSame']);
        }

        return true;
    }
}
