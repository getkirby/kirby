<?php

namespace Kirby\Cms;

use Closure;
use Exception;
use Throwable;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Field;
use Kirby\Http\Visitor;
use Kirby\Image\Darkroom;
use Kirby\Toolkit\Url;
use Kirby\Session\Session;
use Kirby\Util\Controller;
use Kirby\Util\F;
use Kirby\Util\Factory;
use Kirby\Util\Dir;
use Kirby\Util\Str;

class App extends Component
{
    use AppCaches;
    use AppErrors;
    use AppHooks;
    use AppOptions;
    use AppPlugins;
    use AppTranslations;
    use AppUsers;

    protected static $instance;
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

        // load the english translation
        $this->loadFallbackTranslation();

        // load all extensions
        $this->extensionsFromSystem();
        $this->extensionsFromProps($props);
        $this->extensionsFromPlugins();

        // handle those damn errors
        $this->handleErrors();

        // set the singleton
        static::$instance = $this;
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
     * Destroy the instance singleton and
     * purge other static props
     */
    public static function destroy()
    {
        static::$plugins  = [];
        static::$instance = null;
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
     * Finds any file in the content directory
     *
     * @param string $path
     * @return File|null
     */
    public function file(string $path)
    {
        $id       = dirname($path);
        $filename = basename($path);

        if ($id === '.') {
            return $this->site()->file($filename);
        }

        if ($page = $this->site()->find($id)) {
            return $page->file($filename);
        }

        return null;
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
     * Returns the current App instance
     *
     * @param self $instance
     * @return self
     */
    public static function instance(self $instance = null): self
    {
        if ($instance === null) {
            return static::$instance ?? new static;
        }

        return static::$instance = $instance;
    }

    /**
     * Returns any page from the content folder
     *
     * @return Page|null
     */
    public function page(string $id)
    {
        return $this->site()->find($id);
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

        // check for path detection requirements
        if (isset($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME']) === false) {
            throw new InvalidArgumentException('The current path cannot be detected');
        }

        $requestUri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $scriptName  = $_SERVER['SCRIPT_NAME'];
        $scriptFile  = basename($scriptName);
        $scriptDir   = dirname($scriptName);
        $scriptPath  = $scriptFile === 'index.php' ? $scriptDir : $scriptName;
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
            if ($this->option('debug')) {
                throw $e;
            }

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
        return $this->roots->__get($type);
    }

    /**
     * Returns the directory structure
     *
     * @return Ingredients
     */
    public function roots(): Ingredients
    {
        return $this->roots;
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
     * Returns the current session object
     *
     * @param  array   $options Additional options, see the session component
     * @return Session
     */
    public function session(array $options = [])
    {
        return $this->component('session')->get($options);
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
        $roots = array_merge(require static::$root . '/config/roots.php', (array)$roots);
        $this->roots = Ingredients::bake($roots);
        return $this;
    }

    /**
     * Sets a custom Site object
     *
     * @param array|Site $site
     * @return self
     */
    protected function setSite($site = null)
    {
        if (is_array($site) === true) {
            $site = new Site($site + [
                'kirby' => $this
            ]);
        }

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
        $urls = array_merge(require static::$root . '/config/urls.php', (array)$urls);
        $this->urls = Ingredients::bake($urls);
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
     * Thumbnail creator
     *
     * @param string $src
     * @param string $dst
     * @param array $options
     * @return null
     */
    public function thumb(string $src, string $dst, array $attributes = [])
    {
        $options    = $this->options('thumbs', []);
        $darkroom   = Darkroom::factory($options['driver'] ?? 'gd', $options);
        $attributes = $darkroom->preprocess($src, $attributes);
        $root       = (new Filename($src, $dst, $attributes))->toString();

        // check if the thumbnail has to be regenerated
        if (file_exists($root) !== true || filemtime($root) < filemtime($src)) {
            F::copy($src, $root);
            $darkroom->process($root, $attributes);
        }

        return $root;
    }

    /**
     * Returns a system url
     *
     * @param string $type
     * @return string
     */
    public function url($type = 'index'): string
    {
        return $this->urls->__get($type);
    }

    /**
     * Returns the url structure
     *
     * @return Ingredients
     */
    public function urls(): Ingredients
    {
        return $this->urls;
    }

    /**
     * Returns the visitor object
     *
     * @return Visitor
     */
    public function visitor()
    {
        return new Visitor();
    }

}
