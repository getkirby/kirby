<?php

namespace Kirby\Cms;

use Closure;
use Exception;
use Throwable;
use Kirby\Api\Api;
use Kirby\Data\Data;
use Kirby\Email\PHPMailer as Emailer;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Form\Field;
use Kirby\Http\Router;
use Kirby\Http\Request;
use Kirby\Http\Server;
use Kirby\Http\Visitor;
use Kirby\Image\Darkroom;
use Kirby\Session\AutoSession as Session;
use Kirby\Text\KirbyTag;
use Kirby\Toolkit\Controller;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\Properties;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\Url;

/**
 * The App object is a big-ass monolith that's
 * in the center between all the other CMS classes.
 * It's the $kirby object in templates and handles
 */
class App
{
    use AppCaches;
    use AppErrors;
    use AppPlugins;
    use AppTranslations;
    use AppUsers;
    use Properties;

    protected static $instance;
    protected static $root;
    protected static $version;

    public $data = [];
    public $language;

    protected $collections;
    protected $languages;
    protected $options;
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
        $this->optionsFromConfig();
        $this->optionsFromProps($props['options'] ?? []);

        // create all urls after the config, so possible
        // options can be taken into account
        $this->bakeUrls($props['urls'] ?? []);

        // configurable properties
        $this->setOptionalProperties($props, [
            'path',
            'roles',
            'site',
            'user',
            'users'
        ]);

        // setup the I18n class with the translation loader
        $this->i18n();

        // load all extensions
        $this->extensionsFromSystem();
        $this->extensionsFromProps($props);
        $this->extensionsFromPlugins();
        $this->extensionsFromOptions();
        $this->extensionsFromFolders();

        // handle those damn errors
        $this->handleErrors();

        // set the singleton
        static::$instance = $this;
    }

    /**
     * Improved var_dump output
     *
     * @return array
     */
    public function __debuginfo(): array
    {
        return [
            'languages' => $this->languages(),
            'options'   => $this->options(),
            'request'   => $this->request(),
            'roots'     => $this->roots(),
            'site'      => $this->site(),
            'urls'      => $this->urls(),
            'version'   => $this->version(),
        ];
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
    public function controller(string $name, array $arguments = [], string $contentType = 'html'): array
    {
        $name = basename(strtolower($name));

        if ($controller = $this->controllerLookup($name, $contentType)) {
            return (array)$controller->call($this, $arguments);
        }

        if ($contentType !== 'html') {

            // no luck for a specific representation controller?
            // let's try the html controller instead
            if ($controller = $this->controllerLookup($name)) {
                return (array)$controller->call($this, $arguments);
            }
        }

        // still no luck? Let's take the site controller
        if ($controller = $this->controllerLookup('site')) {
            return (array)$controller->call($this, $arguments);
        }

        return [];
    }

    /**
     * Try to find a controller by name
     *
     * @param string $name
     * @return Closure|null
     */
    protected function controllerLookup(string $name, string $contentType = 'html'): ?Controller
    {
        if ($contentType !== null && $contentType !== 'html') {
            $name .= '.' . $contentType;
        }

        // controller on disk
        if ($controller = Controller::load($this->root('controllers') . '/' . $name . '.php')) {
            return $controller;
        }

        // registry controller
        if ($controller = $this->extension('controllers', $name)) {
            return is_a($controller, Controller::class) ? $controller : new Controller($controller);
        }

        return null;
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
     * Returns the current language
     *
     * @param string|null $code
     * @return Language|null
     */
    public function language(string $code = null): ?Language
    {
        if ($code !== null) {
            return $this->languages()->find($code);
        }

        return $this->language = $this->language ?? $this->languages()->findDefault();
    }

    /**
     * Returns all available site languages
     *
     * @return Languages
     */
    public function languages(): Languages
    {
        return $this->languages = $this->languages ?? Languages::load();
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
     * Load a specific configuration option
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function option(string $key, $default = null)
    {
        return $this->options[$key] ?? $default;
    }

    /**
     * Returns all configuration options
     *
     * @return array
     */
    public function options(): array
    {
        return $this->options;
    }

    /**
     * Inject options from Kirby instance props
     *
     * @return array
     */
    protected function optionsFromProps(array $options = [])
    {
        return $this->options = array_replace_recursive($this->options, $options);
    }

    /**
     * Load all options from files in site/config
     *
     * @return array
     */
    protected function optionsFromConfig(): array
    {
        $server = $this->server();
        $root   = $this->root('config');

        $main = (array)F::load($root . '/config.php', []);
        $host = (array)F::load($root . '/config.' . basename($server->host()) . '.php', []);
        $addr = (array)F::load($root . '/config.' . basename($server->address()) . '.php', []);

        return $this->options = array_replace_recursive($main, $host, $addr);
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

        $requestUri  = '/' . $this->request()->url()->path();
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
     * Path resolver for the router
     *
     * @param string $path
     * @param Language $language
     * @return mixed
     */
    public function resolve(string $path, Language $language = null)
    {
        // set the current language
        $this->language = $language;

        // the site is needed a couple times here
        $site = $this->site();

        if ($page = $site->find($path)) {
            return $page;
        }

        if ($draft = $site->draft($path)) {
            if ($draft->isVerified(get('token'))) {
                return $draft;
            }
        }

        // try to resolve content representations if the path has an extension
        $extension = F::extension($path);
        $path      = rtrim($path, '.' . $extension);

        // stop when there's no extension
        if (empty($extension) === true) {
            return null;
        }

        // try to find the page for the representation
        if ($page = $site->find($path)) {
            return Response::for($page, [], $extension);
        }

        $id       = dirname($path);
        $filename = basename($path) . '.' . $extension;

        // try to resolve image urls for pages and drafts
        if ($page = $site->findPageOrDraft($id)) {
            return $page->file($filename);
        }

        // try to resolve site files at least
        return $site->file($filename);
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
    public function snippet(string $name, array $data = []): ?string
    {
        return $this->extensions['components']['snippet']($this, $name, array_merge($this->data, $data));
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
    public function template(string $name, string $type = 'html'): Template
    {
        return $this->extensions['components']['template']($this, $name, $type);
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
