<?php

namespace Kirby\Cms;

use Closure;
use Exception;

use Kirby\Toolkit\Url;
use Kirby\Util\Controller;
use Kirby\Util\F;
use Kirby\Util\Factory;
use Kirby\Util\Dir;

class App extends Component
{

    use HasSingleton;

    protected static $root;

    protected $collections;
    protected $components;
    protected $options;
    protected $hooks;
    protected $path;
    protected $registry;
    protected $roots;
    protected $routes;
    protected $site;
    protected $urls;

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

        // create the plugin registry
        $this->registry = new Registry;

        // register all field methods
        ContentField::methods(include static::$root . '/extensions/methods.php');

        static::$instance = $this;
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
        if ($controller = $this->get('controller', $name)) {
            return (array)$controller->call($this, $arguments);
        }

        return [];
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

        $this->hooks = new Hooks($this);
        $this->hooks->registerAll($this->get('hook'));

        return $this->hooks;
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
     * Load a specific configuration option
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function option(string $key, $default = null)
    {
        return $this->options()[$key] ?? $default;
    }

    /**
     * Returns all configuration options
     *
     * @return array
     */
    public function options(): array
    {
        if (is_array($this->options) === true) {
            return $this->options;
        }

        return $this->options = Config::for($this);
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

    public function get($type, $name = null)
    {
        return $this->registry->get($type, $name);
    }

    /**
     * Returns the Response object for the
     * current request
     *
     * @return Response
     */
    public function render(string $path = null, string $method = null)
    {
        $path   = $path   ?? $this->path();
        $method = $method ?? $this->request()->method();

        return $this->component('response', $this->router()->call($path, $method));
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

        return $this->routes = (array)include static::$root . '/config/routes.php';
    }

    /**
     * Registry setter
     *
     * @param string $type
     * @param mixed ...$arguments
     * @return self
     */
    public function set($type, ...$arguments): self
    {
        $this->registry->set($type, ...$arguments);
        return $this;
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
            // TODO: return the logged in user
            return $this->users()->first();
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
        return $this->component('users');
    }

}
