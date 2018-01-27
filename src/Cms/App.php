<?php

namespace Kirby\Cms;

use Closure;
use Exception;

use Kirby\Toolkit\Url;
use Kirby\Util\Controller;
use Kirby\Util\Factory;

class App extends Object
{

    use HasSingleton;

    protected static $root;

    protected $collections;
    protected $components;
    protected $path;
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
        $this->setOptionalProperties($props, [
            'components',
            'path',
            'roots',
            'site',
            'urls'
        ]);

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

        return $this->collections = Collections::load($this->root('collections'));
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
    public function controller(string $name, array $arguments = [], string $contentType = null): array
    {
        $name = basename(strtolower($name));

        if ($contentType !== null && $contentType !== 'html') {
            $name .= '.' . $contentType;
        }

        if ($controller = Controller::load($this->root('controllers') . '/' . $name . '.php')) {
            return (array)$controller->call($this, $arguments);
        }

        return [];
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
        $path   = $path   ?? $this->path();
        $method = $method ?? $this->request()->method();

        try {
            return $this->component('response', $this->router()->call($path, $method));
        } catch (Exception $e) {
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
     * Creates the Factory class instance
     * with all registered components
     *
     * @param array $components
     * @return self
     */
    protected function setComponents(array $components = []): self
    {

        $defaultComponentsCreator = include static::$root . '/config/components.php';
        $defaultComponentsConfig  = [];

        if (is_a($defaultComponentsCreator, Closure::class)) {
            $defaultComponentsConfig = (array)$defaultComponentsCreator($this);
        }

        $this->components = new Factory(array_merge($defaultComponentsConfig, $components));
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
        $this->path = trim($path, '/');
        return $this;
    }

    /**
     * Sets the directory structure
     *
     * @param array $roots
     * @return self
     */
    protected function setRoots(array $roots = [])
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
    protected function setUrls(array $urls = [])
    {
        $this->urls = new Urls(array_merge(['index' => Url::index()], $urls));
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

        $site = new Site([
            'errorPageId' => 'error',
            'homePageId'  => 'home',
            'root'        => $this->root('content'),
            'url'         => $this->url('index'),
        ]);

        return $this->setSite($site)->site();
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
        return new Users([]);
    }

}
