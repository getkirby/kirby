<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Http\Response;
use Kirby\Http\Uri;
use Kirby\Panel\Page as Panel;
use Kirby\Toolkit\A;

/**
 * The `$page` object is the heart and
 * soul of Kirby. It is used to construct
 * pages and all their dependencies like
 * children, files, content, etc.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Page extends ModelWithContent
{
	use PageActions;
	use PageSiblings;
	use HasChildren;
	use HasFiles;
	use HasMethods;
	use HasSiblings;

	public const CLASS_ALIAS = 'page';

	/**
	 * All registered page methods
	 *
	 * @var array
	 */
	public static $methods = [];

	/**
	 * Registry with all Page models
	 *
	 * @var array
	 */
	public static $models = [];

	/**
	 * The PageBlueprint object
	 *
	 * @var \Kirby\Cms\PageBlueprint
	 */
	protected $blueprint;

	/**
	 * Nesting level
	 *
	 * @var int
	 */
	protected $depth;

	/**
	 * Sorting number + slug
	 *
	 * @var string
	 */
	protected $dirname;

	/**
	 * Path of dirnames
	 *
	 * @var string
	 */
	protected $diruri;

	/**
	 * Draft status flag
	 *
	 * @var bool
	 */
	protected $isDraft;

	/**
	 * The Page id
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * The template, that should be loaded
	 * if it exists
	 *
	 * @var \Kirby\Cms\Template
	 */
	protected $intendedTemplate;

	/**
	 * @var array
	 */
	protected $inventory;

	/**
	 * The sorting number
	 *
	 * @var int|null
	 */
	protected $num;

	/**
	 * The parent page
	 *
	 * @var \Kirby\Cms\Page|null
	 */
	protected $parent;

	/**
	 * Absolute path to the page directory
	 *
	 * @var string
	 */
	protected $root;

	/**
	 * The parent Site object
	 *
	 * @var \Kirby\Cms\Site|null
	 */
	protected $site;

	/**
	 * The URL-appendix aka slug
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * The intended page template
	 *
	 * @var \Kirby\Cms\Template
	 */
	protected $template;

	/**
	 * The page url
	 *
	 * @var string|null
	 */
	protected $url;

	/**
	 * Magic caller
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

		// page methods
		if ($this->hasMethod($method)) {
			return $this->callMethod($method, $arguments);
		}

		// return page content otherwise
		return $this->content()->get($method);
	}

	/**
	 * Creates a new page object
	 *
	 * @param array $props
	 */
	public function __construct(array $props)
	{
		// set the slug as the first property
		$this->slug = $props['slug'] ?? null;

		// add all other properties
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
			'content'      => $this->content(),
			'children'     => $this->children(),
			'siblings'     => $this->siblings(),
			'translations' => $this->translations(),
			'files'        => $this->files(),
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
			return 'pages/' . $this->panel()->id();
		}

		return $this->kirby()->url('api') . '/pages/' . $this->panel()->id();
	}

	/**
	 * Returns the blueprint object
	 *
	 * @return \Kirby\Cms\PageBlueprint
	 */
	public function blueprint()
	{
		if ($this->blueprint instanceof PageBlueprint) {
			return $this->blueprint;
		}

		return $this->blueprint = PageBlueprint::factory('pages/' . $this->intendedTemplate(), 'pages/default', $this);
	}

	/**
	 * Returns an array with all blueprints that are available for the page
	 *
	 * @param string|null $inSection
	 * @return array
	 */
	public function blueprints(string|null $inSection = null): array
	{
		if ($inSection !== null) {
			return $this->blueprint()->section($inSection)->blueprints();
		}

		$blueprints      = [];
		$templates       = $this->blueprint()->changeTemplate() ?? $this->blueprint()->options()['changeTemplate'] ?? [];
		$currentTemplate = $this->intendedTemplate()->name();

		if (is_array($templates) === false) {
			$templates = [];
		}

		// add the current template to the array if it's not already there
		if (in_array($currentTemplate, $templates) === false) {
			array_unshift($templates, $currentTemplate);
		}

		// make sure every template is only included once
		$templates = array_unique($templates);

		foreach ($templates as $template) {
			try {
				$props = Blueprint::load('pages/' . $template);

				$blueprints[] = [
					'name'  => basename($props['name']),
					'title' => $props['title'],
				];
			} catch (Exception) {
				// skip invalid blueprints
			}
		}

		return array_values($blueprints);
	}

	/**
	 * Builds the cache id for the page
	 *
	 * @param string $contentType
	 * @return string
	 */
	protected function cacheId(string $contentType): string
	{
		$cacheId = [$this->id()];

		if ($this->kirby()->multilang() === true) {
			$cacheId[] = $this->kirby()->language()->code();
		}

		$cacheId[] = $contentType;

		return implode('.', $cacheId);
	}

	/**
	 * Prepares the content for the write method
	 *
	 * @internal
	 * @param array $data
	 * @param string|null $languageCode
	 * @return array
	 */
	public function contentFileData(array $data, string|null $languageCode = null): array
	{
		return A::prepend($data, [
			'title' => $data['title'] ?? null,
			'slug'  => $data['slug']  ?? null
		]);
	}

	/**
	 * Returns the content text file
	 * which is found by the inventory method
	 *
	 * @internal
	 * @param string|null $languageCode
	 * @return string
	 */
	public function contentFileName(string|null $languageCode = null): string
	{
		return $this->intendedTemplate()->name();
	}

	/**
	 * Call the page controller
	 *
	 * @internal
	 * @param array $data
	 * @param string $contentType
	 * @return array
	 * @throws \Kirby\Exception\InvalidArgumentException If the controller returns invalid objects for `kirby`, `site`, `pages` or `page`
	 */
	public function controller(array $data = [], string $contentType = 'html'): array
	{
		// create the template data
		$data = array_merge($data, [
			'kirby' => $kirby = $this->kirby(),
			'site'  => $site  = $this->site(),
			'pages' => $site->children(),
			'page'  => $site->visit($this)
		]);

		// call the template controller if there's one.
		$controllerData = $kirby->controller($this->template()->name(), $data, $contentType);

		// merge controller data with original data safely
		if (empty($controllerData) === false) {
			$classes = [
				'kirby' => 'Kirby\Cms\App',
				'site'  => 'Kirby\Cms\Site',
				'pages' => 'Kirby\Cms\Pages',
				'page'  => 'Kirby\Cms\Page'
			];

			foreach ($controllerData as $key => $value) {
				if (array_key_exists($key, $classes) === true) {
					if ($value instanceof $classes[$key]) {
						$data[$key] = $value;
					} else {
						throw new InvalidArgumentException('The returned variable "' . $key . '" from the controller "' . $this->template()->name() . '" is not of the required type "' . $classes[$key] . '"');
					}
				} else {
					$data[$key] = $value;
				}
			}
		}

		return $data;
	}

	/**
	 * Returns a number indicating how deep the page
	 * is nested within the content folder
	 *
	 * @return int
	 */
	public function depth(): int
	{
		return $this->depth ??= (substr_count($this->id(), '/') + 1);
	}

	/**
	 * Sorting number + Slug
	 *
	 * @return string
	 */
	public function dirname(): string
	{
		if ($this->dirname !== null) {
			return $this->dirname;
		}

		if ($this->num() !== null) {
			return $this->dirname = $this->num() . Dir::$numSeparator . $this->uid();
		}

		return $this->dirname = $this->uid();
	}

	/**
	 * Sorting number + Slug
	 *
	 * @return string
	 */
	public function diruri(): string
	{
		if (is_string($this->diruri) === true) {
			return $this->diruri;
		}

		if ($this->isDraft() === true) {
			$dirname = '_drafts/' . $this->dirname();
		} else {
			$dirname = $this->dirname();
		}

		if ($parent = $this->parent()) {
			return $this->diruri = $parent->diruri() . '/' . $dirname;
		}

		return $this->diruri = $dirname;
	}

	/**
	 * Checks if the page exists on disk
	 *
	 * @return bool
	 */
	public function exists(): bool
	{
		return is_dir($this->root()) === true;
	}

	/**
	 * Constructs a Page object and also
	 * takes page models into account.
	 *
	 * @internal
	 * @param mixed $props
	 * @return static
	 */
	public static function factory($props)
	{
		if (empty($props['model']) === false) {
			return static::model($props['model'], $props);
		}

		return new static($props);
	}

	/**
	 * Redirects to this page,
	 * wrapper for the `go()` helper
	 *
	 * @since 3.4.0
	 *
	 * @param array $options Options for `Kirby\Http\Uri` to create URL parts
	 * @param int $code HTTP status code
	 */
	public function go(array $options = [], int $code = 302)
	{
		Response::go($this->url($options), $code);
	}

	/**
	 * Checks if the intended template
	 * for the page exists.
	 *
	 * @return bool
	 */
	public function hasTemplate(): bool
	{
		return $this->intendedTemplate() === $this->template();
	}

	/**
	 * Returns the Page Id
	 *
	 * @return string
	 */
	public function id(): string
	{
		if ($this->id !== null) {
			return $this->id;
		}

		// set the id, depending on the parent
		if ($parent = $this->parent()) {
			return $this->id = $parent->id() . '/' . $this->uid();
		}

		return $this->id = $this->uid();
	}

	/**
	 * Returns the template that should be
	 * loaded if it exists.
	 *
	 * @return \Kirby\Cms\Template
	 */
	public function intendedTemplate()
	{
		if ($this->intendedTemplate !== null) {
			return $this->intendedTemplate;
		}

		return $this->setTemplate($this->inventory()['template'])->intendedTemplate();
	}

	/**
	 * Returns the inventory of files
	 * children and content files
	 *
	 * @internal
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
	 * Compares the current object with the given page object
	 *
	 * @param \Kirby\Cms\Page|string $page
	 * @return bool
	 */
	public function is($page): bool
	{
		if ($page instanceof self === false) {
			if (is_string($page) === false) {
				return false;
			}

			$page = $this->kirby()->page($page);
		}

		if ($page instanceof self === false) {
			return false;
		}

		return $this->id() === $page->id();
	}

	/**
	 * Checks if the page is the current page
	 *
	 * @return bool
	 */
	public function isActive(): bool
	{
		if ($this->site()->page()?->is($this) === true) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if the page is a direct or indirect ancestor of the given $page object
	 *
	 * @param Page $child
	 * @return bool
	 */
	public function isAncestorOf(Page $child): bool
	{
		return $child->parents()->has($this->id()) === true;
	}

	/**
	 * Checks if the page can be cached in the
	 * pages cache. This will also check if one
	 * of the ignore rules from the config kick in.
	 *
	 * @return bool
	 */
	public function isCacheable(): bool
	{
		$kirby   = $this->kirby();
		$cache   = $kirby->cache('pages');
		$options = $cache->options();
		$ignore  = $options['ignore'] ?? null;

		// the pages cache is switched off
		if (($options['active'] ?? false) === false) {
			return false;
		}

		// inspect the current request
		$request = $kirby->request();

		// disable the pages cache for any request types but GET or HEAD
		if (in_array($request->method(), ['GET', 'HEAD']) === false) {
			return false;
		}

		// disable the pages cache when there's request data
		if (empty($request->data()) === false) {
			return false;
		}

		// disable the pages cache when there are any params
		if ($request->params()->isNotEmpty()) {
			return false;
		}

		// check for a custom ignore rule
		if ($ignore instanceof Closure) {
			if ($ignore($this) === true) {
				return false;
			}
		}

		// ignore pages by id
		if (is_array($ignore) === true) {
			if (in_array($this->id(), $ignore) === true) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Checks if the page is a child of the given page
	 *
	 * @param \Kirby\Cms\Page|string $parent
	 * @return bool
	 */
	public function isChildOf($parent): bool
	{
		return $this->parent()?->is($parent) ?? false;
	}

	/**
	 * Checks if the page is a descendant of the given page
	 *
	 * @param \Kirby\Cms\Page|string $parent
	 * @return bool
	 */
	public function isDescendantOf($parent): bool
	{
		if (is_string($parent) === true) {
			$parent = $this->site()->find($parent);
		}

		if (!$parent) {
			return false;
		}

		return $this->parents()->has($parent->id()) === true;
	}

	/**
	 * Checks if the page is a descendant of the currently active page
	 *
	 * @return bool
	 */
	public function isDescendantOfActive(): bool
	{
		if ($active = $this->site()->page()) {
			return $this->isDescendantOf($active);
		}

		return false;
	}

	/**
	 * Checks if the current page is a draft
	 *
	 * @return bool
	 */
	public function isDraft(): bool
	{
		return $this->isDraft;
	}

	/**
	 * Checks if the page is the error page
	 *
	 * @return bool
	 */
	public function isErrorPage(): bool
	{
		return $this->id() === $this->site()->errorPageId();
	}

	/**
	 * Checks if the page is the home page
	 *
	 * @return bool
	 */
	public function isHomePage(): bool
	{
		return $this->id() === $this->site()->homePageId();
	}

	/**
	 * It's often required to check for the
	 * home and error page to stop certain
	 * actions. That's why there's a shortcut.
	 *
	 * @return bool
	 */
	public function isHomeOrErrorPage(): bool
	{
		return $this->isHomePage() === true || $this->isErrorPage() === true;
	}

	/**
	 * Checks if the page has a sorting number
	 *
	 * @return bool
	 */
	public function isListed(): bool
	{
		return $this->num() !== null;
	}

	/**
	 * Checks if the page is open.
	 * Open pages are either the current one
	 * or descendants of the current one.
	 *
	 * @return bool
	 */
	public function isOpen(): bool
	{
		if ($this->isActive() === true) {
			return true;
		}

		if ($this->site()->page()?->parents()->has($this->id()) === true) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if the page is not a draft.
	 *
	 * @return bool
	 */
	public function isPublished(): bool
	{
		return $this->isDraft() === false;
	}

	/**
	 * Check if the page can be read by the current user
	 *
	 * @return bool
	 */
	public function isReadable(): bool
	{
		static $readable = [];

		$template = $this->intendedTemplate()->name();

		if (isset($readable[$template]) === true) {
			return $readable[$template];
		}

		return $readable[$template] = $this->permissions()->can('read');
	}

	/**
	 * Checks if the page is sortable
	 *
	 * @return bool
	 */
	public function isSortable(): bool
	{
		return $this->permissions()->can('sort');
	}

	/**
	 * Checks if the page has no sorting number
	 *
	 * @return bool
	 */
	public function isUnlisted(): bool
	{
		return $this->isListed() === false;
	}

	/**
	 * Checks if the page access is verified.
	 * This is only used for drafts so far.
	 *
	 * @internal
	 * @param string|null $token
	 * @return bool
	 */
	public function isVerified(string $token = null)
	{
		if (
			$this->isDraft() === false &&
			$this->parents()->findBy('status', 'draft') === null
		) {
			return true;
		}

		if ($token === null) {
			return false;
		}

		return $this->token() === $token;
	}

	/**
	 * Returns the root to the media folder for the page
	 *
	 * @internal
	 * @return string
	 */
	public function mediaRoot(): string
	{
		return $this->kirby()->root('media') . '/pages/' . $this->id();
	}

	/**
	 * The page's base URL for any files
	 *
	 * @internal
	 * @return string
	 */
	public function mediaUrl(): string
	{
		return $this->kirby()->url('media') . '/pages/' . $this->id();
	}

	/**
	 * Creates a page model if it has been registered
	 *
	 * @internal
	 * @param string $name
	 * @param array $props
	 * @return static
	 */
	public static function model(string $name, array $props = [])
	{
		if ($class = (static::$models[$name] ?? null)) {
			$object = new $class($props);

			if ($object instanceof self) {
				return $object;
			}
		}

		return new static($props);
	}

	/**
	 * Returns the last modification date of the page
	 *
	 * @param string|null $format
	 * @param string|null $handler
	 * @param string|null $languageCode
	 * @return int|string
	 */
	public function modified(string $format = null, string $handler = null, string $languageCode = null)
	{
		return F::modified(
			$this->contentFile($languageCode),
			$format,
			$handler ?? $this->kirby()->option('date.handler', 'date')
		);
	}

	/**
	 * Returns the sorting number
	 *
	 * @return int|null
	 */
	public function num(): int|null
	{
		return $this->num;
	}

	/**
	 * Returns the panel info object
	 *
	 * @return \Kirby\Panel\Page
	 */
	public function panel()
	{
		return new Panel($this);
	}

	/**
	 * Returns the parent Page object
	 *
	 * @return \Kirby\Cms\Page|null
	 */
	public function parent()
	{
		return $this->parent;
	}

	/**
	 * Returns the parent id, if a parent exists
	 *
	 * @internal
	 * @return string|null
	 */
	public function parentId(): string|null
	{
		return $this->parent()?->id();
	}

	/**
	 * Returns the parent model,
	 * which can either be another Page
	 * or the Site
	 *
	 * @internal
	 * @return \Kirby\Cms\Page|\Kirby\Cms\Site
	 */
	public function parentModel()
	{
		return $this->parent() ?? $this->site();
	}

	/**
	 * Returns a list of all parents and their parents recursively
	 *
	 * @return \Kirby\Cms\Pages
	 */
	public function parents()
	{
		$parents = new Pages();
		$page    = $this->parent();

		while ($page !== null) {
			$parents->append($page->id(), $page);
			$page = $page->parent();
		}

		return $parents;
	}

	/**
	 * Return the permanent URL to the page using its UUID
	 * @since 3.8.0
	 */
	public function permalink(): string|null
	{
		return $this->uuid()?->url();
	}

	/**
	 * Returns the permissions object for this page
	 *
	 * @return \Kirby\Cms\PagePermissions
	 */
	public function permissions()
	{
		return new PagePermissions($this);
	}

	/**
	 * Draft preview Url
	 *
	 * @internal
	 * @return string|null
	 */
	public function previewUrl(): string|null
	{
		$preview = $this->blueprint()->preview();

		if ($preview === false) {
			return null;
		}

		if ($preview === true) {
			$url = $this->url();
		} else {
			$url = $preview;
		}

		if ($this->isDraft() === true) {
			$uri = new Uri($url);
			$uri->query->token = $this->token();

			$url = $uri->toString();
		}

		return $url;
	}

	/**
	 * Renders the page with the given data.
	 *
	 * An optional content type can be passed to
	 * render a content representation instead of
	 * the default template.
	 *
	 * @param array $data
	 * @param string $contentType
	 * @return string
	 * @throws \Kirby\Exception\NotFoundException If the default template cannot be found
	 */
	public function render(array $data = [], $contentType = 'html'): string
	{
		$kirby = $this->kirby();
		$cache = $cacheId = $html = null;

		// try to get the page from cache
		if (empty($data) === true && $this->isCacheable() === true) {
			$cache       = $kirby->cache('pages');
			$cacheId     = $this->cacheId($contentType);
			$result      = $cache->get($cacheId);
			$html        = $result['html'] ?? null;
			$response    = $result['response'] ?? [];
			$usesAuth    = $result['usesAuth'] ?? false;
			$usesCookies = $result['usesCookies'] ?? [];

			// if the request contains dynamic data that the cached response
			// relied on, don't use the cache to allow dynamic code to run
			if (Responder::isPrivate($usesAuth, $usesCookies) === true) {
				$html = null;
			}

			// reconstruct the response configuration
			if (empty($html) === false && empty($response) === false) {
				$kirby->response()->fromArray($response);
			}
		}

		// fetch the page regularly
		if ($html === null) {
			if ($contentType === 'html') {
				$template = $this->template();
			} else {
				$template = $this->representation($contentType);
			}

			if ($template->exists() === false) {
				throw new NotFoundException([
					'key' => 'template.default.notFound'
				]);
			}

			$kirby->data = $this->controller($data, $contentType);

			// render the page
			$html = $template->render($kirby->data);

			// cache the result
			$response = $kirby->response();
			if ($cache !== null && $response->cache() === true) {
				$cache->set($cacheId, [
					'html'        => $html,
					'response'    => $response->toArray(),
					'usesAuth'    => $response->usesAuth(),
					'usesCookies' => $response->usesCookies(),
				], $response->expires() ?? 0);
			}
		}

		return $html;
	}

	/**
	 * @internal
	 * @param mixed $type
	 * @return \Kirby\Cms\Template
	 * @throws \Kirby\Exception\NotFoundException If the content representation cannot be found
	 */
	public function representation($type)
	{
		$kirby          = $this->kirby();
		$template       = $this->template();
		$representation = $kirby->template($template->name(), $type);

		if ($representation->exists() === true) {
			return $representation;
		}

		throw new NotFoundException('The content representation cannot be found');
	}

	/**
	 * Returns the absolute root to the page directory
	 * No matter if it exists or not.
	 *
	 * @return string
	 */
	public function root(): string
	{
		return $this->root ??= $this->kirby()->root('content') . '/' . $this->diruri();
	}

	/**
	 * Returns the PageRules class instance
	 * which is being used in various methods
	 * to check for valid actions and input.
	 *
	 * @return \Kirby\Cms\PageRules
	 */
	protected function rules()
	{
		return new PageRules();
	}

	/**
	 * Search all pages within the current page
	 *
	 * @param string|null $query
	 * @param array $params
	 * @return \Kirby\Cms\Pages
	 */
	public function search(string $query = null, $params = [])
	{
		return $this->index()->search($query, $params);
	}

	/**
	 * Sets the Blueprint object
	 *
	 * @param array|null $blueprint
	 * @return $this
	 */
	protected function setBlueprint(array $blueprint = null)
	{
		if ($blueprint !== null) {
			$blueprint['model'] = $this;
			$this->blueprint = new PageBlueprint($blueprint);
		}

		return $this;
	}

	/**
	 * Sets the dirname manually, which works
	 * more reliable in connection with the inventory
	 * than computing the dirname afterwards
	 *
	 * @param string|null $dirname
	 * @return $this
	 */
	protected function setDirname(string $dirname = null)
	{
		$this->dirname = $dirname;
		return $this;
	}

	/**
	 * Sets the draft flag
	 *
	 * @param bool $isDraft
	 * @return $this
	 */
	protected function setIsDraft(bool $isDraft = null)
	{
		$this->isDraft = $isDraft ?? false;
		return $this;
	}

	/**
	 * Sets the sorting number
	 *
	 * @param int|null $num
	 * @return $this
	 */
	protected function setNum(int $num = null)
	{
		$this->num = $num === null ? $num : (int)$num;
		return $this;
	}

	/**
	 * Sets the parent page object
	 *
	 * @param \Kirby\Cms\Page|null $parent
	 * @return $this
	 */
	protected function setParent(Page $parent = null)
	{
		$this->parent = $parent;
		return $this;
	}

	/**
	 * Sets the absolute path to the page
	 *
	 * @param string|null $root
	 * @return $this
	 */
	protected function setRoot(string $root = null)
	{
		$this->root = $root;
		return $this;
	}

	/**
	 * Sets the required Page slug
	 *
	 * @param string $slug
	 * @return $this
	 */
	protected function setSlug(string $slug)
	{
		$this->slug = $slug;
		return $this;
	}

	/**
	 * Sets the intended template
	 *
	 * @param string|null $template
	 * @return $this
	 */
	protected function setTemplate(string $template = null)
	{
		if ($template !== null) {
			$this->intendedTemplate = $this->kirby()->template($template);
		}

		return $this;
	}

	/**
	 * Sets the Url
	 *
	 * @param string|null $url
	 * @return $this
	 */
	protected function setUrl(string $url = null)
	{
		if (is_string($url) === true) {
			$url = rtrim($url, '/');
		}

		$this->url = $url;
		return $this;
	}

	/**
	 * Returns the slug of the page
	 *
	 * @param string|null $languageCode
	 * @return string
	 */
	public function slug(string $languageCode = null): string
	{
		if ($this->kirby()->multilang() === true) {
			if ($languageCode === null) {
				$languageCode = $this->kirby()->languageCode();
			}

			$defaultLanguageCode = $this->kirby()->defaultLanguage()->code();

			if ($languageCode !== $defaultLanguageCode && $translation = $this->translations()->find($languageCode)) {
				return $translation->slug() ?? $this->slug;
			}
		}

		return $this->slug;
	}

	/**
	 * Returns the page status, which
	 * can be `draft`, `listed` or `unlisted`
	 *
	 * @return string
	 */
	public function status(): string
	{
		if ($this->isDraft() === true) {
			return 'draft';
		}

		if ($this->isUnlisted() === true) {
			return 'unlisted';
		}

		return 'listed';
	}

	/**
	 * Returns the final template
	 *
	 * @return \Kirby\Cms\Template
	 */
	public function template()
	{
		if ($this->template !== null) {
			return $this->template;
		}

		$intended = $this->intendedTemplate();

		if ($intended->exists() === true) {
			return $this->template = $intended;
		}

		return $this->template = $this->kirby()->template('default');
	}

	/**
	 * Returns the title field or the slug as fallback
	 *
	 * @return \Kirby\Cms\Field
	 */
	public function title()
	{
		return $this->content()->get('title')->or($this->slug());
	}

	/**
	 * Converts the most important
	 * properties to array
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		return [
			'children'     => $this->children()->keys(),
			'content'      => $this->content()->toArray(),
			'files'        => $this->files()->keys(),
			'id'           => $this->id(),
			'mediaUrl'     => $this->mediaUrl(),
			'mediaRoot'    => $this->mediaRoot(),
			'num'          => $this->num(),
			'parent'       => $this->parent() ? $this->parent()->id() : null,
			'slug'         => $this->slug(),
			'template'     => $this->template(),
			'translations' => $this->translations()->toArray(),
			'uid'          => $this->uid(),
			'uri'          => $this->uri(),
			'url'          => $this->url()
		];
	}

	/**
	 * Returns a verification token, which
	 * is used for the draft authentication
	 *
	 * @return string
	 */
	protected function token(): string
	{
		return $this->kirby()->contentToken($this, $this->id() . $this->template());
	}

	/**
	 * Returns the UID of the page.
	 * The UID is basically the same as the
	 * slug, but stays the same on
	 * multi-language sites. Whereas the slug
	 * can be translated.
	 *
	 * @see self::slug()
	 * @return string
	 */
	public function uid(): string
	{
		return $this->slug;
	}

	/**
	 * The uri is the same as the id, except
	 * that it will be translated in multi-language setups
	 *
	 * @param string|null $languageCode
	 * @return string
	 */
	public function uri(string $languageCode = null): string
	{
		// set the id, depending on the parent
		if ($parent = $this->parent()) {
			return $parent->uri($languageCode) . '/' . $this->slug($languageCode);
		}

		return $this->slug($languageCode);
	}

	/**
	 * Returns the Url
	 *
	 * @param array|string|null $options
	 * @return string
	 */
	public function url($options = null): string
	{
		if ($this->kirby()->multilang() === true) {
			if (is_string($options) === true) {
				return $this->urlForLanguage($options);
			}

			return $this->urlForLanguage(null, $options);
		}

		if ($options !== null) {
			return Url::to($this->url(), $options);
		}

		if (is_string($this->url) === true) {
			return $this->url;
		}

		if ($this->isHomePage() === true) {
			return $this->url = $this->site()->url();
		}

		if ($parent = $this->parent()) {
			if ($parent->isHomePage() === true) {
				return $this->url = $this->kirby()->url('base') . '/' . $parent->uid() . '/' . $this->uid();
			}

			return $this->url = $this->parent()->url() . '/' . $this->uid();
		}

		return $this->url = $this->kirby()->url('base') . '/' . $this->uid();
	}

	/**
	 * Builds the Url for a specific language
	 *
	 * @internal
	 * @param string|null $language
	 * @param array|null $options
	 * @return string
	 */
	public function urlForLanguage($language = null, array $options = null): string
	{
		if ($options !== null) {
			return Url::to($this->urlForLanguage($language), $options);
		}

		if ($this->isHomePage() === true) {
			return $this->url = $this->site()->urlForLanguage($language);
		}

		if ($parent = $this->parent()) {
			if ($parent->isHomePage() === true) {
				return $this->url = $this->site()->urlForLanguage($language) . '/' . $parent->slug($language) . '/' . $this->slug($language);
			}

			return $this->url = $this->parent()->urlForLanguage($language) . '/' . $this->slug($language);
		}

		return $this->url = $this->site()->urlForLanguage($language) . '/' . $this->slug($language);
	}
}
