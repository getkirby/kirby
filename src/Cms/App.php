<?php

namespace Kirby\Cms;

use Closure;
use Exception;
use Throwable;
use Kirby\Api\Api;
use Kirby\Data\Data;
use Kirby\Email\PHPMailer as Emailer;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Field;
use Kirby\Http\Router;
use Kirby\Http\Request;
use Kirby\Http\Server;
use Kirby\Http\Visitor;
use Kirby\Image\Darkroom;
use Kirby\Session\AutoSession as Session;
use Kirby\Text\KirbyTag;
use Kirby\Toolkit\Url;
use Kirby\Toolkit\Url\Query as UrlQuery;
use Kirby\Toolkit\Controller;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\Str;

/**
 * The App object is a big-ass monolith that's
 * in the center between all the other CMS classes.
 * It's the $kirby object in templates and handles
 */
class App extends Component
{
    use AppCaches;
    use AppErrors;
    use AppOptions;
    use AppPlugins;
    use AppTranslations;
    use AppUsers;

    protected static $instance;
    protected static $root;
    protected static $version;

    protected $collections;
    protected $path;
    protected $roles;
    protected $roots;
    protected $routes;
    protected $router;
    protected $server;
    protected $session;
    protected $site;
    protected $system;
    protected $urls;
    protected $user;
    protected $users;
    protected $visitor;

    /**
     * Creates a new App instance
     *
     * @param array $props
     */
    public function __construct(array $props = [])
    {
        // the kirby folder directory
        static::$root = dirname(dirname(__DIR__));

        // register all roots to be able to load stuff afterwards
        $this->bakeRoots($props['roots'] ?? []);

        // stuff from config and additional options
        $this->optionsFromSystem();
        $this->optionsFromProps($props['options'] ?? []);

        // create all urls after the config, so possible
        // options can be taken into account
        $this->bakeUrls($props['urls'] ?? []);

        // configurable properties
        $this->setProperties($props);

        // load the english translation
        $this->loadFallbackTranslation();

        // load all extensions
        $this->extensionsFromSystem();
        $this->extensionsFromProps($props);
        $this->extensionsFromOptions();
        $this->extensionsFromPlugins();

        // handle those damn errors
        $this->handleErrors();

        // set the singleton
        static::$instance = $this;
    }

    /**
     * Returns the Api instance
     *
     * @return Api
     */
    public function api(): Api
    {
        return $this->api = $this->api ?? new Api(include static::$root . '/config/api.php');
    }

    /**
     * Sets the directory structure
     *
     * @param array $roots
     * @return self
     */
    protected function bakeRoots(array $roots = null)
    {
        $roots = array_merge(require static::$root . '/config/roots.php', (array)$roots);
        $this->roots = Ingredients::bake($roots);
        return $this;
    }

    /**
     * Sets the Url structure
     *
     * @param array $urls
     * @return self
     */
    protected function bakeUrls(array $urls = null)
    {
        // inject the index URL from the config
        if (isset($this->options['url']) === true) {
            $urls['index'] = $this->options['url'];
        }

        $urls = array_merge(require static::$root . '/config/urls.php', (array)$urls);
        $this->urls = Ingredients::bake($urls);
        return $this;
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
        $route  = $this->router()->find($path, $method);

        $this->trigger('route:before', $route, $path, $method);
        $result = $route->action()->call($route, ...$route->arguments());
        $this->trigger('route:after', $route, $path, $method, $result);

        return $result;
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
        return $this->collections = $this->collections ?? Collections::load($this);
    }

