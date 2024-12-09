<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Content\Field;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\Dir;
use Kirby\Http\Response;
use Kirby\Http\Uri;
use Kirby\Panel\Page as Panel;
use Kirby\Template\Template;
use Kirby\Toolkit\A;
use Kirby\Toolkit\LazyValue;
use Kirby\Toolkit\Str;
use Throwable;

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
	use HasChildren;
	use HasFiles;
	use HasMethods;
	use HasSiblings;
	use PageActions;
	use PageSiblings;

	public const CLASS_ALIAS = 'page';

	/**
	 * All registered page methods
	 * @todo Remove when support for PHP 8.2 is dropped
	 */
	public static array $methods = [];

	/**
	 * Registry with all Page models
	 */
	public static array $models = [];

	/**
	 * The PageBlueprint object
	 */
	protected PageBlueprint|null $blueprint = null;

	/**
	 * Nesting level
	 */
	protected int $depth;

	/**
	 * Sorting number + slug
	 */
	protected string|null $dirname;

	/**
	 * Path of dirnames
	 */
	protected string|null $diruri = null;

	/**
	 * Draft status flag
	 */
	protected bool $isDraft;

	/**
	 * The Page id
	 */
	protected string|null $id = null;

	/**
	 * The template, that should be loaded
	 * if it exists
	 */
	protected Template|null $intendedTemplate = null;

	protected array|null $inventory = null;

	/**
	 * The sorting number
	 */
	protected int|null $num;

	/**
	 * The parent page
	 */
	protected Page|null $parent;

	/**
	 * Absolute path to the page directory
	 */
	protected string|null $root;

	/**
	 * The URL-appendix aka slug
	 */
	protected string $slug;

	/**
	 * The intended page template
	 */
	protected Template|null $template = null;

	/**
	 * The page url
	 */
	protected string|null $url;

	/**
	 * Creates a new page object
	 */
	public function __construct(array $props)
	{
		if (isset($props['slug']) === false) {
			throw new InvalidArgumentException('The page slug is required');
		}

		parent::__construct($props);

		$this->slug    = $props['slug'];
		// Sets the dirname manually, which works
		// more reliable in connection with the inventory
		// than computing the dirname afterwards
		$this->dirname = $props['dirname'] ?? null;
		$this->isDraft = $props['isDraft'] ?? false;
		$this->num     = $props['num'] ?? null;
		$this->parent  = $props['parent'] ?? null;
		$this->root    = $props['root'] ?? null;

		$this->setBlueprint($props['blueprint'] ?? null);
		$this->setChildren($props['children'] ?? null);
		$this->setDrafts($props['drafts'] ?? null);
		$this->setFiles($props['files'] ?? null);
		$this->setTemplate($props['template'] ?? null);
		$this->setUrl($props['url'] ?? null);
	}

	/**
	 * Magic caller
	 */
	public function __call(string $method, array $arguments = []): mixed
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
	 * Improved `var_dump` output
	 * @codeCoverageIgnore
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
	 * @internal
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
	 */
	public function blueprint(): PageBlueprint
	{
		return $this->blueprint ??= PageBlueprint::factory(
			'pages/' . $this->intendedTemplate(),
			'pages/default',
			$this
		);
	}

	/**
	 * Returns an array with all blueprints that are available for the page
	 */
	public function blueprints(string|null $inSection = null): array
	{
		if ($inSection !== null) {
			return $this->blueprint()->section($inSection)->blueprints();
		}

		if ($this->blueprints !== null) {
			return $this->blueprints;
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

		return $this->blueprints = array_values($blueprints);
	}

	/**
	 * Builds the cache id for the page
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
	 * @internal
	 */
	public function contentFileData(
		array $data,
		string|null $languageCode = null
	): array {
		return A::prepend($data, [
			'title' => $data['title'] ?? null,
			'slug'  => $data['slug']  ?? null
		]);
	}

	/**
	 * Returns the content text file
	 * which is found by the inventory method
	 * @internal
	 * @deprecated 4.0.0
	 * @todo Remove in v5
	 * @codeCoverageIgnore
	 */
	public function contentFileName(string|null $languageCode = null): string
	{
		Helpers::deprecated('The internal $model->contentFileName() method has been deprecated. Please let us know via a GitHub issue if you need this method and tell us your use case.', 'model-content-file');
		return $this->intendedTemplate()->name();
	}

	/**
	 * Call the page controller
	 * @internal
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the controller returns invalid objects for `kirby`, `site`, `pages` or `page`
	 */
	public function controller(
		array $data = [],
		string $contentType = 'html'
	): array {
		// create the template data
		$data = array_merge($data, [
			'kirby' => $kirby = $this->kirby(),
			'site'  => $site  = $this->site(),
			'pages' => new LazyValue(fn () => $site->children()),
			'page'  => new LazyValue(fn () => $site->visit($this))
		]);

		// call the template controller if there's one.
		$controllerData = $kirby->controller(
			$this->template()->name(),
			$data,
			$contentType
		);

		// merge controller data with original data safely
		// to provide original data to template even if
		// it wasn't returned by the controller explicitly
		if (empty($controllerData) === false) {
			$classes = [
				'kirby' => App::class,
				'site'  => Site::class,
				'pages' => Pages::class,
				'page'  => Page::class
			];

			foreach ($controllerData as $key => $value) {
				$data[$key] = match (true) {
					// original data wasn't overwritten
					array_key_exists($key, $classes) === false => $value,
					// original data was overwritten, but matches expected type
					$value instanceof $classes[$key] => $value,
					// throw error if data was overwritten with wrong type
					default => throw new InvalidArgumentException('The returned variable "' . $key . '" from the controller "' . $this->template()->name() . '" is not of the required type "' . $classes[$key] . '"')
				};
			}
		}

		// unwrap remaining lazy values in data
		// (happens if the controller didn't override an original lazy Kirby object)
		$data = LazyValue::unwrap($data);

		return $data;
	}

	/**
	 * Returns a number indicating how deep the page
	 * is nested within the content folder
	 */
	public function depth(): int
	{
		return $this->depth ??= (substr_count($this->id(), '/') + 1);
	}

	/**
	 * Sorting number + Slug
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
	 */
	public function exists(): bool
	{
		return is_dir($this->root()) === true;
	}

	/**
	 * Constructs a Page object and also
	 * takes page models into account.
	 * @internal
	 */
	public static function factory($props): static
	{
		return static::model($props['model'] ?? 'default', $props);
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
	public function go(array $options = [], int $code = 302): void
	{
		Response::go($this->url($options), $code);
	}

	/**
	 * Checks if the intended template
	 * for the page exists.
	 */
	public function hasTemplate(): bool
	{
		return $this->intendedTemplate() === $this->template();
	}

	/**
	 * Returns the Page Id
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
	 */
	public function intendedTemplate(): Template
	{
		if ($this->intendedTemplate !== null) {
			return $this->intendedTemplate;
		}

		return $this->setTemplate($this->inventory()['template'])->intendedTemplate();
	}

	/**
	 * Returns the inventory of files
	 * children and content files
	 * @internal
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
	 * Checks if the page is accessible that accessible and listable.
	 * This permission depends on the `read` option until v5
	 */
	public function isAccessible(): bool
	{
		// TODO: remove this check when `read` option deprecated in v5
		if ($this->isReadable() === false) {
			return false;
		}

		static $accessible   = [];
		$role                = $this->kirby()->user()?->role()->id() ?? '__none__';
		$template            = $this->intendedTemplate()->name();
		$accessible[$role] ??= [];

		return $accessible[$role][$template] ??= $this->permissions()->can('access');
	}

	/**
	 * Checks if the page is the current page
	 */
	public function isActive(): bool
	{
		return $this->site()->page()?->is($this) === true;
	}

	/**
	 * Checks if the page is a direct or indirect ancestor
	 * of the given $page object
	 */
	public function isAncestorOf(Page $child): bool
	{
		return $child->parents()->has($this->id()) === true;
	}

	/**
	 * Checks if the page can be cached in the
	 * pages cache. This will also check if one
	 * of the ignore rules from the config kick in.
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
	 */
	public function isChildOf($parent): bool
	{
		return $this->parent()?->is($parent) ?? false;
	}

	/**
	 * Checks if the page is a descendant of the given page
	 *
	 * @param \Kirby\Cms\Page|string $parent
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
	 */
	public function isDraft(): bool
	{
		return $this->isDraft;
	}

	/**
	 * Checks if the page is the error page
	 */
	public function isErrorPage(): bool
	{
		return $this->id() === $this->site()->errorPageId();
	}

	/**
	 * Checks if the page is the home page
	 */
	public function isHomePage(): bool
	{
		return $this->id() === $this->site()->homePageId();
	}

	/**
	 * It's often required to check for the
	 * home and error page to stop certain
	 * actions. That's why there's a shortcut.
	 */
	public function isHomeOrErrorPage(): bool
	{
		return $this->isHomePage() === true || $this->isErrorPage() === true;
	}

	/**
	 * Check if the page can be listable by the current user
	 * This permission depends on the `read` option until v5
	 */
	public function isListable(): bool
	{
		// TODO: remove this check when `read` option deprecated in v5
		if ($this->isReadable() === false) {
			return false;
		}

		// not accessible also means not listable
		if ($this->isAccessible() === false) {
			return false;
		}

		static $listable   = [];
		$role              = $this->kirby()->user()?->role()->id() ?? '__none__';
		$template          = $this->intendedTemplate()->name();
		$listable[$role] ??= [];

		return $listable[$role][$template] ??= $this->permissions()->can('list');
	}

	/**
	 * Checks if the page has a sorting number
	 */
	public function isListed(): bool
	{
		return $this->isPublished() && $this->num() !== null;
	}

	public function isMovableTo(Page|Site $parent): bool
	{
		try {
			return PageRules::move($this, $parent);
		} catch (Throwable) {
			return false;
		}
	}

	/**
	 * Checks if the page is open.
	 * Open pages are either the current one
	 * or descendants of the current one.
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
	 */
	public function isPublished(): bool
	{
		return $this->isDraft() === false;
	}

	/**
	 * Check if the page can be read by the current user
	 * @todo Deprecate `read` option in v5 and make the necessary changes for `access` and `list` options.
	 */
	public function isReadable(): bool
	{
		static $readable   = [];
		$role              = $this->kirby()->user()?->role()->id() ?? '__none__';
		$template          = $this->intendedTemplate()->name();
		$readable[$role] ??= [];

		return $readable[$role][$template] ??= $this->permissions()->can('read');
	}

	/**
	 * Checks if the page is sortable
	 */
	public function isSortable(): bool
	{
		return $this->permissions()->can('sort');
	}

	/**
	 * Checks if the page has no sorting number
	 */
	public function isUnlisted(): bool
	{
		return $this->isPublished() && $this->num() === null;
	}

	/**
	 * Checks if the page access is verified.
	 * This is only used for drafts so far.
	 * @internal
	 */
	public function isVerified(string|null $token = null): bool
	{
		if (
			$this->isPublished() === true &&
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
	 * @internal
	 */
	public function mediaRoot(): string
	{
		return $this->kirby()->root('media') . '/pages/' . $this->id();
	}

	/**
	 * The page's base URL for any files
	 * @internal
	 */
	public function mediaUrl(): string
	{
		return $this->kirby()->url('media') . '/pages/' . $this->id();
	}

	/**
	 * Creates a page model if it has been registered
	 * @internal
	 */
	public static function model(string $name, array $props = []): static
	{
		$class   = static::$models[$name] ?? null;
		$class ??= static::$models['default'] ?? null;

		if ($class !== null) {
			$object = new $class($props);

			if ($object instanceof self) {
				return $object;
			}
		}

		return new static($props);
	}

	/**
	 * Returns the last modification date of the page
	 */
	public function modified(
		string|null $format = null,
		string|null $handler = null,
		string|null $languageCode = null
	): int|string|false|null {
		$identifier = $this->isDraft() === true ? 'changes' : 'published';

		$modified = $this->storage()->modified(
			$identifier,
			$languageCode
		);

		if ($modified === null) {
			return null;
		}

		return Str::date($modified, $format, $handler);
	}

	/**
	 * Returns the sorting number
	 */
	public function num(): int|null
	{
		return $this->num;
	}

	/**
	 * Returns the panel info object
	 */
	public function panel(): Panel
	{
		return new Panel($this);
	}

	/**
	 * Returns the parent Page object
	 */
	public function parent(): Page|null
	{
		return $this->parent;
	}

	/**
	 * Returns the parent id, if a parent exists
	 * @internal
	 */
	public function parentId(): string|null
	{
		return $this->parent()?->id();
	}

	/**
	 * Returns the parent model,
	 * which can either be another Page
	 * or the Site
	 * @internal
	 */
	public function parentModel(): Page|Site
	{
		return $this->parent() ?? $this->site();
	}

	/**
	 * Returns a list of all parents and their parents recursively
	 */
	public function parents(): Pages
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
	 */
	public function permissions(): PagePermissions
	{
		return new PagePermissions($this);
	}

	/**
	 * Draft preview Url
	 * @internal
	 */
	public function previewUrl(): string|null
	{
		$preview = $this->blueprint()->preview();

		if ($preview === false) {
			return null;
		}

		$url = match ($preview) {
			true    => $this->url(),
			default => $preview
		};

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
	 * @param string $contentType
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

			// trigger before hook and apply for `data`
			$kirby->data = $kirby->apply('page.render:before', [
				'contentType' => $contentType,
				'data'        => $kirby->data,
				'page'        => $this
			], 'data');

			// render the page
			$html = $template->render($kirby->data);

			// trigger after hook and apply for `html`
			$html = $kirby->apply('page.render:after', [
				'contentType' => $contentType,
				'data'        => $kirby->data,
				'html'        => $html,
				'page'        => $this
			], 'html');

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
	 * @throws \Kirby\Exception\NotFoundException If the content representation cannot be found
	 */
	public function representation(mixed $type): Template
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
	 */
	public function root(): string
	{
		return $this->root ??= $this->kirby()->root('content') . '/' . $this->diruri();
	}

	/**
	 * Returns the PageRules class instance
	 * which is being used in various methods
	 * to check for valid actions and input.
	 */
	protected function rules(): PageRules
	{
		return new PageRules();
	}

	/**
	 * Search all pages within the current page
	 */
	public function search(string|null $query = null, string|array $params = []): Pages
	{
		return $this->index()->search($query, $params);
	}

	/**
	 * Sets the Blueprint object
	 *
	 * @return $this
	 */
	protected function setBlueprint(array|null $blueprint = null): static
	{
		if ($blueprint !== null) {
			$blueprint['model'] = $this;
			$this->blueprint = new PageBlueprint($blueprint);
		}

		return $this;
	}

	/**
	 * Sets the intended template
	 *
	 * @return $this
	 */
	protected function setTemplate(string|null $template = null): static
	{
		if ($template !== null) {
			$this->intendedTemplate = $this->kirby()->template($template);
		}

		return $this;
	}

	/**
	 * Sets the Url
	 *
	 * @return $this
	 */
	protected function setUrl(string|null $url = null): static
	{
		if (is_string($url) === true) {
			$url = rtrim($url, '/');
		}

		$this->url = $url;
		return $this;
	}

	/**
	 * Returns the slug of the page
	 */
	public function slug(string|null $languageCode = null): string
	{
		if ($this->kirby()->multilang() === true) {
			$languageCode      ??= $this->kirby()->languageCode();
			$defaultLanguageCode = $this->kirby()->defaultLanguage()->code();

			if (
				$languageCode !== $defaultLanguageCode &&
				$translation = $this->translations()->find($languageCode)
			) {
				return $translation->slug() ?? $this->slug;
			}
		}

		return $this->slug;
	}

	/**
	 * Returns the page status, which
	 * can be `draft`, `listed` or `unlisted`
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
	 */
	public function template(): Template
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
	 */
	public function title(): Field
	{
		return $this->content()->get('title')->or($this->slug());
	}

	/**
	 * Converts the most important
	 * properties to array
	 */
	public function toArray(): array
	{
		return array_merge(parent::toArray(), [
			'children'  => $this->children()->keys(),
			'files'     => $this->files()->keys(),
			'id'        => $this->id(),
			'mediaUrl'  => $this->mediaUrl(),
			'mediaRoot' => $this->mediaRoot(),
			'num'       => $this->num(),
			'parent'    => $this->parent()?->id(),
			'slug'      => $this->slug(),
			'template'  => $this->template(),
			'uid'       => $this->uid(),
			'uri'       => $this->uri(),
			'url'       => $this->url()
		]);
	}

	/**
	 * Returns a verification token, which
	 * is used for the draft authentication
	 */
	protected function token(): string
	{
		return $this->kirby()->contentToken(
			$this,
			$this->id() . $this->template()
		);
	}

	/**
	 * Returns the UID of the page.
	 * The UID is basically the same as the
	 * slug, but stays the same on
	 * multi-language sites. Whereas the slug
	 * can be translated.
	 *
	 * @see self::slug()
	 */
	public function uid(): string
	{
		return $this->slug;
	}

	/**
	 * The uri is the same as the id, except
	 * that it will be translated in multi-language setups
	 */
	public function uri(string|null $languageCode = null): string
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
	 */
	public function urlForLanguage(
		$language = null,
		array|null $options = null
	): string {
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
