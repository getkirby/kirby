<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
use Kirby\Email\PHPMailer as Emailer;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Http\Router;
use Kirby\Http\Request;
use Kirby\Http\Server;
use Kirby\Http\Visitor;
use Kirby\Session\AutoSession;
use Kirby\Text\KirbyTag;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Config;
use Kirby\Toolkit\Controller;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\Properties;

/**
 * The `$kirby` object is the app instance of
 * your Kirby installation. It's the central
 * starting point to get all the different
 * aspects of your site, like the options, urls,
 * roots, languages, roles, etc.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class App
{
    const CLASS_ALIAS = 'kirby';

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

    protected $api;
    protected $collections;
    protected $defaultLanguage;
    protected $language;
    protected $languages;
    protected $locks;
    protected $multilang;
    protected $options;
    protected $path;
    protected $request;
    protected $response;
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

        // set the path to make it available for the url bakery
        $this->setPath($props['path'] ?? null);

        // create all urls after the config, so possible
        // options can be taken into account
        $this->bakeUrls($props['urls'] ?? []);

        // configurable properties
        $this->setOptionalProperties($props, [
            'languages',
            'request',
            'roles',
            'site',
            'user',
            'users'
        ]);

        // set the singleton
        Model::$kirby = static::$instance = $this;

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

        // bake config
        Config::$data = $this->options;
    }

    /**
     * Improved `var_dump` output
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
     * @internal
     * @return Kirby\Cms\Api
     */
    public function api()
    {
        if ($this->api !== null) {
            return $this->api;
        }

        $root       = static::$root . '/config/api';
        $extensions = $this->extensions['api'] ?? [];
        $routes     = (include $root . '/routes.php')($this);

        $api = [
            'debug'          => $this->option('debug', false),
            'authentication' => $extensions['authentication'] ?? include $root . '/authentication.php',
            'data'           => $extensions['data']           ?? [],
            'collections'    => array_merge($extensions['collections'] ?? [], include $root . '/collections.php'),
            'models'         => array_merge($extensions['models']      ?? [], include $root . '/models.php'),
            'routes'         => array_merge($routes, $extensions['routes'] ?? []),
            'kirby'          => $this,
        ];

        return $this->api = new Api($api);
    }

    /**
     * Applies a hook to the given value;
     * the value that gets modified by the hooks
     * is always the last argument
     *
     * @internal
     * @param string $name Hook name
     * @param mixed $args Arguments to pass to the hooks
     * @return mixed Resulting value as modified by the hooks
     */
    public function apply(string $name, ...$args)
    {
        // split up args into "passive" args and the value
        $value = array_pop($args);

        if ($functions = $this->extension('hooks', $name)) {
            foreach ($functions as $function) {
                // re-assemble args
                $hookArgs   = $args;
                $hookArgs[] = $value;

                // bind the App object to the hook
                $newValue = $function->call($this, ...$hookArgs);

                // update value if one was returned
                if ($newValue !== null) {
                    $value = $newValue;
                }
            }
        }

        return $value;
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
     * Returns all available blueprints for this installation
     *
     * @param string $type
     * @return array
     */
    public function blueprints(string $type = 'pages'): array
    {
        $blueprints = [];

        foreach ($this->extensions('blueprints') as $name => $blueprint) {
            if (dirname($name) === $type) {
                $name = basename($name);
                $blueprints[$name] = $name;
            }
        }

        foreach (glob($this->root('blueprints') . '/' . $type . '/*.yml') as $blueprint) {
            $name = F::name($blueprint);
            $blueprints[$name] = $name;
        }

        ksort($blueprints);

        return array_values($blueprints);
    }

    /**
     * Calls any Kirby route
     *
     * @param string $path
     * @param string $method
     * @return mixed
     */
    public function call(string $path = null, string $method = null)
    {
        $router = $this->router();

        $router::$beforeEach = function ($route, $path, $method) {
            $this->trigger('route:before', $route, $path, $method);
        };

        $router::$afterEach = function ($route, $path, $method, $result) {
            return $this->apply('route:after', $route, $path, $method, $result);
        };

        return $router->call($path ?? $this->path(), $method ?? $this->request()->method());
    }

    /**
     * Returns a specific user-defined collection
     * by name. All relevant dependencies are
     * automatically injected
     *
     * @param string $name
     * @return Kirby\Cms\Collection|null
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
     * @return Kirby\Cms\Collections
     */
    public function collections()
    {
        return $this->collections = $this->collections ?? new Collections;
    }

    /**
     * Returns a core component
     *
     * @internal
     * @param string $name
     * @return mixed
     */
    public function component($name)
    {
        return $this->extensions['components'][$name] ?? null;
    }

    /**
     * Returns the content extension
     *
     * @internal
     * @return string
     */
    public function contentExtension(): string
    {
        return $this->options['content']['extension'] ?? 'txt';
    }

    /**
     * Returns files that should be ignored when scanning folders
     *
     * @internal
     * @return array
     */
    public function contentIgnore(): array
    {
        return $this->options['content']['ignore'] ?? Dir::$ignore;
    }

    /**
     * Calls a page controller by name
     * and with the given arguments
     *
     * @internal
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
     * @return Kirby\Toolkit\Controller|null
     */
    protected function controllerLookup(string $name, string $contentType = 'html')
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
     * Returns the default language object
     *
     * @return Kirby\Cms\Language|null
     */
    public function defaultLanguage()
    {
        return $this->defaultLanguage = $this->defaultLanguage ?? $this->languages()->default();
    }

    /**
     * Destroy the instance singleton and
     * purge other static props
     *
     * @internal
     */
    public static function destroy(): void
    {
        static::$plugins  = [];
        static::$instance = null;
    }

    /**
     * Detect the prefered language from the visitor object
     *
     * @return Kirby\Cms\Language
     */
    public function detectedLanguage()
    {
        $languages = $this->languages();
        $visitor   = $this->visitor();

        foreach ($visitor->acceptedLanguages() as $lang) {
            if ($language = $languages->findBy('locale', $lang->locale(LC_ALL))) {
                return $language;
            }
        }

        foreach ($visitor->acceptedLanguages() as $lang) {
            if ($language = $languages->findBy('code', $lang->code())) {
                return $language;
            }
        }

        return $this->defaultLanguage();
    }

    /**
     * Returns the Email singleton
     *
     * @return Kirby\Email\PHPMailer
     */
    public function email($preset = [], array $props = [])
    {
        return new Emailer((new Email($preset, $props))->toArray(), $props['debug'] ?? false);
    }

    /**
     * Finds any file in the content directory
     *
     * @param string $path
     * @param boolean $drafts
     * @return Kirby\Cms\File|null
     */
    public function file(string $path, $parent = null, bool $drafts = true)
    {
        $parent   = $parent ?? $this->site();
        $id       = dirname($path);
        $filename = basename($path);

        if (is_a($parent, User::class) === true) {
            return $parent->file($filename);
        }

        if (is_a($parent, File::class) === true) {
            $parent = $parent->parent();
        }

        if ($id === '.') {
            if ($file = $parent->file($filename)) {
                return $file;
            } elseif ($file = $this->site()->file($filename)) {
                return $file;
            } else {
                return null;
            }
        }

        if ($page = $this->page($id, $parent, $drafts)) {
            return $page->file($filename);
        }

        if ($page = $this->page($id, null, $drafts)) {
            return $page->file($filename);
        }

        return null;
    }

    /**
     * Returns the current App instance
     *
     * @param Kirby\Cms\App $instance
     * @return self
     */
    public static function instance(self $instance = null)
    {
        if ($instance === null) {
            return static::$instance ?? new static;
        }

        return static::$instance = $instance;
    }

    /**
     * Takes almost any kind of input and
     * tries to convert it into a valid response
     *
     * @internal
     * @param mixed $input
     * @return Kirby\Http\Response
     */
    public function io($input)
    {
        // use the current response configuration
        $response = $this->response();

        // any direct exception will be turned into an error page
        if (is_a($input, 'Throwable') === true) {
            if (is_a($input, 'Kirby\Exception\Exception') === true) {
                $code    = $input->getHttpCode();
                $message = $input->getMessage();
            } else {
                $code    = $input->getCode();
                $message = $input->getMessage();
            }

            if ($code < 400 || $code > 599) {
                $code = 500;
            }

            if ($errorPage = $this->site()->errorPage()) {
                return $response->code($code)->send($errorPage->render([
                    'errorCode'    => $code,
                    'errorMessage' => $message,
                    'errorType'    => get_class($input)
                ]));
            }

            return $response
                ->code($code)
                ->type('text/html')
                ->send($message);
        }

        // Empty input
        if (empty($input) === true) {
            return $this->io(new NotFoundException());
        }

        // Response Configuration
        if (is_a($input, 'Kirby\Cms\Responder') === true) {
            return $input->send();
        }

        // Responses
        if (is_a($input, 'Kirby\Http\Response') === true) {
            return $input;
        }

        // Pages
        if (is_a($input, 'Kirby\Cms\Page')) {
            $html = $input->render();

            if ($input->isErrorPage() === true) {
                if ($response->code() === null) {
                    $response->code(404);
                }
            }

            return $response->send($html);
        }

        // Files
        if (is_a($input, 'Kirby\Cms\File')) {
            return $response->redirect($input->mediaUrl(), 307)->send();
        }

        // Simple HTML response
        if (is_string($input) === true) {
            return $response->send($input);
        }

        // array to json conversion
        if (is_array($input) === true) {
            return $response->json($input)->send();
        }

        throw new InvalidArgumentException('Unexpected input');
    }

    /**
     * Renders a single KirbyTag with the given attributes
     *
     * @internal
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
     * @internal
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
     * @internal
     * @param string $text
     * @param array $data
     * @return string
     */
    public function kirbytext(string $text = null, array $data = [], bool $inline = false): string
    {
        $text = $this->apply('kirbytext:before', $text);
        $text = $this->kirbytags($text, $data);
        $text = $this->markdown($text, $inline);

        if ($this->option('smartypants', false) !== false) {
            $text = $this->smartypants($text);
        }

        $text = $this->apply('kirbytext:after', $text);

        return $text;
    }

    /**
     * Returns the current language
     *
     * @param string|null $code
     * @return Kirby\Cms\Language|null
     */
    public function language(string $code = null)
    {
        if ($this->multilang() === false) {
            return null;
        }

        if ($code === 'default') {
            return $this->languages()->default();
        }

        if ($code !== null) {
            return $this->languages()->find($code);
        }

        return $this->language = $this->language ?? $this->languages()->default();
    }

    /**
     * Returns the current language code
     *
     * @internal
     * @return string|null
     */
    public function languageCode(string $languageCode = null): ?string
    {
        if ($language = $this->language($languageCode)) {
            return $language->code();
        }

        return null;
    }

    /**
     * Returns all available site languages
     *
     * @return Kirby\Cms\Languages
     */
    public function languages()
    {
        if ($this->languages !== null) {
            return clone $this->languages;
        }

        return $this->languages = Languages::load();
    }

    /**
     * Returns the app's locks object
     *
     * @return Kirby\Cms\ContentLocks
     */
    public function locks(): ContentLocks
    {
        if ($this->locks !== null) {
            return $this->locks;
        }

        return $this->locks = new ContentLocks;
    }

    /**
     * Parses Markdown
     *
     * @internal
     * @param string $text
     * @param bool $inline
     * @return string
     */
    public function markdown(string $text = null, bool $inline = false): string
    {
        return $this->component('markdown')($this, $text, $this->options['markdown'] ?? [], $inline);
    }

    /**
     * Check for a multilang setup
     *
     * @return boolean
     */
    public function multilang(): bool
    {
        if ($this->multilang !== null) {
            return $this->multilang;
        }

        return $this->multilang = $this->languages()->count() !== 0;
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
        return A::get($this->options, $key, $default);
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
    protected function optionsFromProps(array $options = []): array
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

        Config::$data = [];

        $main   = F::load($root . '/config.php', []);
        $host   = F::load($root . '/config.' . basename($server->host()) . '.php', []);
        $addr   = F::load($root . '/config.' . basename($server->address()) . '.php', []);

        $config = Config::$data;

        return $this->options = array_replace_recursive($config, $main, $host, $addr);
    }

    /**
     * Returns any page from the content folder
     *
     * @param string $id
     * @param Kirby\Cms\Page|Kirby\Cms\Site|null $parent
     * @param bool $drafts
     * @return Kirby\Cms\Page|null
     */
    public function page(string $id, $parent = null, bool $drafts = true)
    {
        $parent = $parent ?? $this->site();

        if ($page = $parent->find($id)) {
            return $page;
        }

        if ($drafts === true && $draft = $parent->draft($id)) {
            return $draft;
        }

        return null;
    }

    /**
     * Returns the request path
     *
     * @return string
     */
    public function path(): string
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
     * @return Kirby\Http\Response
     */
    public function render(string $path = null, string $method = null)
    {
        return $this->io($this->call($path, $method));
    }

    /**
     * Returns the Request singleton
     *
     * @return Kirby\Http\Request
     */
    public function request()
    {
        return $this->request = $this->request ?? new Request;
    }

    /**
     * Path resolver for the router
     *
     * @internal
     * @param string $path
     * @param string|null $language
     * @return mixed
     */
    public function resolve(string $path = null, string $language = null)
    {
        // set the current translation
        $this->setCurrentTranslation($language);

        // set the current locale
        $this->setCurrentLanguage($language);

        // the site is needed a couple times here
        $site = $this->site();

        // use the home page
        if ($path === null) {
            if ($homePage = $site->homePage()) {
                return $homePage;
            }

            throw new NotFoundException('The home page does not exist');
        }

        // search for the page by path
        $page = $site->find($path);

        // search for a draft if the page cannot be found
        if (!$page && $draft = $site->draft($path)) {
            if ($this->user() || $draft->isVerified(get('token'))) {
                $page = $draft;
            }
        }

        // try to resolve content representations if the path has an extension
        $extension = F::extension($path);

        // no content representation? then return the page
        if (empty($extension) === true) {
            return $page;
        }

        // only try to return a representation
        // when the page has been found
        if ($page) {
            try {
                return $this
                    ->response()
                    ->body($page->render([], $extension))
                    ->type($extension);
            } catch (NotFoundException $e) {
                return null;
            }
        }

        $id       = dirname($path);
        $filename = basename($path);

        // try to resolve image urls for pages and drafts
        if ($page = $site->findPageOrDraft($id)) {
            return $page->file($filename);
        }

        // try to resolve site files at least
        return $site->file($filename);
    }

    /**
     * Response configuration
     *
     * @return Kirby\Cms\Responder
     */
    public function response()
    {
        return $this->response = $this->response ?? new Responder;
    }

    /**
     * Returns all user roles
     *
     * @return Kirby\Cms\Roles
     */
    public function roles()
    {
        return $this->roles = $this->roles ?? Roles::load($this->root('roles'));
    }

    /**
     * Returns a system root
     *
     * @param string $type
     * @return string
     */
    public function root(string $type = 'index'): string
    {
        return $this->roots->__get($type);
    }

    /**
     * Returns the directory structure
     *
     * @return Kirby\Cms\Ingredients
     */
    public function roots()
    {
        return $this->roots;
    }

    /**
     * Returns the currently active route
     *
     * @return Kirby\Http\Route|null
     */
    public function route()
    {
        return $this->router()->route();
    }

    /**
     * Returns the Router singleton
     *
     * @internal
     * @return Kirby\Http\Router
     */
    public function router()
    {
        $routes = $this->routes();

        if ($this->multilang() === true) {
            foreach ($routes as $index => $route) {
                if (empty($route['language']) === false) {
                    unset($routes[$index]);
                }
            }
        }

        return $this->router = $this->router ?? new Router($routes);
    }

    /**
     * Returns all defined routes
     *
     * @internal
     * @return array
     */
    public function routes(): array
    {
        if (is_array($this->routes) === true) {
            return $this->routes;
        }

        $registry = $this->extensions('routes');
        $system   = (include static::$root . '/config/routes.php')($this);
        $routes   = array_merge($system['before'], $registry, $system['after']);

        return $this->routes = $routes;
    }

    /**
     * Returns the current session object
     *
     * @param array $options Additional options, see the session component
     * @return Kirby\Session\Session
     */
    public function session(array $options = [])
    {
        $this->session = $this->session ?? new AutoSession($this->root('sessions'), $this->options['session'] ?? []);
        return $this->session->get($options);
    }

    /**
     * Create your own set of languages
     *
     * @param array $languages
     * @return self
     */
    protected function setLanguages(array $languages = null)
    {
        if ($languages !== null) {
            $this->languages = new Languages();

            foreach ($languages as $props) {
                $language = new Language($props);
                $this->languages->data[$language->code()] = $language;
            }
        }

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
     * Sets the request
     *
     * @param array $request
     * @return self
     */
    protected function setRequest(array $request = null)
    {
        if ($request !== null) {
            $this->request = new Request($request);
        }

        return $this;
    }

    /**
     * Create your own set of roles
     *
     * @param array $roles
     * @return self
     */
    protected function setRoles(array $roles = null)
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
     * @param Kirby\Cms\Site|array $site
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
     * @return Kirby\Http\Server
     */
    public function server()
    {
        return $this->server = $this->server ?? new Server;
    }

    /**
     * Initializes and returns the Site object
     *
     * @return Kirby\Cms\Site
     */
    public function site()
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
     * @internal
     * @param string $text
     * @return string
     */
    public function smartypants(string $text = null): string
    {
        $options = $this->option('smartypants', []);

        if ($options === true) {
            $options = [];
        }

        return $this->component('smartypants')($this, $text, $options);
    }

    /**
     * Uses the snippet component to create
     * and return a template snippet
     *
     * @internal
     * @return string
     */
    public function snippet($name, array $data = []): ?string
    {
        return $this->component('snippet')($this, $name, array_merge($this->data, $data));
    }

    /**
     * System check class
     *
     * @return Kirby\Cms\System
     */
    public function system()
    {
        return $this->system = $this->system ?? new System($this);
    }

    /**
     * Uses the template component to initialize
     * and return the Template object
     *
     * @internal
     * @return Kirby\Cms\Template
     */
    public function template(string $name, string $type = 'html', string $defaultType = 'html')
    {
        return $this->component('template')($this, $name, $type, $defaultType);
    }

    /**
     * Thumbnail creator
     *
     * @param string $src
     * @param string $dst
     * @param array $options
     * @return string
     */
    public function thumb(string $src, string $dst, array $options = []): string
    {
        return $this->component('thumb')($this, $src, $dst, $options);
    }

    /**
     * Trigger a hook by name
     *
     * @internal
     * @param string $name
     * @param mixed ...$arguments
     * @return void
     */
    public function trigger(string $name, ...$arguments)
    {
        if ($functions = $this->extension('hooks', $name)) {
            static $level = 0;
            static $triggered = [];
            $level++;

            foreach ($functions as $index => $function) {
                if (in_array($function, $triggered[$name] ?? []) === true) {
                    continue;
                }

                // mark the hook as triggered, to avoid endless loops
                $triggered[$name][] = $function;

                // bind the App object to the hook
                $function->call($this, ...$arguments);
            }

            $level--;

            if ($level === 0) {
                $triggered = [];
            }
        }
    }

    /**
     * Returns a system url
     *
     * @param string $type
     * @return string
     */
    public function url(string $type = 'index'): string
    {
        return $this->urls->__get($type);
    }

    /**
     * Returns the url structure
     *
     * @return Kirby\Cms\Ingredients
     */
    public function urls()
    {
        return $this->urls;
    }

    /**
     * Returns the current version number from
     * the composer.json (Keep that up to date! :))
     *
     * @return string|null
     */
    public static function version(): ?string
    {
        return static::$version = static::$version ?? Data::read(static::$root . '/composer.json')['version'] ?? null;
    }

    /**
     * Creates a hash of the version number
     *
     * @return string
     */
    public static function versionHash(): string
    {
        return md5(static::version());
    }

    /**
     * Returns the visitor object
     *
     * @return Kirby\Cms\Visitor
     */
    public function visitor()
    {
        return $this->visitor = $this->visitor ?? new Visitor();
    }
}
