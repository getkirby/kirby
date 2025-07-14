<?php

namespace Kirby\Cms;

use Closure;
use Exception as GlobalException;
use Generator;
use Kirby\Content\Storage;
use Kirby\Content\VersionCache;
use Kirby\Data\Data;
use Kirby\Email\Email as BaseEmail;
use Kirby\Exception\ErrorPageException;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Http\Environment;
use Kirby\Http\Request;
use Kirby\Http\Response;
use Kirby\Http\Route;
use Kirby\Http\Router;
use Kirby\Http\Uri;
use Kirby\Http\Visitor;
use Kirby\Session\AutoSession;
use Kirby\Session\Session;
use Kirby\Template\Snippet;
use Kirby\Template\Template;
use Kirby\Text\KirbyTag;
use Kirby\Text\KirbyTags;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Config;
use Kirby\Toolkit\Controller;
use Kirby\Toolkit\LazyValue;
use Kirby\Toolkit\Locale;
use Kirby\Toolkit\Str;
use Kirby\Uuid\Uuid;
use Throwable;

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
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class App
{
	use AppCaches;
	use AppErrors;
	use AppPlugins;
	use AppTranslations;
	use AppUsers;

	public const CLASS_ALIAS = 'kirby';

	protected static App|null $instance = null;
	protected static string|null $version = null;

	public array $data = [];

	protected Api|null $api = null;
	protected Collections|null $collections = null;
	protected Core $core;
	protected Language|null $defaultLanguage = null;
	protected Environment|null $environment = null;
	protected Events $events;
	protected Language|null $language = null;
	protected Languages|null $languages = null;
	protected bool|null $multilang = null;
	protected string|null $nonce = null;
	protected array $options;
	protected string|null $path = null;
	protected Request|null $request = null;
	protected Responder|null $response = null;
	protected Roles|null $roles = null;
	protected Ingredients $roots;
	protected array|null $routes = null;
	protected Router|null $router = null;
	protected AutoSession|null $sessionHandler = null;
	protected Site|null $site = null;
	protected System|null $system = null;
	protected Ingredients $urls;
	protected Visitor|null $visitor = null;

	protected array $propertyData;

	/**
	 * Creates a new App instance
	 *
	 * @param bool $setInstance If false, the instance won't be set globally
	 */
	public function __construct(array $props = [], bool $setInstance = true)
	{
		$this->core   = new Core($this);
		$this->events = new Events($this);

		// start with a fresh snippet and version cache
		Snippet::$cache = [];
		VersionCache::reset();

		// register all roots to be able to load stuff afterwards
		$this->bakeRoots($props['roots'] ?? []);

		try {
			// stuff from config and additional options
			$this->optionsFromConfig();
			$this->optionsFromProps($props['options'] ?? []);
			$this->optionsFromEnvironment($props);
		} finally {
			// register the Whoops error handler inside of a
			// try-finally block to ensure it's still registered
			// even if there is a problem loading the configurations
			$this->handleErrors();
		}

		$this->propertyData = $props;

		// a custom request setup must come before defining the path
		$this->setRequest($props['request'] ?? null);

		// set the path to make it available for the url bakery
		$this->setPath($props['path'] ?? null);

		// create all urls after the config, so possible
		// options can be taken into account
		$this->bakeUrls($props['urls'] ?? []);

		// configurable properties
		$this->setLanguages($props['languages'] ?? null);
		$this->setRoles($props['roles'] ?? null);
		$this->setUser($props['user'] ?? null);
		$this->setUsers($props['users'] ?? null);

		// set the singleton
		if (static::$instance === null || $setInstance === true) {
			static::$instance = ModelWithContent::$kirby = $this;
		}

		// setup the I18n class with the translation loader
		$this->i18n();

		// load all extensions
		$this->extensionsFromSystem();
		$this->extensionsFromProps($props);
		$this->extensionsFromPlugins();
		$this->extensionsFromOptions();
		$this->extensionsFromFolders();

		// must be set after the extensions are loaded.
		// the default storage instance must be defined
		// and the App::$instance singleton needs to be set
		$this->setSite($props['site'] ?? null);

		// trigger hook for use in plugins
		$this->trigger('system.loadPlugins:after');

		// execute a ready callback from the config
		$this->optionsFromReadyCallback();

		// bake config
		$this->bakeOptions();
	}

	/**
	 * Improved `var_dump` output
	 *
	 * @codeCoverageIgnore
	 */
	public function __debugInfo(): array
	{
		return [
			'languages' => $this->languages(),
			'options'   => $this->options(),
			'request'   => $this->request(),
			'roots'     => $this->roots(),
			'site'      => $this->site(),
			'urls'      => $this->urls(),
			'version'   => static::version(),
		];
	}

	/**
	 * Returns the Api instance
	 *
	 * @unstable
	 */
	public function api(): Api
	{
		if ($this->api !== null) {
			return $this->api;
		}

		$root       = $this->root('kirby') . '/config/api';
		$extensions = $this->extensions['api'] ?? [];
		$routes     = (include $root . '/routes.php')($this);

		return $this->api = new Api([
			'debug'          => $this->option('debug', false),
			'authentication' => $extensions['authentication'] ?? include $root . '/authentication.php',
			'data'           => $extensions['data'] ?? [],
			'collections'    => [
				...$extensions['collections'] ?? [],
				...include $root . '/collections.php'
			],
			'models'         => [
				...$extensions['models'] ?? [],
				...include $root . '/models.php'
			],
			'routes'         => [
				...$routes,
				...$extensions['routes'] ?? []
			],
			'kirby'          => $this,
		]);
	}

	/**
	 * Applies a hook to the given value
	 *
	 * @param string $name Full event name
	 * @param array $args Associative array of named arguments
	 * @param string|null $modify Key in $args that is modified by the hooks (default: first argument)
	 * @return mixed Resulting value as modified by the hooks
	 */
	public function apply(
		string $name,
		array $args,
		string|null $modify = null
	): mixed {
		return $this->events->apply($name, $args, $modify);
	}

	/**
	 * Normalizes and globally sets the configured options
	 *
	 * @return $this
	 */
	protected function bakeOptions(): static
	{
		// convert the old plugin option syntax to the new one
		foreach ($this->options as $key => $value) {
			// detect option keys with the `vendor.plugin.option` format
			if (preg_match('/^([a-z0-9-]+\.[a-z0-9-]+)\.(.*)$/i', $key, $matches) === 1) {
				[, $plugin, $option] = $matches;

				// verify that it's really a plugin option
				if (isset(static::$plugins[str_replace('.', '/', $plugin)]) !== true) {
					continue;
				}

				// ensure that the target option array exists
				// (which it will if the plugin has any options)
				if (isset($this->options[$plugin]) !== true) {
					$this->options[$plugin] = []; // @codeCoverageIgnore
				}

				// move the option to the plugin option array
				// don't overwrite nested arrays completely but merge them
				$this->options[$plugin] = array_replace_recursive(
					$this->options[$plugin],
					[$option => $value]
				);
				unset($this->options[$key]);
			}
		}

		Config::$data = $this->options;
		return $this;
	}

	/**
	 * Sets the directory structure
	 *
	 * @return $this
	 */
	protected function bakeRoots(array|null $roots = null): static
	{
		$roots = [...$this->core->roots(), ...$roots ?? []];
		$this->roots = Ingredients::bake($roots);
		return $this;
	}

	/**
	 * Sets the Url structure
	 *
	 * @return $this
	 */
	protected function bakeUrls(array|null $urls = null): static
	{
		$urls = [...$this->core->urls(), ...$urls ?? []];
		$this->urls = Ingredients::bake($urls);
		return $this;
	}

	/**
	 * Returns all available blueprints for this installation
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

		try {
			// protect against path traversal attacks
			$root     = $this->root('blueprints') . '/' . $type;
			$realpath = Dir::realpath($root, $this->root('blueprints'));

			foreach (glob($realpath . '/*.yml') as $blueprint) {
				$name = F::name($blueprint);
				$blueprints[$name] = $name;
			}
		} catch (GlobalException) {
			// if the realpath operation failed, the following glob was skipped,
			// keeping just the blueprints from extensions
		}

		ksort($blueprints);

		return array_values($blueprints);
	}

	/**
	 * Calls any Kirby route
	 */
	public function call(string|null $path = null, string|null $method = null): mixed
	{
		$path   ??= $this->path();
		$method ??= $this->request()->method();
		return $this->router()->call($path, $method);
	}

	/**
	 * Creates an instance with the same
	 * initial properties
	 *
	 * @param bool $setInstance If false, the instance won't be set globally
	 */
	public function clone(array $props = [], bool $setInstance = true): static
	{
		$props = array_replace_recursive($this->propertyData, $props);

		$clone = new static($props, $setInstance);
		$clone->data = $this->data;

		return $clone;
	}

	/**
	 * Returns a specific user-defined collection
	 * by name. All relevant dependencies are
	 * automatically injected
	 *
	 * @return \Kirby\Toolkit\Collection|null
	 * @todo 6.0 Add return type declaration
	 */
	public function collection(string $name, array $options = [])
	{
		return $this->collections()->get($name, [
			...$options,
			'kirby' => $this,
			'site'  => $site = $this->site(),
			'pages' => new LazyValue(fn () => $site->children()),
			'users' => new LazyValue(fn () => $this->users())

		]);
	}

	/**
	 * Returns all user-defined collections
	 */
	public function collections(): Collections
	{
		return $this->collections ??= new Collections();
	}

	/**
	 * Returns a core component
	 */
	public function component(string $name): mixed
	{
		return $this->extensions['components'][$name] ?? null;
	}

	/**
	 * Returns the content extension
	 */
	public function contentExtension(): string
	{
		return $this->options['content']['extension'] ?? 'txt';
	}

	/**
	 * Returns files that should be ignored when scanning folders
	 */
	public function contentIgnore(): array
	{
		return $this->options['content']['ignore'] ?? Dir::$ignore;
	}

	/**
	 * Generates a non-guessable token based on model
	 * data and a configured salt
	 *
	 * @param object|null $model Object to pass to the salt callback if configured
	 * @param string $value Model data to include in the generated token
	 */
	public function contentToken(object|null $model, string $value): string
	{
		$default = $this->root('content');

		if ($model !== null && method_exists($model, 'id') === true) {
			$default .= '/' . $model->id();
		}

		$salt = $this->option('content.salt', $default);

		if ($salt instanceof Closure) {
			$salt = $salt($model);
		}

		return hash_hmac('sha1', $value, $salt);
	}

	/**
	 * Calls a page controller by name
	 * and with the given arguments
	 */
	public function controller(
		string $name,
		array $arguments = [],
		string $contentType = 'html'
	): array {
		$name = strtolower($name);
		$data = [];

		// always use the site controller as defaults, if available
		// (unless the controller is a snippet controller)
		if (strpos($name, '/') === false) {
			$site   = $this->controllerLookup('site', $contentType);
			$site ??= $this->controllerLookup('site');
			$data   = (array)$site?->call($this, $arguments) ?? [];
		}

		// try to find a specific representation controller
		$controller   = $this->controllerLookup($name, $contentType);
		// no luck for a specific representation controller?
		// let's try the html controller instead
		$controller ??= $this->controllerLookup($name);

		return [
			...$data,
			...(array)$controller?->call($this, $arguments) ?? []
		];
	}

	/**
	 * Try to find a controller by name
	 */
	protected function controllerLookup(
		string $name,
		string $contentType = 'html'
	): Controller|null {
		if ($contentType !== null && $contentType !== 'html') {
			$name .= '.' . $contentType;
		}

		// controller from site root
		$controller = Controller::load(
			file: $this->root('controllers') . '/' . $name . '.php',
			in:   $this->root('controllers')
		);

		// controller from extension
		$controller ??= $this->extension('controllers', $name);

		if ($controller instanceof Controller) {
			return $controller;
		}

		if ($controller !== null) {
			return new Controller($controller);
		}

		return null;
	}

	/**
	 * Get access to object that lists
	 * all parts of Kirby core
	 */
	public function core(): Core
	{
		return $this->core;
	}

	/**
	 * Checks/returns a CSRF token
	 * @since 3.7.0
	 *
	 * @param string|null $check Pass a token here to compare it to the one in the session
	 * @return string|bool Either the token or a boolean check result
	 */
	public function csrf(string|null $check = null): string|bool
	{
		$session = $this->session();

		// no arguments, generate/return a token
		// (check explicitly if there have been no arguments at all;
		// checking for null introduces a security issue because null could come
		// from user input or bugs in the calling code!)
		if (func_num_args() === 0) {
			$token = $session->get('kirby.csrf');

			if (is_string($token) !== true) {
				$token = bin2hex(random_bytes(32));
				$session->set('kirby.csrf', $token);
			}

			return $token;
		}

		// argument has been passed, check the token
		if (
			is_string($check) === true &&
			is_string($session->get('kirby.csrf')) === true
		) {
			return hash_equals($session->get('kirby.csrf'), $check) === true;
		}

		return false;
	}

	/**
	 * Checks if CORS support is enabled
	 * @since 5.2.0
	 */
	public function isCorsEnabled(): bool
	{
		return $this->option('cors', false) !== false;
	}

	/**
	 * Returns the current language, if set by `static::setCurrentLanguage`
	 */
	public function currentLanguage(): Language|null
	{
		return $this->language ??= $this->defaultLanguage();
	}

	/**
	 * Returns the default language object
	 */
	public function defaultLanguage(): Language|null
	{
		return $this->defaultLanguage ??= $this->languages()->default();
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
	 * Detect the preferred language from the visitor object
	 */
	public function detectedLanguage(): Language|null
	{
		$languages = $this->languages();
		$visitor   = $this->visitor();

		foreach ($visitor->acceptedLanguages() as $acceptedLang) {
			$acceptedCode   = $acceptedLang->code();
			$acceptedLocale = $acceptedLang->locale();

			$match = fn (Language $language, int $precision) =>
				Str::substr($language->locale(LC_ALL), 0, $precision) ===
				Str::substr($acceptedLocale, 0, $precision);

			// Find exact locale matches (e.g. en_GB => en_GB)
			if ($language = $languages->filter(fn ($language) => $match($language, 5))?->first()) {
				return $language;
			}

			// Find exact code matches
			if ($language = $languages->findBy('code', $acceptedCode)) {
				return $language;
			}

			// Find broad locale matches (e.g. en_GB => en)
			if ($language = $languages->filter(fn ($language) => $match($language, 2))?->first()) {
				return $language;
			}
		}

		return $this->defaultLanguage();
	}

	/**
	 * Returns the Email singleton
	 */
	public function email(mixed $preset = [], array $props = []): BaseEmail
	{
		$debug = $props['debug'] ?? false;
		$props = (new Email($preset, $props))->toArray();

		return ($this->component('email'))($this, $props, $debug);
	}

	/**
	 * Returns the environment object with access
	 * to the detected host, base url and dedicated options
	 */
	public function environment(): Environment
	{
		return $this->environment ??= new Environment();
	}

	/**
	 * Finds any file in the content directory
	 */
	public function file(
		string $path,
		mixed $parent = null,
		bool $drafts = true
	): File|null {
		// find by global UUID
		if (Uuid::is($path, 'file') === true) {
			// prefer files of parent, when parent given
			return Uuid::for($path, $parent?->files())->model();
		}

		$parent ??= $this->site();
		$id       = dirname($path);
		$filename = basename($path);

		if ($parent instanceof User) {
			return $parent->file($filename);
		}

		if ($parent instanceof File) {
			$parent = $parent->parent();
		}

		if ($id === '.') {
			return $parent->file($filename) ?? $this->site()->file($filename);
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
	 * Return an image from any page
	 * specified by the path
	 *
	 * Example:
	 * <?= $kirby->image('some/page/myimage.jpg') ?>
	 *
	 * @todo merge with App::file()
	 */
	public function image(string|null $path = null): File|null
	{
		if ($path === null) {
			return $this->site()->page()->image();
		}

		$uri      = dirname($path);
		$filename = basename($path);

		if ($uri === '.') {
			$uri = null;
		}

		$parent = match ($uri) {
			'/'     => $this->site(),
			null    => $this->site()->page(),
			default => $this->site()->page($uri)
		};

		return $parent?->image($filename);
	}

	/**
	 * Returns the current App instance
	 *
	 * @param bool $lazy If `true`, the instance is only returned if already existing
	 * @psalm-return ($lazy is false ? static : static|null)
	 */
	public static function instance(
		self|null $instance = null,
		bool $lazy = false
	): static|null {
		if ($instance !== null) {
			return static::$instance = $instance;
		}

		if ($lazy === true) {
			return static::$instance;
		}

		return static::$instance ?? new static();
	}

	/**
	 * Takes almost any kind of input and
	 * tries to convert it into a valid response
	 *
	 * @unstable
	 */
	public function io(mixed $input): Response
	{
		// use the current response configuration
		$response = $this->response();

		// any direct exception will be turned into an error page
		if ($input instanceof Throwable) {
			$message = $input->getMessage();
			$code    = match (true) {
				$input instanceof Exception => $input->getHttpCode(),
				default                     => $input->getCode()
			};

			if ($code < 400 || $code > 599) {
				$code = 500;
			}

			if ($errorPage = $this->site()->errorPage()) {
				return $response->code($code)->send($errorPage->render([
					'errorCode'    => $code,
					'errorMessage' => $message,
					'errorType'    => $input::class
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

		// (Modified) global response configuration, e.g. in routes
		if ($input instanceof Responder) {
			// return the passed object unmodified (without injecting headers
			// from the global object) to allow a complete response override
			// https://github.com/getkirby/kirby/pull/4144#issuecomment-1034766726
			return $input->send();
		}

		// Responses
		if ($input instanceof Response) {
			return $response->send($input);
		}

		// Pages
		if ($input instanceof Page) {
			try {
				$html = $input->render();
			} catch (ErrorPageException|NotFoundException $e) {
				return $this->io($e);
			}

			if (
				$input->isErrorPage() === true &&
				$response->code() === null
			) {
				$response->code(404);
			}

			return $response->send($html);
		}

		// Files
		if ($input instanceof File) {
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

		throw new InvalidArgumentException(
			message: 'Unexpected input'
		);
	}

	/**
	 * Renders a single KirbyTag with the given attributes
	 *
	 * @param string|array $type Tag type or array with all tag arguments
	 *                           (the key of the first element becomes the type)
	 */
	public function kirbytag(
		string|array $type,
		string|null $value = null,
		array $attr = [],
		array $data = []
	): string {
		if (is_array($type) === true) {
			$kirbytag = $type;
			$type     = key($kirbytag);
			$value    = current($kirbytag);
			$attr     = $kirbytag;

			// check data attribute and separate from attr data if exists
			if (isset($attr['data']) === true) {
				$data = $attr['data'];
				unset($attr['data']);
			}
		}

		$data['kirby']  ??= $this;
		$data['site']   ??= $data['kirby']->site();
		$data['parent'] ??= $data['site']->page();

		return (new KirbyTag($type, $value, $attr, $data, $this->options))->render();
	}

	/**
	 * Parses and resolves KirbyTags in text
	 */
	public function kirbytags(
		string|null $text = null,
		array $data = []
	): string {
		$data['kirby']  ??= $this;
		$data['site']   ??= $data['kirby']->site();
		$data['parent'] ??= $data['site']->page();

		$options = $this->options;

		$text = $this->apply('kirbytags:before', compact('text', 'data', 'options'));
		$text = KirbyTags::parse($text, $data, $options);
		$text = $this->apply('kirbytags:after', compact('text', 'data', 'options'));

		return $text;
	}

	/**
	 * Parses KirbyTags first and Markdown afterwards
	 */
	public function kirbytext(
		string|null $text = null,
		array $options = []
	): string {
		$text = $this->apply('kirbytext:before', compact('text'));
		$text = $this->kirbytags($text, $options);
		$text = $this->markdown($text, $options['markdown'] ?? []);

		if ($this->option('smartypants', false) !== false) {
			$text = $this->smartypants($text);
		}

		$text = $this->apply('kirbytext:after', compact('text'));

		return $text;
	}

	/**
	 * Returns the language by code or shortcut (`default`, `current`).
	 * Passing `null` is an alias for passing `current`
	 */
	public function language(string|null $code = null): Language|null
	{
		if ($this->multilang() === false) {
			return null;
		}

		return match ($code ?? 'current') {
			'default' => $this->defaultLanguage(),
			'current' => $this->currentLanguage(),
			default   => $this->languages()->find($code)
		};
	}

	/**
	 * Returns the current language code
	 */
	public function languageCode(string|null $languageCode = null): string|null
	{
		return $this->language($languageCode)?->code();
	}

	/**
	 * Returns all available site languages
	 */
	public function languages(bool $clone = true): Languages
	{
		if ($clone === false) {
			$this->multilang = null;
			$this->defaultLanguage = null;
		}

		if ($this->languages !== null) {
			return match($clone) {
				true  => clone $this->languages,
				false => $this->languages
			};
		}

		return $this->languages = Languages::load();
	}

	/**
	 * Access Kirby's part loader
	 */
	public function load(): Loader
	{
		return new Loader($this);
	}

	/**
	 * Parses Markdown
	 */
	public function markdown(string|null $text = null, array|null $options = null): string
	{
		// merge global options with local options
		$options = [
			...$this->options['markdown'] ?? [],
			...$options ?? []
		];

		return ($this->component('markdown'))($this, $text, $options);
	}

	/**
	 * Yields all models (site, pages, files and users) of this site
	 * @since 4.0.0
	 *
	 * @return \Generator|\Kirby\Cms\ModelWithContent[]
	 */
	public function models(): Generator
	{
		$site = $this->site();

		yield from $site->files();
		yield $site;

		foreach ($site->index(true) as $page) {
			yield from $page->files();
			yield $page;
		}

		foreach ($this->users() as $user) {
			yield from $user->files();
			yield $user;
		}
	}

	/**
	 * Check for a multilang setup
	 */
	public function multilang(): bool
	{
		return $this->multilang ??= $this->languages()->count() !== 0;
	}

	/**
	 * Returns the nonce, which is used
	 * in the panel for inline scripts
	 * @since 3.3.0
	 */
	public function nonce(): string
	{
		return $this->nonce ??= base64_encode(random_bytes(20));
	}

	/**
	 * Load a specific configuration option
	 */
	public function option(string $key, mixed $default = null): mixed
	{
		return A::get($this->options, $key, $default);
	}

	/**
	 * Returns all configuration options
	 */
	public function options(): array
	{
		return $this->options;
	}

	/**
	 * Load all options from files in site/config
	 */
	protected function optionsFromConfig(): array
	{
		// create an empty config container
		Config::$data = [];

		// load the main config options
		$root    = $this->root('config');
		$options = F::load($root . '/config.php', [], allowOutput: false);

		// merge into one clean options array
		return $this->options = array_replace_recursive(Config::$data, $options);
	}

	/**
	 * Load all options for the current
	 * server environment
	 */
	protected function optionsFromEnvironment(array $props = []): array
	{
		$root = $this->root('config');

		// first load `config/env.php` to access its `url` option
		$envOptions = F::load($root . '/env.php', [], allowOutput: false);

		// use the option from the main `config.php`,
		// but allow the `env.php` to override it
		$globalUrl = $envOptions['url'] ?? $this->options['url'] ?? null;

		// create the URL setup based on hostname and server IP address
		$this->environment = new Environment([
			'allowed' => $globalUrl,
			'cli'     => $props['cli'] ?? null,
		], $props['server'] ?? null);

		// merge into one clean options array;
		// the `env.php` options always override everything else
		$hostAddrOptions = $this->environment()->options($root);
		$this->options = array_replace_recursive(
			$this->options,
			$hostAddrOptions,
			$envOptions
		);

		// reload the environment if the host/address config has overridden
		// the `url` option; this ensures that the base URL is correct
		$envUrl = $this->options['url'] ?? null;
		if ($envUrl !== $globalUrl) {
			$this->environment->detect([
				'allowed' => $envUrl,
				'cli'     => $props['cli'] ?? null
			], $props['server'] ?? null);
		}

		return $this->options;
	}

	/**
	 * Inject options from Kirby instance props
	 */
	protected function optionsFromProps(array $options = []): array
	{
		return $this->options = array_replace_recursive(
			$this->options,
			$options
		);
	}

	/**
	 * Merge last-minute options from ready callback
	 */
	protected function optionsFromReadyCallback(): array
	{
		if (
			isset($this->options['ready']) === true &&
			is_callable($this->options['ready']) === true
		) {
			// fetch last-minute options from the callback
			$options = (array)$this->options['ready']($this);

			// inject all last-minute options recursively
			$this->options = array_replace_recursive($this->options, $options);

			// update the system with changed options
			if (
				isset($options['debug']) === true ||
				isset($options['whoops']) === true ||
				isset($options['editor']) === true
			) {
				$this->handleErrors();
			}

			if (isset($options['debug']) === true) {
				$this->api = null;
			}

			if (
				isset($options['home']) === true ||
				isset($options['error']) === true
			) {
				$this->site = null;
			}

			// checks custom language definition for slugs
			if ($slugsOption = $this->option('slugs')) {
				// slugs option must be set to string or
				// "slugs" => ["language" => "de"] as array
				if (
					is_string($slugsOption) === true ||
					isset($slugsOption['language']) === true
				) {
					$this->i18n();
				}
			}
		}

		return $this->options;
	}

	/**
	 * Returns any page from the content folder
	 */
	public function page(
		string|null $id = null,
		Page|Site|null $parent = null,
		bool $drafts = true
	): Page|null {
		if ($id === null) {
			return null;
		}

		$parent ??= $this->site();

		if ($page = $parent->find($id)) {
			/**
			 * We passed a single $id, we can be sure that the result is
			 * @var \Kirby\Cms\Page $page
			 */
			return $page;
		}

		if ($drafts === true && $draft = $parent->draft($id)) {
			return $draft;
		}

		return null;
	}

	/**
	 * Returns the request path
	 */
	public function path(): string
	{
		if (is_string($this->path) === true) {
			return $this->path;
		}

		$current = $this->request()->path()->toString();
		$index   = $this->environment()->baseUri()->path()->toString();
		$path    = Str::afterStart($current, $index);

		return $this->setPath($path)->path;
	}

	/**
	 * Returns the Response object for the
	 * current request
	 */
	public function render(
		string|null $path = null,
		string|null $method = null
	): Response|null {
		if ((filter_var($_ENV['KIRBY_RENDER'] ?? true, FILTER_VALIDATE_BOOLEAN)) === false) {
			return null;
		}

		return $this->io($this->call($path, $method));
	}

	/**
	 * Returns the Request singleton
	 */
	public function request(): Request
	{
		if ($this->request !== null) {
			return $this->request;
		}

		$env = $this->environment();

		return $this->request = new Request([
			'cli' => $env->cli(),
			'url' => $env->requestUri()
		]);
	}

	/**
	 * Path resolver for the router
	 *
	 * @unstable
	 * @throws \Kirby\Exception\NotFoundException if the home page cannot be found
	 */
	public function resolve(
		string|null $path = null,
		string|null $language = null
	): mixed {
		// set the current translation
		$this->setCurrentTranslation($language);

		// set the current locale
		$this->setCurrentLanguage($language);

		// directly prevent path with incomplete content representation
		if (Str::endsWith($path, '.') === true) {
			return null;
		}

		// the site is needed a couple times here
		$site = $this->site();

		// use the home page
		if ($path === null) {
			if ($homePage = $site->homePage()) {
				return $homePage;
			}

			throw new NotFoundException(
				message: 'The home page does not exist'
			);
		}

		// search for the page by path
		$page = $site->find($path);

		// search for a draft if the page cannot be found
		if (!$page && $draft = $site->draft($path)) {
			if (
				$this->user() ||
				$draft->renderVersionFromRequest() !== null
			) {
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
			// if extension is the default content type,
			// redirect to page URL without extension
			if ($extension === 'html') {
				return Response::redirect($page->url(), 301);
			}

			try {
				$response = $this->response();
				$output   = $page->render([], $extension);

				// attach a MIME type based on the representation
				// only if no custom MIME type was set
				if ($response->type() === null) {
					$response->type($extension);
				}

				return $response->body($output);
			} catch (NotFoundException) {
				return null;
			}
		}

		// try to resolve clean URLs to site files
		if (str_contains($path, '/') === false) {
			return $this->resolveFile($site->file($path));
		}

		$id       = dirname($path);
		$filename = basename($path);

		// try to resolve clean URLs to files for pages and drafts
		if ($page = $site->findPageOrDraft($id)) {
			return $this->resolveFile($page->file($filename));
		}

		// none of our resolvers were successful
		return null;
	}

	/**
	 * Filters a resolved file object using the configuration
	 * @internal
	 */
	public function resolveFile(File|null $file): File|null
	{
		// shortcut for files that don't exist
		if ($file === null) {
			return null;
		}

		$option = $this->option('content.fileRedirects', false);

		if ($option === true) {
			return $file;
		}

		if ($option instanceof Closure) {
			return $option($file) === true ? $file : null;
		}

		// option was set to `false` or an invalid value
		return null;
	}

	/**
	 * Response configuration
	 */
	public function response(): Responder
	{
		return $this->response ??= new Responder();
	}

	/**
	 * Returns a system root
	 */
	public function root(string $type = 'index'): string|null
	{
		return $this->roots->__get($type);
	}

	/**
	 * Returns the directory structure
	 */
	public function roots(): Ingredients
	{
		return $this->roots;
	}

	/**
	 * Returns the currently active route
	 */
	public function route(): Route|null
	{
		return $this->router()->route();
	}

	/**
	 * Returns the Router singleton
	 */
	public function router(): Router
	{
		if ($this->router !== null) {
			return $this->router;
		}

		$routes = $this->routes();

		if ($this->multilang() === true) {
			foreach ($routes as $index => $route) {
				if (empty($route['language']) === false) {
					unset($routes[$index]);
				}
			}
		}

		$hooks = [
			'beforeEach' => function ($route, $path, $method) {
				$this->trigger('route:before', compact('route', 'path', 'method'));
			},
			'afterEach' => function ($route, $path, $method, $result, $final) {
				return $this->apply('route:after', compact('route', 'path', 'method', 'result', 'final'), 'result');
			}
		];

		return $this->router = new Router($routes, $hooks);
	}

	/**
	 * Returns all defined routes
	 */
	public function routes(): array
	{
		if (is_array($this->routes) === true) {
			return $this->routes;
		}

		$registry = $this->extensions('routes');
		$system   = $this->core->routes();
		$routes   = [...$system['before'], ...$registry, ...$system['after']];

		return $this->routes = $routes;
	}

	/**
	 * Returns the current session object
	 *
	 * @param array $options Additional options, see the session component
	 */
	public function session(array $options = []): Session
	{
		$session = $this->sessionHandler()->get($options);

		// disable caching for sessions that use the `Authorization` header;
		// cookie sessions are already covered by the `Cookie` class
		if ($session->mode() === 'manual') {
			$this->response()->cache(false);
			$this->response()->header('Cache-Control', 'no-store, private', true);
		}

		return $session;
	}

	/**
	 * Returns the session handler
	 */
	public function sessionHandler(): AutoSession
	{
		return $this->sessionHandler ??= new AutoSession(
			($this->component('session::store'))($this),
			$this->option('session', [])
		);
	}

	/**
	 * Load and set the current language if it exists
	 * Otherwise fall back to the default language
	 */
	public function setCurrentLanguage(
		string|null $languageCode = null
	): Language|null {
		if ($this->multilang() === false) {
			Locale::set($this->option('locale', 'en_US.utf-8'));
			return $this->language = null;
		}

		$this->language   = $this->language($languageCode);
		$this->language ??= $this->defaultLanguage();

		Locale::set($this->language->locale());

		// add language slug rules to Str class
		Str::$language = $this->language->rules();

		return $this->language;
	}

	/**
	 * Create your own set of languages
	 *
	 * @return $this
	 */
	protected function setLanguages(array|null $languages = null): static
	{
		if ($languages !== null) {
			$objects = [];

			foreach ($languages as $props) {
				$objects[] = new Language($props);
			}

			$this->languages = new Languages($objects);
		}

		return $this;
	}

	/**
	 * Sets the request path that is
	 * used for the router
	 *
	 * @return $this
	 */
	protected function setPath(string|null $path = null): static
	{
		$this->path = $path !== null ? trim($path, '/') : null;
		return $this;
	}

	/**
	 * Sets the request
	 *
	 * @return $this
	 */
	protected function setRequest(array|null $request = null): static
	{
		if ($request !== null) {
			$this->request = new Request($request);
		}

		return $this;
	}

	/**
	 * Create your own set of roles
	 *
	 * @return $this
	 */
	protected function setRoles(array|null $roles = null): static
	{
		if ($roles !== null) {
			$this->roles = Roles::factory($roles);
		}

		return $this;
	}

	/**
	 * Sets a custom Site object
	 *
	 * @return $this
	 */
	public function setSite(Site|array|null $site = null): static
	{
		if (is_array($site) === true) {
			$site = new Site($site);
		}

		$this->site = $site;
		return $this;
	}

	/**
	 * Initializes and returns the Site object
	 */
	public function site(): Site
	{
		return $this->site ??= new Site([
			'errorPageId' => $this->options['error'] ?? 'error',
			'homePageId'  => $this->options['home']  ?? 'home',
			'url'         => $this->url('index'),
		]);
	}

	/**
	 * Applies the smartypants rule on the text
	 */
	public function smartypants(string|null $text = null): string
	{
		$options = $this->option('smartypants', []);

		if ($options === false) {
			return $text;
		}

		if (is_array($options) === false) {
			$options = [];
		}

		if ($this->multilang() === true) {
			$languageSmartypants = $this->language()->smartypants() ?? [];

			if ($languageSmartypants !== []) {
				$options = [...$options, ...$languageSmartypants];
			}
		}

		return ($this->component('smartypants'))($this, $text, $options);
	}

	/**
	 * Uses the snippet component to create
	 * and return a template snippet
	 *
	 * @param array|object $data Variables or an object that becomes `$item`
	 * @param bool $return On `false`, directly echo the snippet
	 * @psalm-return ($return is true ? string : null)
	 */
	public function snippet(
		string|array|null $name,
		array|object $data = [],
		bool $return = true,
		bool $slots = false
	): Snippet|string|null {
		if (is_object($data) === true) {
			$data = ['item' => $data];
		}

		$snippet = ($this->component('snippet'))(
			$this,
			$name,
			[...$this->data, ...$data],
			$slots
		);

		if ($return === true || $slots === true) {
			return $snippet;
		}

		echo $snippet;
		return null;
	}

	/**
	 * Returns the default storage instance for a given Model
	 */
	public function storage(ModelWithContent $model): Storage
	{
		return $this->component('storage')($this, $model);
	}

	/**
	 * System check class
	 */
	public function system(): System
	{
		return $this->system ??= new System($this);
	}

	/**
	 * Uses the template component to initialize
	 * and return the Template object
	 */
	public function template(
		string $name,
		string $type = 'html',
		string $defaultType = 'html'
	): Template {
		return ($this->component('template'))($this, $name, $type, $defaultType);
	}

	/**
	 * Thumbnail creator
	 */
	public function thumb(string $src, string $dst, array $options = []): string
	{
		return ($this->component('thumb'))($this, $src, $dst, $options);
	}

	/**
	 * Trigger a hook by name
	 *
	 * @param string $name Full event name
	 * @param array $args Associative array of named arguments
	 */
	public function trigger(
		string $name,
		array $args = []
	): void {
		$this->events->trigger($name, $args);
	}

	/**
	 * Returns a system url
	 *
	 * @param bool $object If set to `true`, the URL is converted to an object
	 * @psalm-return ($object is false ? string|null : \Kirby\Http\Uri)
	 */
	public function url(
		string $type = 'index',
		bool $object = false
	): string|Uri|null {
		$url = $this->urls->__get($type);

		if ($object === true) {
			if (Url::isAbsolute($url)) {
				return Url::toObject($url);
			}

			// index URL was configured without host, use the current host
			return Uri::current([
				'path'   => $url,
				'query'  => null
			]);
		}

		return $url;
	}

	/**
	 * Returns the url structure
	 */
	public function urls(): Ingredients
	{
		return $this->urls;
	}

	/**
	 * Returns the current version number from
	 * the composer.json (Keep that up to date! :))
	 *
	 * @throws \Kirby\Exception\LogicException if the Kirby version cannot be detected
	 */
	public static function version(): string|null
	{
		try {
			return static::$version ??= Data::read(dirname(__DIR__, 2) . '/composer.json')['version'] ?? null;
		} catch (Throwable) {
			throw new LogicException(
				message: 'The Kirby version cannot be detected. The composer.json is probably missing or not readable.'
			);
		}
	}

	/**
	 * Creates a hash of the version number
	 */
	public static function versionHash(): string
	{
		return md5(static::version());
	}

	/**
	 * Returns the visitor object
	 */
	public function visitor(): Visitor
	{
		return $this->visitor ??= new Visitor();
	}
}
