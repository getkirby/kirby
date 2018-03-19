<?php

namespace Kirby\Cms;

use Closure;
use Exception;
use Kirby\Util\A;
use Kirby\Util\Str;

/**
 * The Page class is the heart and soul of
 * Kirby. It is used to construct pages and
 * all their dependencies like children,
 * files, content, etc.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 */
class Page extends Model
{

    use PageActions;
    use HasChildren;
    use HasContent;
    use HasFiles;
    use HasSiblings;
    use HasStore;
    use HasTemplate;

    /**
     * Registry with all Page models
     *
     * @var array
     */
    public static $models = [];

    /**
     * Properties that should be converted to array
     *
     * @var array
     */
    protected static $toArray = [
        'children',
        'content',
        'files',
        'id',
        'num',
        'parent',
        'slug',
        'template',
        'uid',
        'url'
    ];

    /**
     * The PageBlueprint object
     *
     * @var PageBlueprint
     */
    protected $blueprint;

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
     * The Page id
     *
     * @var string
     */
    protected $id;

    /**
     * The sorting number
     *
     * @var integer|null
     */
    protected $num;

    /**
     * The parent page
     *
     * @var Page|null
     */
    protected $parent;

    /**
     * The parent Site object
     *
     * @var Site|null
     */
    protected $site;

    /**
     * The URL-appendix aka slug
     *
     * @var string
     */
    protected $slug;

    /**
     * The page url
     *
     * @var string|null
     */
    protected $url;

    /**
     * Creates a new page object
     *
     * @param array $props
     */
    public function __construct(array $props)
    {
        $this->setProperties($props);

        // set the id, depending on the parent
        if ($parent = $this->parent()) {
            $this->id = $parent->id() . '/' . $this->slug();
        } else {
            $this->id = $this->slug();
        }
    }

    /**
     * @return PageBlueprint
     */
    public function blueprint(): PageBlueprint
    {
        if (is_a($this->blueprint, PageBlueprint::class) === true) {
            return $this->blueprint;
        }

        return $this->blueprint = PageBlueprint::factory('pages/' . $this->template(), 'pages/default', $this);
    }

