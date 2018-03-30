<?php

namespace Kirby\Cms;

use Closure;
use Exception;
use Throwable;
use Firebase\JWT\JWT;
use Kirby\Form\Field;
use Kirby\Toolkit\Url;
use Kirby\Util\Controller;
use Kirby\Util\F;
use Kirby\Util\Factory;
use Kirby\Util\Dir;

class App extends Component
{

    use AppCaches;
    use AppHooks;
    use AppOptions;
    use AppPlugins;

    use HasSingleton;

    protected static $root;

    protected $collections;
    protected $components;
    protected $path;
    protected $roles;
    protected $roots;
    protected $routes;
    protected $site;
    protected $urls;
    protected $user;
    protected $users;

    /**
     * Creates a new App instance
     *
     * @param array $props
     */
    public function __construct(array $props = [])
    {
        // the kirby folder directory
        static::$root = dirname(dirname(__DIR__));

        // configurable properties
        $this->setProperties($props);

        // load all extensions
        $this->extensionsFromSystem();
        $this->extensionsFromProps($props);
        $this->extensionsFromPlugins();

        // set the singleton
        static::$instance = $this;
    }

    /**
     * Returns the authentication token from the request
     *
     * @return array|null
     */
    public function authToken()
    {
        $query   = $this->request()->data();
        $headers = $this->request()->headers();
        $token   = $query['auth'] ?? $headers['Authorization'] ?? null;
        $token   = preg_replace('!^Bearer !', '', $token);

        if (empty($token) === true) {
            throw new Exception('Invalid authentication token');
        }

        // TODO: get the key from config
        $key = 'kirby';

        // return the token object
        return (array)JWT::decode($token, $key, ['HS256']);
    }

    /**
     * Calls any Kirby route
     *
     * @return mixed
     */
    public function call(string $path = null, string $method = null)
    {
        $path   = $path   ?? $this->path();
        $method = $method ?? $this->request()->method();

        return $this->router()->call($path, $method);
    }

    /**
     * Returns a specific user-defined collection
     * by name. All relevant dependencies are
     * automatically injected
     *
     * @param string $name
     * @return void
     */
    public function collection(string $name)
    {
        return $this->collections()->get($name, [
            'kirby' => $this,
            'site'  => $this->site(),
            'pages' => $this->site()->children(),
            'users' => $this->users()
        ]);
    }

    /**
     * Returns all user-defined collections
     *
     * @return Collections
     */
    public function collections(): Collections
    {
        if (is_a($this->collections, Collections::class)) {
            return $this->collections;
        }

        return $this->collections = Collections::load($this);
    }

    /**
     * @return Factory
     */
    public function components(): Factory
    {
        if (is_a($this->components, Factory::class)) {
            return $this->components;
        }

        // set the default components
        return $this->setComponents()->components;
    }

    /**
     * Returns a component instance
     *
     * @param string $className
     * @param mixed ...$arguments
     * @return mixed
     */
    public function component(string $className, ...$arguments)
    {
        return $this->components()->get($className, ...$arguments);
    }

    /**
     * Calls a page controller by name
     * and with the given arguments
     *
     * @param string $name
     * @param array $arguments
     * @return array
     */
    public function controller(string $name, array $arguments = []): array
    {
        $name = basename(strtolower($name));

        // site controller
        if ($controller = Controller::load($this->root('controllers') . '/' . $name . '.php')) {
            return (array)$controller->call($this, $arguments);
        }

        // registry controller
        if ($controller = $this->extension('controllers', $name)) {
            return (array)$controller->call($this, $arguments);
        }

        return [];
    }

    /**
     * Returns the Email singleton
     *
     * @return Email
     */
    public function email($preset = [], array $props = [])
    {
        $email = new Email($preset, $props);
        return $this->component('email', $email->toArray());
    }

    /**
     * The Hooks registry
     *
     * @return Hooks
     */
    public function hooks(): Hooks
    {
        if (is_a($this->hooks, Hooks::class) === true) {
            return $this->hooks;
        }

        return $this->hooks = new Hooks($this);
    }

    /**
     * Returns all available locales
     *
     * @return Locales
     */
    public function locales()
    {
        return $this->component('locales');
    }

    /**
     * Returns the Media manager object
     *
     * @return Media
     */
    public function media(): Media
    {
        return $this->component('media', [
            'darkroom' => $this->component('darkroom'),
            'root'     => $this->root('media'),
            'url'      => $this->url('media')
        ]);
    }

    /**
     * Returns the request path
     *
     * @return void
     */
    public function path()
    {
        if (is_string($this->path) === true) {
            return $this->path;
        }

        /**
         * Fetch the default request path
         * TODO: move this to its own place
         */
        $requestUri  = parse_url($this->server()->get('request_uri'), PHP_URL_PATH);
        $scriptName  = $this->server()->get('script_name');
        $scriptFile  = basename($scriptName);
        $scriptDir   = dirname($scriptName);
        $scriptPath  = $scriptFile === 'index.php' ? $scriptDir: $scriptName;
        $requestPath = preg_replace('!^' . preg_quote($scriptPath) . '!', '', $requestUri);

        return $this->setPath($requestPath)->path;
    }

    /**
     * Returns the Response object for the
     * current request
     *
     * @return Response
     */
    public function render(string $path = null, string $method = null)
    {
        try {
            return $this->component('response', $this->call($path, $method));
        } catch (Throwable $e) {
            error_log($e);
            return $this->component('response', $e);
        }
    }

