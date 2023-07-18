<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Filesystem\Dir;
use Kirby\Panel\Site as Panel;
use Kirby\Toolkit\A;

/**
 * The `$site` object is the root element
 * for any site with pages. It represents
 * the main content folder with its
 * `site.txt`.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Site extends ModelWithContent
{
	use HasChildren;
	use HasFiles;
	use HasMethods;
	use SiteActions;

	public const CLASS_ALIAS = 'site';

	/**
	 * The SiteBlueprint object
	 */
	protected SiteBlueprint|null $blueprint = null;

	/**
	 * The error page object
	 */
	protected Page|null $errorPage = null;

	/**
	 * The id of the error page, which is
	 * fetched in the errorPage method
	 */
	protected string $errorPageId;

	/**
	 * The home page object
	 */
	protected Page|null $homePage = null;

	/**
	 * The id of the home page, which is
	 * fetched in the errorPage method
	 */
	protected string $homePageId;

	/**
	 * Cache for the inventory array
	 */
	protected array|null $inventory = null;

	/**
	 * The current page object
	 */
	protected Page|null $page;

	/**
	 * The absolute path to the site directory
	 */
	protected string $root;

	/**
	 * The page url
	 */
	protected string|null $url;

	/**
	 * Creates a new Site object
	 */
	public function __construct(array $props = [])
	{
		parent::__construct($props);

		$this->errorPageId = $props['errorPageId'] ?? 'error';
		$this->homePageId  = $props['homePageId'] ?? 'home';
		$this->page        = $props['page'] ?? null;
		$this->url         = $props['url'] ?? null;

		$this->setBlueprint($props['blueprint'] ?? null);
		$this->setChildren($props['children'] ?? null);
		$this->setDrafts($props['drafts'] ?? null);
		$this->setFiles($props['files'] ?? null);
	}

	/**
	 * Modified getter to also return fields
	 * from the content
	 */
	public function __call(string $method, array $arguments = []): mixed
	{
		// public property access
		if (isset($this->$method) === true) {
			return $this->$method;
		}

		// site methods
		if ($this->hasMethod($method)) {
			return $this->callMethod($method, $arguments);
		}

		// return site content otherwise
		return $this->content()->get($method);
	}

	/**
	 * Improved `var_dump` output
	 * @codeCoverageIgnore
	 */
	public function __debugInfo(): array
	{
		return array_merge($this->toArray(), [
			'content'  => $this->content(),
			'children' => $this->children(),
			'files'    => $this->files(),
		]);
	}

	/**
	 * Makes it possible to convert the site model
	 * to a string. Mostly useful for debugging.
	 */
	public function __toString(): string
	{
		return $this->url();
	}

	/**
	 * Returns the url to the api endpoint
	 * @internal
	 */
	public function apiUrl(bool $relative = false): string
	{
		if ($relative === true) {
			return 'site';
		}

		return $this->kirby()->url('api') . '/site';
	}

	/**
	 * Returns the blueprint object
	 */
	public function blueprint(): SiteBlueprint
	{
		if ($this->blueprint instanceof SiteBlueprint) {
			return $this->blueprint;
		}

		return $this->blueprint = SiteBlueprint::factory('site', null, $this);
	}

	/**
	 * Builds a breadcrumb collection
	 */
	public function breadcrumb(): Pages
	{
		// get all parents and flip the order
		$crumb = $this->page()->parents()->flip();

		// add the home page
		$crumb->prepend($this->homePage()->id(), $this->homePage());

		// add the active page
		$crumb->append($this->page()->id(), $this->page());

		return $crumb;
	}

	/**
	 * Prepares the content for the write method
	 * @internal
	 */
	public function contentFileData(
		array $data,
		string|null $languageCode = null
	): array {
		return A::prepend($data, ['title' => $data['title'] ?? null]);
	}

	/**
	 * Filename for the content file
	 * @internal
	 * @deprecated 4.0.0
	 * @todo Remove in v5
	 * @codeCoverageIgnore
	 */
	public function contentFileName(): string
	{
		Helpers::deprecated('The internal $model->contentFileName() method has been deprecated. Please let us know via a GitHub issue if you need this method and tell us your use case.', 'model-content-file');
		return 'site';
	}

	/**
	 * Returns the error page object
	 */
	public function errorPage(): Page|null
	{
		return $this->errorPage ??= $this->find($this->errorPageId());
	}

	/**
	 * Returns the global error page id
	 * @internal
	 */
	public function errorPageId(): string
	{
		return $this->errorPageId ?? 'error';
	}

	/**
	 * Checks if the site exists on disk
	 */
	public function exists(): bool
	{
		return is_dir($this->root()) === true;
	}

	/**
	 * Returns the home page object
	 */
	public function homePage(): Page|null
	{
		return $this->homePage ??= $this->find($this->homePageId());
	}

	/**
	 * Returns the global home page id
	 * @internal
	 */
	public function homePageId(): string
	{
		return $this->homePageId ?? 'home';
	}

	/**
	 * Creates an inventory of all files
	 * and children in the site directory
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
	 * Compares the current object with the given site object
	 *
	 * @param mixed $site
	 */
	public function is($site): bool
	{
		if ($site instanceof self === false) {
			return false;
		}

		return $this === $site;
	}

	/**
	 * Returns the root to the media folder for the site
	 * @internal
	 */
	public function mediaRoot(): string
	{
		return $this->kirby()->root('media') . '/site';
	}

	/**
	 * The site's base url for any files
	 * @internal
	 */
	public function mediaUrl(): string
	{
		return $this->kirby()->url('media') . '/site';
	}

	/**
	 * Gets the last modification date of all pages
	 * in the content folder.
	 *
	 * @return int|string
	 */
	public function modified(
		string|null $format = null,
		string|null $handler = null
	) {
		return Dir::modified(
			$this->root(),
			$format,
			$handler ?? $this->kirby()->option('date.handler', 'date')
		);
	}

	/**
	 * Returns the current page if `$path`
	 * is not specified. Otherwise it will try
	 * to find a page by the given path.
	 *
	 * If no current page is set with the page
	 * prop, the home page will be returned if
	 * it can be found. (see `Site::homePage()`)
	 *
	 * @param string|null $path omit for current page,
	 *                          otherwise e.g. `notes/across-the-ocean`
	 */
	public function page(string|null $path = null): Page|null
	{
		if ($path !== null) {
			return $this->find($path);
		}

		if ($this->page instanceof Page) {
			return $this->page;
		}

		try {
			return $this->page = $this->homePage();
		} catch (LogicException) {
			return $this->page = null;
		}
	}

	/**
	 * Alias for `Site::children()`
	 */
	public function pages(): Pages
	{
		return $this->children();
	}

	/**
	 * Returns the panel info object
	 */
	public function panel(): Panel
	{
		return new Panel($this);
	}

	/**
	 * Returns the permissions object for this site
	 */
	public function permissions(): SitePermissions
	{
		return new SitePermissions($this);
	}

	/**
	 * Preview Url
	 * @internal
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

		return $url;
	}

	/**
	 * Returns the absolute path to the content directory
	 */
	public function root(): string
	{
		return $this->root ??= $this->kirby()->root('content');
	}

	/**
	 * Returns the SiteRules class instance
	 * which is being used in various methods
	 * to check for valid actions and input.
	 */
	protected function rules(): SiteRules
	{
		return new SiteRules();
	}

	/**
	 * Search all pages in the site
	 *
	 * @param array $params
	 * @return \Kirby\Cms\Pages
	 */
	public function search(string|null $query = null, $params = [])
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
			$this->blueprint = new SiteBlueprint($blueprint);
		}

		return $this;
	}

	/**
	 * Converts the most important site
	 * properties to an array
	 */
	public function toArray(): array
	{
		return array_merge(parent::toArray(), [
			'children'   => $this->children()->keys(),
			'errorPage'  => $this->errorPage()?->id() ?? false,
			'files'      => $this->files()->keys(),
			'homePage'   => $this->homePage()?->id() ?? false,
			'page'       => $this->page()?->id() ?? false,
			'title'      => $this->title()->value(),
			'url'        => $this->url(),
		]);
	}

	/**
	 * Returns the Url
	 */
	public function url(string|null $language = null): string
	{
		if ($language !== null || $this->kirby()->multilang() === true) {
			return $this->urlForLanguage($language);
		}

		return $this->url ?? $this->kirby()->url();
	}

	/**
	 * Returns the translated url
	 * @internal
	 */
	public function urlForLanguage(
		string|null $languageCode = null,
		array|null $options = null
	): string {
		if ($language = $this->kirby()->language($languageCode)) {
			return $language->url();
		}

		return $this->kirby()->url();
	}

	/**
	 * Sets the current page by
	 * id or page object and
	 * returns the current page
	 *
	 * @internal
	 * @param string|\Kirby\Cms\Page $page
	 * @return \Kirby\Cms\Page
	 */
	public function visit($page, string|null $languageCode = null)
	{
		if ($languageCode !== null) {
			$this->kirby()->setCurrentTranslation($languageCode);
			$this->kirby()->setCurrentLanguage($languageCode);
		}

		// convert ids to a Page object
		if (is_string($page) === true) {
			$page = $this->find($page);
		}

		// handle invalid pages
		if ($page instanceof Page === false) {
			throw new InvalidArgumentException('Invalid page object');
		}

		// set and return the current active page
		return $this->page = $page;
	}

	/**
	 * Checks if any content of the site has been
	 * modified after the given unix timestamp
	 * This is mainly used to auto-update the cache
	 *
	 * @param mixed $time
	 */
	public function wasModifiedAfter($time): bool
	{
		return Dir::wasModifiedAfter($this->root(), $time);
	}
}
