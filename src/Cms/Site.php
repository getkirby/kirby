<?php

namespace Kirby\Cms;

use Exception;

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

    use HasChildren;
    use HasContent;
    use HasFiles;

    protected static $toArray = [
        'children',
        'content',
        'errorPage',
        'files',
        'homePage',
        'root',
        'url'
    ];

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
     * The root to the content directory
     *
     * @var string|null
     */
    protected $root;

    /**
     * The page url
     *
     * @var string
     */
    protected $url = '/';

    /**
     * Creates a new Site object
     *
     * @param array $props
     */
    public function __construct(array $props = [])
    {
        $this->setOptionalProperties($props, [
            'blueprint',
            'children',
            'content',
            'errorPage',
            'files',
            'homePage',
            'kirby',
            'page',
            'root',
            'url',
        ]);
    }

    /**
     * @return SiteBlueprint
     */
    public function blueprint(): SiteBlueprint
    {
        if (is_a($this->blueprint, SiteBlueprint::class) === true) {
            return $this->blueprint;
        }

        return $this->blueprint = $this->store()->blueprint();
    }

    /**
     * Returns the Content class with
     * all ContentFields for the site
     *
     * The HasContent trait takes care
     * of the rest.
     *
     * @return Content
     */
    public function content()
    {
        if (is_a($this->content, Content::class) === true) {
            return $this->content;
        }

        return $this->content = $this->store()->content();
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

        return $this->children = $this->store()->children();
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
    public function files()
    {
        if (is_a($this->files, Files::class) === true) {
            return $this->files;
        }

        return $this->files = $this->store()->files();
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
     * Returns the directory root
     *
     * @return string
     */
    public function root()
    {
        return $this->root;
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
     * Sets the page directory
     *
     * @param string|null $root
     * @return self
     */
    protected function setRoot(string $root = null): self
    {
        $this->root = $root;
        return $this;
    }

    /**
     * Sets the Url
     *
     * @param string $url
     * @return void
     */
    protected function setUrl(string $url = null): self
    {
        $this->url = rtrim($url, '/');
        return $this;
    }

    /**
     * Returns the SiteStore
     *
     * @return SiteStore
     */
    protected function store(): SiteStore
    {
        return App::instance()->component('SiteStore', $this);
    }

    /**
     * Updates the content of the site
     * in the site.txt
     *
     * @param array $content
     * @return self
     */
    public function update(array $content = []): self
    {
        return $this->store()->update($content);
    }

    /**
     * Returns the Url
     *
     * @return string
     */
    public function url()
    {
        return $this->url ?? '/';
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
            throw new Exception('Invalid page object');
        }

        // set the current active page
        $this->setPage($page);

        // return the page
        return $page;
    }

}