    /**
     * Returns the Request singleton
     *
     * @return Request
     */
    public function request()
    {
        return $this->component('request');
    }

    /**
     * Returns all user roles
     *
     * @return Roles
     */
    public function roles(): Roles
    {
        if (is_a($this->roles, Roles::class) === true) {
            return $this->roles;
        }

        return $this->roles = Roles::load($this->root('roles'));
    }

    /**
     * Returns a system root
     *
     * @param string $type
     * @return string
     */
    public function root($type = 'index'): string
    {
        return $this->roots()->$type();
    }

    /**
     * Returns the directory structure
     *
     * @return Roots
     */
    public function roots(): Roots
    {
        if (is_a($this->roots, Roots::class) === true) {
            return $this->roots;
        }

        // set the default roots
        return $this->setRoots()->roots();
    }

    /**
     * Returns the Router singleton
     *
     * @return Router
     */
    public function router()
    {
        return $this->component('router', $this->routes());
    }

    /**
     * Returns all defined routes
     *
     * @return array
     */
    public function routes(): array
    {
        if (is_array($this->routes) === true) {
            return $this->routes;
        }

        $registry = $this->extensions('routes');
        $main     = (include static::$root . '/config/routes.php')($this);

        return $this->routes = array_merge($registry, $main);
    }

    /**
     * Creates the Factory class instance
     * with all registered components
     *
     * @param array $components
     * @return self
     */
    protected function setComponents(array $components = null): self
    {
        $defaultComponentsCreator = include static::$root . '/config/components.php';
        $defaultComponentsConfig  = [];

        if (is_a($defaultComponentsCreator, Closure::class)) {
            $defaultComponentsConfig = (array)$defaultComponentsCreator($this);
        }

        $this->components = new Factory(array_merge($defaultComponentsConfig, (array)$components));
        return $this;
    }

    /**
     * Sets the request path that is
     * used for the router
     *
     * @param string $path
     * @return self
     */
    protected function setPath(string $path = null)
    {
        $this->path = $path !== null ? trim($path, '/') : null;
        return $this;
    }

    /**
     * Create your own set of roles
     *
     * @param array $roles
     * @return self
     */
    protected function setRoles(array $roles = null): self
    {
        if ($roles !== null) {
            $this->roles = Roles::factory($roles, [
                'kirby' => $this
            ]);
        }

        return $this;
    }

    /**
     * Sets the directory structure
     *
     * @param array $roots
     * @return self
     */
    protected function setRoots(array $roots = null)
    {
        $this->roots = new Roots($roots);
        return $this;
    }

    /**
     * Sets a custom Site object
     *
     * @param Site $site
     * @return self
     */
    protected function setSite(Site $site = null)
    {
        $this->site = $site;
        return $this;
    }

    /**
     * Sets the Url structure
     *
     * @param array $urls
     * @return self
     */
    protected function setUrls(array $urls = null)
    {
        $this->urls = new Urls(array_merge(['index' => Url::index()], (array)$urls));
        return $this;
    }

    /**
     * Set the currently active user id
     *
     * @param  User|string $user
     * @return self
     */
    protected function setUser($user = null): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Create your own set of app users
     *
     * @param array $users
     * @return self
     */
    protected function setUsers(array $users = null): self
    {
        if ($users !== null) {
            $this->users = Users::factory($users, [
                'kirby' => $this
            ]);
        }

        return $this;
    }

    /**
     * Returns the Server singleton
     *
     * @return Server
     */
    public function server()
    {
        return $this->component('server');
    }

    /**
     * @return Site
     */
    public function site(): Site
    {
        if (is_a($this->site, Site::class)) {
            return $this->site;
        }

        return $this->site = $this->component('site');
    }

    /**
     * System check class
     *
     * @return System
     */
    public function system(): System
    {
        return new System($this);
    }

    /**
     * Returns a system url
     *
     * @param string $type
     * @return string
     */
    public function url($type = 'index'): string
    {
        return $this->urls()->$type();
    }

    /**
     * Returns the url structure
     *
     * @return Urls
     */
    public function urls(): Urls
    {
        if (is_a($this->urls, Urls::class) === true) {
            return $this->urls;
        }

        // set the default urls
        return $this->setUrls()->urls();
    }

    /**
     * Returns a specific user by id
     * or the current user if no id is given
     *
     * @param string $id
     * @return User|null
     */
    public function user(string $id = null)
    {
        if ($id === null) {
            if (is_a($this->user, User::class) === true) {
                return $this->user;
            }

            if (is_string($this->user) === true) {
                return $this->user = $this->users()->find($this->user);
            }

            try {
                return $this->user = $this->users()->find($this->authToken()['uid']);
            } catch (Throwable $e) {
                return null;
            }
        }

        return $this->users()->find($id);
    }

    /**
     * Returns all users
     *
     * @return Users
     */
    public function users(): Users
    {
        if (is_a($this->users, Users::class) === true) {
            return $this->users;
        }

        return $this->users = Users::load($this->root('accounts'), ['kirby' => $this]);
    }

    /**
     * Returns translate string for key from locales file
     *
     * @param   string       $key
     * @param   string|null  $fallback
     * @param   string|null  $locale
     * @return  string
     */
    public function translate(string $key, string $fallback = null, string $locale = null): string
    {
        // TODO: handle short locales
        if ($locale === null) {
            if ($user = $this->user()) {
                $locale = $user->language() ?? 'en_US';
            } else {
                $locale = 'en_US';
            }
        }

        $locale = $this->locales()->get($locale);

        return $locale->get($key, $fallback);
    }

}