    /**
     * Returns a core component
     *
     * @param string $name
     * @return mixed
     */
    public function component($name)
    {
        return $this->extensions['components'][$name] ?? null;
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
    public function email($preset = [], array $props = []): Emailer
    {
        return new Emailer((new Email($preset, $props))->toArray(), $props['debug'] ?? false);
    }

    /**
     * Finds any file in the content directory
     *
     * @param string $path
     * @return File|null
     */
    public function file(string $path, $parent = null)
    {
        $parent   = $parent ?? $this->site();
        $id       = dirname($path);
        $filename = basename($path);

        if ($id === '.') {
            return $parent->file($filename);
        }

        if ($page = $parent->find($id)) {
            return $page->file($filename);
        }

        return null;
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
     * Renders a single KirbyTag with the given attributes
     *
     * @param string $type
     * @param string $value
     * @param array $attr
     * @param array $data
     * @return string
     */
    public function kirbytag(string $type, string $value = null, array $attr = [], array $data = []): string
    {
        $data['kirby']  = $data['kirby']  ?? $this;
        $data['site']   = $data['site']   ?? $data['kirby']->site();
        $data['parent'] = $data['parent'] ?? $data['site']->page();

        return (new KirbyTag($type, $value, $attr, $data, $this->options))->render();
    }

    /**
     * KirbyTags Parser
     *
     * @param string $text
     * @param array $data
     * @return string
     */
    public function kirbytags(string $text = null, array $data = []): string
    {
        $data['kirby']  = $data['kirby']  ?? $this;
        $data['site']   = $data['site']   ?? $data['kirby']->site();
        $data['parent'] = $data['parent'] ?? $data['site']->page();

        return KirbyTags::parse($text, $data, $this->options, $this->extensions['hooks']);
    }

    /**
     * Parses KirbyTags first and Markdown afterwards
     *
     * @param string $text
     * @param array $data
     * @return string
     */
    public function kirbytext(string $text = null, array $data = []): string
    {
        $text = $this->kirbytags($text, $data);
        $text = $this->markdown($text);

        return $text;
    }

    /**
     * Parses Markdown
     *
     * @param string $text
     * @return string
     */
    public function markdown(string $text = null): string
    {
        return $this->extensions['components']['markdown']($this, $text, $this->options['markdown'] ?? []);
    }

    /**
     * Returns any page from the content folder
     *
     * @return Page|null
     */
    public function page(string $id, $parent = null)
    {
        return ($parent ?? $this->site())->find($id);
    }

    /**
     * Creates a Pagination object
     *
     * @return Pagination
     */
    public function pagination(array $options = []): Pagination
    {
        $config  = $this->options['pagination'] ?? [];
        $request = $this->request();

        $options['limit']    = $options['limit']    ?? $config['limit'] ?? 20;
        $options['variable'] = $options['variable'] ?? $config['variable'] ?? 'page';
        $options['page']     = $options['page']     ?? $request->query()->get($options['variable'], 1);
        $options['url']      = $options['url']      ?? $request->url();

        return new Pagination($options);
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
        return $this->response($this->call($path, $method));
    }

    /**
     * Returns the Request singleton
     *
     * @return Request
     */
    public function request(): Request
    {
        return $this->request = $this->request ?? new Request;
    }

    /**
     * @return Response
     */
    public function response($input)
    {
        return $this->extensions['components']['response']($this, $input);
    }

    /**
     * Returns all user roles
     *
     * @return Roles
     */
    public function roles(): Roles
    {
        return $this->roles = $this->roles ?? Roles::load($this->root('roles'));
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
     * Returns the currently active route
     *
     * @return Route|null
     */
    public function route()
    {
        return $this->router()->route();
    }

    /**
     * Returns the Router singleton
     *
     * @return Router
     */
    public function router(): Router
    {
        return $this->router = $this->router ?? new Router($this->routes());
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
     * @return Session|AutoSession
     */
    public function session(array $options = [])
    {
        $this->session = $this->session ?? new Session($this->root('sessions'), $this->options['session'] ?? []);
        return $this->session->get($options);
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
     * Returns the Server object
     *
     * @return Server
     */
    public function server(): Server
    {
        return $this->server = $this->server ?? new Server;
    }

    /**
     * @return Site
     */
    public function site(): Site
    {
        return $this->site = $this->site ?? new Site([
            'errorPageId' => $this->options['error'] ?? 'error',
            'homePageId'  => $this->options['home']  ?? 'home',
            'kirby'       => $this,
            'url'         => $this->url('index'),
        ]);
    }

    /**
     * Applies the smartypants rule on the text
     *
     * @param string $text
     * @return string
     */
    public function smartypants(string $text = null): string
    {
        return $this->extensions['components']['smartypants']($this, $text, $this->options['smartypants'] ?? []);
    }

    /**
     * @return Snippet
     */
    public function snippet(string $name, array $data = []): Snippet
    {
        return $this->extensions['components']['snippet']($this, $name, $data);
    }

    /**
     * System check class
     *
     * @return System
     */
    public function system(): System
    {
        return $this->system = $this->system ?? new System($this);
    }

    /**
     * @return Template
     */
    public function template(string $name, array $data = [], string $appendix = null): Template
    {
        return $this->extensions['components']['template']($this, $name, $data, $appendix);
    }

    /**
     * Thumbnail creator
     *
     * @param string $src
     * @param string $dst
     * @param array $options
     * @return null
     */
    public function thumb(string $src, string $dst, array $options = [])
    {
        return $this->extensions['components']['thumb']($this, $src, $dst, $options);
    }

    /**
     *  Trigger a hook by name
     *
     * @param string $name
     * @param mixed ...$arguments
     * @return void
     */
    public function trigger(string $name, ...$arguments)
    {
        if ($functions = $this->extension('hooks', $name)) {
            static $triggered = [];

            foreach ($functions as $function) {
                if (in_array($function, $triggered[$name] ?? []) === true) {
                    continue;
                }

                // mark the hook as triggered, to avoid endless loops
                $triggered[$name][] = $function;

                // bind the App object to the hook
                $function->call($this, ...$arguments);
            }
        }
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
     * Returns the current version number from
     * the composer.json (Keep that up to date! :))
     *
     * @return string|null
     */
    public static function version()
    {
        return static::$version = static::$version ?? Data::read(static::$root . '/composer.json')['version'] ?? null;
    }

    /**
     * Returns the visitor object
     *
     * @return Visitor
     */
    public function visitor(): Visitor
    {
        return $this->visitor = $this->visitor ?? new Visitor();
    }
}
