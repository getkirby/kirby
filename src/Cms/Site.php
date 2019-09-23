<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
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
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Site extends ModelWithContent
{
    const CLASS_ALIAS = 'site';

    use SiteActions;
    use HasChildren;
    use HasFiles;
    use HasMethods;

    /**
     * The SiteBlueprint object
     *
     * @var SiteBlueprint
     */
    protected $blueprint;

    /**
     * The error page object
     *
     * @var Page
     */
    protected $errorPage;

    /**
     * The id of the error page, which is
     * fetched in the errorPage method
     *
     * @var string
     */
    protected $errorPageId = 'error';

    /**
     * The home page object
     *
     * @var Page
     */
    protected $homePage;

    /**
     * The id of the home page, which is
     * fetched in the errorPage method
     *
     * @var string
     */
    protected $homePageId = 'home';

    /**
     * Cache for the inventory array
     *
     * @var array
     */
    protected $inventory;

    /**
     * The current page object
     *
     * @var Page
     */
    protected $page;

    /**
     * The absolute path to the site directory
     *
     * @var string
     */
    protected $root;

    /**
     * The page url
     *
     * @var string
     */
    protected $url;

    /**
     * Modified getter to also return fields
     * from the content
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

        // site methods
        if ($this->hasMethod($method)) {
            return $this->callMethod($method, $arguments);
        }

        // return site content otherwise
        return $this->content()->get($method, $arguments);
    }

    /**
     * Creates a new Site object
     *
     * @param array $props
     */
    public function __construct(array $props = [])
    {
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
            'content'  => $this->content(),
            'children' => $this->children(),
            'files'    => $this->files(),
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
            return 'site';
        } else {
            return $this->kirby()->url('api') . '/site';
        }
    }

    /**
     * Returns the blueprint object
     *
     * @return \Kirby\Cms\SiteBlueprint
     */
    public function blueprint()
    {
        if (is_a($this->blueprint, 'Kirby\Cms\SiteBlueprint') === true) {
            return $this->blueprint;
        }

        return $this->blueprint = SiteBlueprint::factory('site', null, $this);
    }

    /**
     * Returns an array with all blueprints that are available
     * as subpages of the site
     *
     * @param string $inSection
     * @return array
     */
    public function blueprints(string $inSection = null): array
    {
        $blueprints = [];
        $blueprint  = $this->blueprint();
        $sections   = $inSection !== null ? [$blueprint->section($inSection)] : $blueprint->sections();

        foreach ($sections as $section) {
            if ($section === null) {
                continue;
            }

            foreach ((array)$section->blueprints() as $blueprint) {
                $blueprints[$blueprint['name']] = $blueprint;
            }
        }

        return array_values($blueprints);
    }

    /**
     * Builds a breadcrumb collection
     *
     * @return \Kirby\Cms\Pages
     */
    public function breadcrumb()
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
     *
     * @internal
     * @param array $data
     * @param string $languageCode
     * @return array
     */
    public function contentFileData(array $data, string $languageCode = null): array
    {
        return A::prepend($data, [
            'title' => $data['title'] ?? null,
        ]);
    }

    /**
     * Filename for the content file
     *
     * @internal
     * @return string
     */
    public function contentFileName(): string
    {
        return 'site';
    }

    /**
     * Returns the error page object
     *
     * @return \Kirby\Cms\Page|null
     */
    public function errorPage()
    {
        if (is_a($this->errorPage, 'Kirby\Cms\Page') === true) {
            return $this->errorPage;
        }

        if ($error = $this->find($this->errorPageId())) {
            return $this->errorPage = $error;
        }

        return null;
    }

    /**
     * Returns the global error page id
     *
     * @internal
     * @return string
     */
    public function errorPageId(): string
    {
        return $this->errorPageId ?? 'error';
    }

    /**
     * Checks if the site exists on disk
     *
     * @return bool
     */
    public function exists(): bool
    {
        return is_dir($this->root()) === true;
    }

    /**
     * Returns the home page object
     *
     * @return \Kirby\Cms\Page|null
     */
    public function homePage()
    {
        if (is_a($this->homePage, 'Kirby\Cms\Page') === true) {
            return $this->homePage;
        }

        if ($home = $this->find($this->homePageId())) {
            return $this->homePage = $home;
        }

        return null;
    }

    /**
     * Returns the global home page id
     *
     * @internal
     * @return string
     */
    public function homePageId(): string
    {
        return $this->homePageId ?? 'home';
    }

    /**
     * Creates an inventory of all files
     * and children in the site directory
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
     * Compares the current object with the given site object
     *
     * @param mixed $site
     * @return bool
     */
    public function is($site): bool
    {
        if (is_a($site, 'Kirby\Cms\Site') === false) {
            return false;
        }

        return $this === $site;
    }

    /**
     * Returns the root to the media folder for the site
     *
     * @internal
     * @return string
     */
    public function mediaRoot(): string
    {
        return $this->kirby()->root('media') . '/site';
    }

    /**
     * The site's base url for any files
     *
     * @internal
     * @return string
     */
    public function mediaUrl(): string
    {
        return $this->kirby()->url('media') . '/site';
    }

    /**
     * Gets the last modification date of all pages
     * in the content folder.
     *
     * @param string|null $format
     * @param string|null $handler
     * @return mixed
     */
    public function modified(string $format = null, string $handler = null)
    {
        return Dir::modified($this->root(), $format, $handler ?? $this->kirby()->option('date.handler', 'date'));
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
     * @param string $path
     * @return \Kirby\Cms\Page|null
     */
    public function page(string $path = null)
    {
        if ($path !== null) {
            return $this->find($path);
        }

        if (is_a($this->page, 'Kirby\Cms\Page') === true) {
            return $this->page;
        }

        try {
            return $this->page = $this->homePage();
        } catch (LogicException $e) {
            return $this->page = null;
        }
    }

    /**
     * Alias for `Site::children()`
     *
     * @return \Kirby\Cms\Pages
     */
    public function pages()
    {
        return $this->children();
    }

    /**
     * Returns the full path without leading slash
     *
     * @internal
     * @return string
     */
    public function panelPath(): string
    {
        return 'site';
    }

    /**
     * Returns the url to the editing view
     * in the panel
     *
     * @internal
     * @param bool $relative
     * @return string
     */
    public function panelUrl(bool $relative = false): string
    {
        if ($relative === true) {
            return '/' . $this->panelPath();
        } else {
            return $this->kirby()->url('panel') . '/' . $this->panelPath();
        }
    }

    /**
     * Returns the permissions object for this site
     *
     * @return \Kirby\Cms\SitePermissions
     */
    public function permissions()
    {
        return new SitePermissions($this);
    }

    /**
     * Preview Url
     *
     * @internal
     * @return string|null
     */
    public function previewUrl(): ?string
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
     *
     * @return string
     */
    public function root(): string
    {
        return $this->root = $this->root ?? $this->kirby()->root('content');
    }

    /**
     * Returns the SiteRules class instance
     * which is being used in various methods
     * to check for valid actions and input.
     *
     * @return \Kirby\Cms\SiteRules
     */
    protected function rules()
    {
        return new SiteRules();
    }

    /**
     * Search all pages in the site
     *
     * @param string $query
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
     * @return self
     */
    protected function setBlueprint(array $blueprint = null)
    {
        if ($blueprint !== null) {
            $blueprint['model'] = $this;
            $this->blueprint = new SiteBlueprint($blueprint);
        }

        return $this;
    }

    /**
     * Sets the id of the error page, which
     * is used in the errorPage method
     * to get the default error page if nothing
     * else is set.
     *
     * @param string $id
     * @return self
     */
    protected function setErrorPageId(string $id = 'error')
    {
        $this->errorPageId = $id;
        return $this;
    }

    /**
     * Sets the id of the home page, which
     * is used in the homePage method
     * to get the default home page if nothing
     * else is set.
     *
     * @param string $id
     * @return self
     */
    protected function setHomePageId(string $id = 'home')
    {
        $this->homePageId = $id;
        return $this;
    }

    /**
     * Sets the current page object
     *
     * @internal
     * @param \Kirby\Cms\Page|null $page
     * @return self
     */
    public function setPage(Page $page = null)
    {
        $this->page = $page;
        return $this;
    }

    /**
     * Sets the Url
     *
     * @param string $url
     * @return self
     */
    protected function setUrl($url = null)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Converts the most important site
     * properties to an array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'children'  => $this->children()->keys(),
            'content'   => $this->content()->toArray(),
            'errorPage' => $this->errorPage() ? $this->errorPage()->id(): false,
            'files'     => $this->files()->keys(),
            'homePage'  => $this->homePage() ? $this->homePage()->id(): false,
            'page'      => $this->page() ? $this->page()->id(): false,
            'title'     => $this->title()->value(),
            'url'       => $this->url(),
        ];
    }

    /**
     * Returns the Url
     *
     * @param string|null $language
     * @return string
     */
    public function url($language = null): string
    {
        if ($language !== null || $this->kirby()->multilang() === true) {
            return $this->urlForLanguage($language);
        }

        return $this->url ?? $this->kirby()->url();
    }

    /**
     * Returns the translated url
     *
     * @internal
     * @param string $languageCode
     * @param array $options
     * @return string
     */
    public function urlForLanguage(string $languageCode = null, array $options = null): string
    {
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
     * @param string|null $languageCode
     * @return \Kirby\Cms\Page
     */
    public function visit($page, string $languageCode = null)
    {
        if ($languageCode !== null) {
            $this->kirby()->setCurrentTranslation($languageCode);
            $this->kirby()->setCurrentLanguage($languageCode);
        }

        // convert ids to a Page object
        if (is_string($page)) {
            $page = $this->find($page);
        }

        // handle invalid pages
        if (is_a($page, 'Kirby\Cms\Page') === false) {
            throw new InvalidArgumentException('Invalid page object');
        }

        // set the current active page
        $this->setPage($page);

        // return the page
        return $page;
    }

    /**
     * Checks if any content of the site has been
     * modified after the given unix timestamp
     * This is mainly used to auto-update the cache
     *
     * @param mixed $time
     * @return bool
     */
    public function wasModifiedAfter($time): bool
    {
        return Dir::wasModifiedAfter($this->root(), $time);
    }
}
