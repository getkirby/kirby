<?php

namespace Kirby\Cms;

use Closure;
use Exception;
use Throwable;
use Kirby\Data\Data;
use Kirby\Email\PHPMailer as Emailer;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\NotFoundException;
use Kirby\Form\Field;
use Kirby\Http\Route;
use Kirby\Http\Router;
use Kirby\Http\Request;
use Kirby\Http\Server;
use Kirby\Http\Visitor;
use Kirby\Image\Darkroom;
use Kirby\Session\AutoSession as Session;
use Kirby\Text\KirbyTag;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Config;
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
        Model::$kirby = static::$instance = $this;

        // bake config
        Config::$data = $this->options;
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

        return $this->api = $this->api ?? new Api($api);
    }

    /**
     *  Apply a hook to the given value
     *
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function apply(string $name, $value)
    {
        if ($functions = $this->extension('hooks', $name)) {
            foreach ($functions as $function) {
                // bind the App object to the hook
                $value = $function->call($this, $value);
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
     * Returns the content extension
     *
     * @return string
     */
    public function contentExtension(): string
    {
        return $this->options['content']['extension'] ?? 'txt';
    }

    /**
     * Returns files that should be ignored when scanning folders
     *
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
     * Returns the default language object
     *
     * @return Language|null
     */
    public function defaultLanguage(): ?Language
    {
        return $this->defaultLanguage = $this->defaultLanguage ?? $this->languages()->default();
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
     * Detect the prefered language from the visitor object
     *
     * @return Language
     */
    public function detectedLanguage()
    {
        $languages = $this->languages();
        $visitor   = $this->visitor();

        foreach ($visitor->acceptedLanguages() as $lang) {
            if ($language = $languages->findBy('locale', $lang->locale())) {
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
     * @param boolean $drafts
     * @return File|null
     */
    public function file(string $path, $parent = null, bool $drafts = true)
    {
        $parent   = $parent ?? $this->site();
        $id       = dirname($path);
        $filename = basename($path);

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
     * Takes almost any kind of input and
     * tries to convert it into a valid response
     *
     * @param mixed $input
     * @return Response
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
        $text = $this->apply('kirbytext:before', $text);
        $text = $this->kirbytags($text, $data);
        $text = $this->markdown($text);
        $text = $this->apply('kirbytext:after', $text);

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
     * @param Page|null $parent
     * @param bool $drafts
     * @return Page|null
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
        return $this->io($this->call($path, $method));
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

        if ($path === null) {
            return $site->homePage();
        }

        if ($page = $site->find($path)) {
            return $page;
        }

        if ($draft = $site->draft($path)) {
            if ($this->user() || $draft->isVerified(get('token'))) {
                return $draft;
            }
        }

        // try to resolve content representations if the path has an extension
        $extension = F::extension($path);

        // remove the extension from the path
        $path = Str::rtrim($path, '.' . $extension);

        // stop when there's no extension
        if (empty($extension) === true) {
            return null;
        }

        // try to find the page for the representation
        if ($page = $site->find($path)) {
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
        $filename = basename($path) . '.' . $extension;

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
     * @return Responder
     */
    public function response()
    {
        return $this->response = $this->response ?? new Responder;
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
        $system   = (include static::$root . '/config/routes.php')($this);

        return $this->routes = array_merge($system['before'], $registry, $system['after']);
    }

    /**
     * Returns the current session object
     *
     * @param array $options Additional options, see the session component
     * @return Session
     */
    public function session(array $options = [])
    {
        $this->session = $this->session ?? new Session($this->root('sessions'), $this->options['session'] ?? []);
        return $this->session->get($options);
    }

    /**
     * Create your own set of languages
     *
     * @param array $languages
     * @return self
     */
    protected function setLanguages(array $languages = null): self
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

    protected function setRequest(array $request = null): self
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
     * Initializes and returns the Site object
     *
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
     * Uses the snippet component to create
     * and return a template snippet
     *
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
     * Uses the template component to initialize
     * and return the Template object
     *
     * @return Template
     */
    public function template(string $name, string $type = 'html', string $defaultType = 'html'): Template
    {
        return $this->extensions['components']['template']($this, $name, $type, $defaultType);
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