    /**
     * Checks if the page can be cached in the
     * pages cache. This will also check if one
     * of the ignore rules from the config kick in.
     *
     * @return boolean
     */
    protected function canBeCached(): bool
    {
        $kirby   = $this->kirby();
        $cache   = $kirby->cache('pages');
        $request = $kirby->request();
        $options = $cache->options();
        $ignore  = $options['ignore'] ?? null;

        // the pages cache is switched off
        if (($options['active'] ?? false) === false) {
            return false;
        }

        // disable the pages cache for incomin requests or special data
        if ((string)$request->method() !== 'GET' || empty($request->data()) === false) {
            return false;
        }

        // check for a custom ignore rule
        if (is_a($ignore, Closure::class)) {
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
     * Returns the Children collection for this page
     * The HasChildren trait takes care of the rest
     *
     * @return Pages|Children
     */
    public function children(): Pages
    {
        if (is_a($this->children, Pages::class) === true) {
            return $this->children;
        }

        return $this->children = $this->store()->children();
    }

    /**
     * Clone the page object and
     * optionally convert it to a draft object
     *
     * @param array $props
     * @return self
     */
    public function clone(array $props = [], string $to = null)
    {
        if ($to === null || $to === static::class) {
            return parent::clone($props);
        }

        return new $to(array_replace_recursive($this->propertyData, $props));
    }

    /**
     * Returns the default parent collection
     *
     * @return Collection
     */
    public function collection()
    {
        if (is_a($this->collection, Collection::class)) {
            return $this->collection;
        }

        if ($parent = $this->parent()) {
            return $this->collection = $parent->children();
        }

        return $this->collection = new Pages([$this]);
    }

    protected function defaultStore()
    {
        return PageStoreDefault::class;
    }

    /**
     * Sorting number + Slug
     *
     * @return string
     */
    public function dirname(): string
    {
        return $this->num() !== null ? $this->num() . '.' . $this->slug() : $this->slug();
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

        if ($parent = $this->parent()) {
            return $this->diruri = $this->parent()->diruri() . '/' . $this->dirname();
        }

        return $this->diruri = $this->dirname();
    }

    /**
     * @param string $path
     * @return PageDraft|null
     */
    public function draft(string $path)
    {
        return PageDraft::seek($path);
    }

    /**
     * Return all drafts for the page
     *
     * @return Children
     */
    public function drafts(): Children
    {
        return new Children(array_map([PageDraft::class, 'factory'], $this->store()->drafts()), $this);
    }

    /**
     * Returns all content validation errors
     *
     * @return array
     */
    public function errors(): array
    {
        $errors = [];

        foreach ($this->blueprint()->sections() as $section) {
            $errors = array_merge($errors, array_values($section->errors()));
        }

        return $errors;
    }

    /**
     * Checks if the page exists in the store
     *
     * @return bool
     */
    public function exists(): bool
    {
        return $this->store()->exists();
    }

    /**
     * Constructs a Page object and also
     * takes page models into account.
     *
     * @return self
     */
    public static function factory($props): self
    {
        if (empty(static::$models) === true) {
            return new static($props);
        }

        if (empty($props['template']) === false) {
            return static::model($props['template'], $props);
        }

        return new static($props);
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

        return $this->store()->files();
    }

    /**
     * Checks if there's a next invisible
     * page in the siblings collection
     *
     * @return bool
     */
    public function hasNextInvisible(): bool
    {
        return $this->nextInvisible() !== null;
    }

    /**
     * Checks if there's a next visible
     * page in the siblings collection
     *
     * @return bool
     */
    public function hasNextVisible(): bool
    {
        return $this->nextVisible() !== null;
    }

    /**
     * Checks if there's a previous invisible
     * page in the siblings collection
     *
     * @return bool
     */
    public function hasPrevInvisible(): bool
    {
        return $this->prevInvisible() !== null;
    }

    /**
     * Checks if there's a previous visible
     * page in the siblings collection
     *
     * @return bool
     */
    public function hasPrevVisible(): bool
    {
        return $this->prevVisible() !== null;
    }

    /**
     * Returns the Page Id
     *
     * @return string
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * Compares the current object with the given page object
     *
     * @param Page $page
     * @return bool
     */
    public function is(Page $page): bool
    {
        return $this->id() === $page->id();
    }

    /**
     * Checks if the page is the current page
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->site()->page()->is($this);
    }

    /**
     * Checks if the current page is a draft
     *
     * @return boolean
     */
    public function isDraft(): bool
    {
        return static::class === PageDraft::class;
    }

    /**
     * Checks if the page is the error page
     *
     * @return bool
     */
    public function isErrorPage(): bool
    {
        if ($errorPage = $this->site()->errorPage()) {
            return $errorPage->is($this);
        }

        return false;
    }

    /**
     * Checks if the page is the home page
     *
     * @return bool
     */
    public function isHomePage(): bool
    {
        if ($homePage = $this->site()->homePage()) {
            return $homePage->is($this);
        }

        return false;
    }

    /**
     * It's often required to check for the
     * home and error page to stop certain
     * actions. That's why there's a shortcut.
     *
     * @return boolean
     */
    public function isHomeOrErrorPage(): bool
    {
        return $this->isHomePage() === true || $this->isErrorPage() === true;
    }

    /**
     * Checks if the page is invisible
     *
     * @return bool
     */
    public function isInvisible(): bool
    {
        return $this->isUnlisted();
    }

    /**
     * Checks if the page has a sorting number
     *
     * @return boolean
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
        return $this->isActive() || $this->site()->page()->parents()->has($this->id());
    }

    /**
     * Checks if the page has no sorting number
     *
     * @return boolean
     */
    public function isUnlisted(): bool
    {
        return $this->num() === null;
    }

    /**
     * Checks if the page is visible
     *
     * @return bool
     */
    public function isVisible(): bool
    {
        return $this->isListed();
    }

    /**
     * The page's base url for any files
     *
     * @return string
     */
    public function mediaUrl(): string
    {
        return $this->kirby()->url('media') . '/pages/' . $this->id();
    }

    /**
     * Creates a Page model if it has been registered
     *
     * @param string $name
     * @param array $props
     * @return Page
     */
    public static function model(string $name, array $props = [])
    {
        if ($class = (static::$models[$name] ?? null)) {
            $object = new $class($props);

            if (is_a($object, Page::class)) {
                return $object;
            }
        }

        return new static($props);
    }

    /**
     * Returns the next invisible page if it exists
     *
     * @return self|null
     */
    public function nextInvisible()
    {
        return $this->nextAll()->invisible()->first();
    }

    /**
     * Returns the next visible page if it exists
     *
     * @return self|null
     */
    public function nextVisible()
    {
        return $this->nextAll()->visible()->first();
    }

    /**
     * Returns the sorting number
     *
     * @return integer|null
     */
    public function num()
    {
        return $this->num;
    }

    /**
     * Returns the parent Page object
     *
     * @return Page|null
     */
    public function parent()
    {
        return $this->parent;
    }

    /**
     * Returns the parent model,
     * which can either be another Page
     * or the Site
     *
     * @return Page|Site
     */
    public function parentModel()
    {
        return $this->parent() ?? $this->site();
    }

    /**
     * Returns a list of all parents and their parents recursively
     *
     * @return Pages
     */
    public function parents(): Pages
    {
        $parents = new Pages;
        $page    = $this->parent();

        while ($page !== null) {
            $parents->append($page->id(), $page);
            $page = $page->parent();
        }

        return $parents;
    }

    /**
     * Returns the previous invisible page
     *
     * @return self|null
     */
    public function prevInvisible()
    {
        return $this->prevAll()->invisible()->first();
    }

    /**
     * Returns the previous visible page
     *
     * @return self|null
     */
    public function prevVisible()
    {
        return $this->prevAll()->visible()->last();
    }

    public function render(array $data = [], $contentType = 'html'): string
    {
        $kirby = $this->kirby();
        $cache = $cacheId = $result = null;

        // try to get the page from cache
        if (empty($data) === true && $this->canBeCached() === true) {
            $cache   = $kirby->cache('pages');
            $cacheId = $this->id() . '.' . $contentType;
            $result  = $cache->get($cacheId);

            if ($result !== null) {
                return $result;
            }
        }

        // create all globals for the
        // controller, template and snippets
        $globals = array_merge($data, [
            'kirby' => $kirby,
            'site'  => $site = $this->site(),
            'pages' => $site->children(),
            'page'  => $site->visit($this)
        ]);

        // try to create the page template
        $template = $kirby->component('template', $this->template(), [], $contentType);

        // fall back to the default template if it doesn't exist
        if ($template->exists() === false) {
            $template = $kirby->component('template', 'default', [], $contentType);
        }

        // react if even the default template does not exist
        if ($template->exists() === false) {
            if ($this->isErrorPage() === true) {
                throw new Exception('The error template does not exist');
            } else {
                throw new Exception('The default template does not exist');
            }
        }

        // call the template controller if there's one.
        $globals = array_merge($kirby->controller($template->name(), $globals), $globals);

        // make all globals available
        // for templates and snippets
        Template::globals($globals);

        // render the page
        $result = $template->render();

        // render the template and cache the result
        if ($cache !== null) {
            $cache->set($cacheId, $result);
        }

        return $result;
    }

    /**
     * Returns the absolute root to the page directory
     * No matter if it exists or not.
     *
     * @return string
     */
    public function root(): string
    {
        return $this->kirby()->root('content') . '/' . $this->diruri();
    }

    /**
     * Returns the PageRules class instance
     * which is being used in various methods
     * to check for valid actions and input.
     *
     * @return PageRules
     */
    protected function rules()
    {
        return new PageRules();
    }

    /**
     * Sets the Blueprint object
     *
     * @param PageBlueprint|null $blueprint
     * @return self
     */
    protected function setBlueprint(PageBlueprint $blueprint = null): self
    {
        $this->blueprint = $blueprint;
        return $this;
    }

    /**
     * Sets the sorting number
     *
     * @param integer $num
     * @return self
     */
    protected function setNum(int $num = null): self
    {
        $this->num = $num === null ? $num : intval($num);
        return $this;
    }

    /**
     * Sets the parent page object
     *
     * @param Page|null $parent
     * @return self
     */
    protected function setParent(Page $parent = null): self
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Sets the required Page slug
     *
     * @param string $slug
     * @return self
     */
    protected function setSlug(string $slug): self
    {
        $this->slug = Str::slug($slug);
        return $this;
    }

    /**
     * Sets the Url
     *
     * @param string $url
     * @return self
     */
    protected function setUrl(string $url = null): self
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
     * @return string
     */
    public function slug(): string
    {
        return $this->slug;
    }

    /**
     * @return string draft, listed or unlisted
     */
    public function status()
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
     * Returns the title field or the slug as fallback
     *
     * @return ContentField
     */
    public function title(): ContentField
    {
        return $this->content()->get('title')->or($this->slug());
    }

    /**
     * Returns the UID of the page
     *
     * @see self::slug()
     * @return string
     */
    public function uid(): string
    {
        return $this->slug();
    }

    /**
     * Returns the Url
     *
     * @return string
     */
    public function url(): string
    {
        if (is_string($this->url) === true) {
            return $this->url;
        }

        if ($parent = $this->parent()) {
            return $this->url = $this->parent()->url() . '/' . $this->slug();
        }

        return $this->site()->url() . '/' . $this->slug();
    }

}
