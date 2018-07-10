<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Toolkit\Str;

/**
 * The Site class is the root element
 * for any site with pages. It represents
 * the main content folder with its site.txt
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 */
class Site extends Model
{
    use SiteActions;

    use HasChildren;
    use HasContent;
    use HasErrors;
    use HasFiles;
    use HasStore;

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
     * The files collection
     *
     * @var Files
     */
    protected $files;

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
     * The current page object
     *
     * @var Page
     */
    protected $page;

    /**
     * The page url
     *
     * @var string
     */
    protected $url;

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
     * Improved var_dump output
     *
     * @return array
     */
    public function __debuginfo(): array
    {
        return array_merge($this->toArray(), [
            'content'  => $this->content(),
            'children' => $this->children(),
            'files'    => $this->files(),
        ]);
    }

    /**
     * Returns the blueprint object
     *
     * @return SiteBlueprint
     */
    public function blueprint(): SiteBlueprint
    {
        if (is_a($this->blueprint, SiteBlueprint::class) === true) {
            return $this->blueprint;
        }

        return $this->blueprint = SiteBlueprint::factory('site', null, $this);
    }

    /**
     * Returns the Children collection
     *
     * @return Pages
     */
    public function children()
    {
        if (is_a($this->children, Pages::class) === true) {
            return $this->children;
        }

        return $this->children = Pages::factory($this->children ?? $this->store()->children(), $this, [
            'kirby' => $this->kirby(),
            'site'  => $this,
        ]);
    }

    protected function defaultStore()
    {
        return SiteStoreDefault::class;
    }

    /**
     * Returns a draft object by the path
     * if one can be found
     *
     * @param string $path
     * @return PageDraft|null
     */
    public function draft(string $path)
    {
        return PageDraft::seek($this, $path);
    }

    /**
     * Return all drafts for the site
     *
     * @return Pages
     */
    public function drafts(): Pages
    {
        return Pages::factory($this->store()->drafts(), $this, [
            'kirby' => $this->kirby(),
            'site'  => $this,
        ], PageDraft::class);
    }

    /**
     * Returns the error page object
     *
     * @return Page
     */
    public function errorPage()
    {
        if (is_a($this->errorPage, Page::class) === true) {
            return $this->errorPage;
        }

        return $this->errorPage = $this->find($this->errorPageId());
    }

    /**
     * Returns the global error page id
     *
     * @return string
     */
    public function errorPageId(): string
    {
        return $this->errorPageId ?? 'error';
    }

    /**
     * Returns the Files collection
     *
     * @return Files
     */
    public function files(): Files
    {
        if (is_a($this->files, Files::class) === true) {
            return $this->files;
        }

        return $this->files = Files::factory($this->files ?? $this->store()->files(), $this, [
            'kirby'  => $this->kirby(),
            'parent' => $this,
            'site'   => $this,
        ]);
    }

    /**
     * Returns the home page object
     *
     * @return Page
     */
    public function homePage()
    {
        if (is_a($this->homePage, Page::class) === true) {
            return $this->homePage;
        }

        return $this->homePage ?? $this->find($this->homePageId());
    }

    /**
     * Returns the global home page id
     *
     * @return string
     */
    public function homePageId(): string
    {
        return $this->homePageId ?? 'home';
    }

    /**
     * Returns the root to the media folder for the site
     *
     * @return string
     */
    public function mediaRoot(): string
    {
        return $this->kirby()->root('media') . '/site';
    }

    /**
     * The site's base url for any files
     *
     * @return string
     */
    public function mediaUrl(): string
    {
        return $this->kirby()->url('media') . '/site';
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
     * @param  string $path
     * @return Page|null
     */
    public function page(string $path = null)
    {
        if ($path !== null) {
            return $this->find($path);
        }

        if (is_a($this->page, Page::class) === true) {
            return $this->page;
        }

        return $this->page = $this->homePage();
    }

    /**
     * Alias for `Site::children()`
     *
     * @return Pages
     */
    public function pages(): Pages
    {
        return $this->children();
    }

    /**
     * Returns the url to the editing view
     * in the panel
     *
     * @return string
     */
    public function panelUrl(): string
    {
        return $this->kirby()->url('panel') . '/pages';
    }

    /**
     * Returns the permissions object for this site
     *
     * @return SiteBlueprintOptions
     */
    public function permissions(): SiteBlueprintOptions
    {
        return $this->blueprint()->options();
    }

    /**
     * Returns the absolute path to the content directory
     *
     * @return string
     */
    public function root(): string
    {
        return $this->kirby()->root('content');
    }

    /**
     * Returns the SiteRules class instance
     * which is being used in various methods
     * to check for valid actions and input.
     *
     * @return SiteRules
     */
    protected function rules()
    {
        return new SiteRules();
    }

    /**
     * Sets the Blueprint object
     *
     * @param SiteBlueprint|null $blueprint
     * @return self
     */
    protected function setBlueprint(SiteBlueprint $blueprint = null): self
    {
        $this->blueprint = $blueprint;
        return $this;
    }

    /**
     * Sets the error page object
     *
     * @param Page|null $errorPage
     * @return self
     */
    public function setErrorPage(Page $errorPage = null): self
    {
        if (is_a($this->errorPage, Page::class) === true) {
            throw new LogicException('The error page has already been set');
        }

        $this->errorPage = $errorPage;
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
    protected function setErrorPageId(string $id = 'error'): self
    {
        $this->errorPageId = $id;
        return $this;
    }

    /**
     * Sets the home page object
     *
     * @param Page|null $homePage
     * @return self
     */
    public function setHomePage(Page $homePage = null): self
    {
        if (is_a($this->homePage, Page::class) === true) {
            throw new LogicException('The home page has already been set');
        }

        $this->homePage = $homePage;
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
    protected function setHomePageId(string $id = 'home'): self
    {
        $this->homePageId = $id;
        return $this;
    }

    /**
     * Sets the current page object
     *
     * @param Page|null $page
     * @return self
     */
    public function setPage(Page $page = null): self
    {
        $this->page = $page;
        return $this;
    }

    /**
     * Sets the Url
     *
     * @param string $url
     * @return void
     */
    protected function setUrl($url = null): self
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
            'errorPage' => $this->errorPage() ? $this->errorPage()->id(): false,
            'homePage'  => $this->homePage() ? $this->homePage()->id(): false,
            'page'      => $this->page() ? $this->page()->id(): false,
            'title'     => $this->title()->value(),
            'url'       => $this->url(),
        ];
    }

    /**
     * Returns the Url
     *
     * @return string
     */
    public function url()
    {
        return $this->url ?? $this->kirby()->url();
    }

    /**
     * Sets the current page by
     * id or page object and
     * returns the current page
     *
     * @param  string|Page $page
     * @return Page
     */
    public function visit($page): Page
    {
        // convert ids to a Page object
        if (is_string($page)) {
            $page = $this->find($page);
        }

        // handle invalid pages
        if (is_a($page, Page::class) === false) {
            throw new InvalidArgumentException('Invalid page object');
        }

        // set the current active page
        $this->setPage($page);

        // return the page
        return $page;
    }
}
