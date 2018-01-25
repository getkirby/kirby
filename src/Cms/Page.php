<?php

namespace Kirby\Cms;

use Exception;
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

    use HasChildren;
    use HasContent;
    use HasFiles;
    use HasSiblings;

    /**
     * Registry with all Page models
     *
     * @var array
     */
    protected static $models = [];

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
        'root',
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
     * The root to the page directory
     *
     * @var string|null
     */
    protected $root;

    /**
     * The parent Site object
     *
     * @var Site|null
     */
    protected $site;

    /**
     * The template name
     *
     * @var string|null
     */
    protected $template;

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
        $this->setRequiredProperties($props, ['id']);
        $this->setOptionalProperties($props, [
            'blueprint',
            'children',
            'collection',
            'content',
            'files',
            'num',
            'kirby',
            'parent',
            'root',
            'site',
            'template',
            'url'
        ]);

    }

    /**
     * @return PageBlueprint
     */
    public function blueprint(): PageBlueprint
    {
        if (is_a($this->blueprint, PageBlueprint::class) === true) {
            return $this->blueprint;
        }

        return $this->blueprint = $this->store()->blueprint();
    }

    /**
     * Changes the slug/uid of the page
     *
     * @param string $slug
     * @return self
     */
    public function changeSlug(string $slug): self
    {
        $this->rules()->check('page.change.slug', $this, $slug);
        $this->perms()->check('page.change.slug', $this, $slug);

        return $this->store()->changeSlug($slug);
    }

    /**
     * Changes the page template
     *
     * @param string $template
     * @return self
     */
    public function changeTemplate(string $template): self
    {
        $this->rules()->check('page.change.template', $this, $template);
        $this->perms()->check('page.change.template', $this, $template);

        return $this->store()->changeTemplate($template);
    }

    /**
     * Changes the visibility/status of the page
     *
     * @param string $status
     * @param int $position
     * @return self
     */
    public function changeStatus(string $status, int $position = null): self
    {
        $this->rules()->check('page.change.status', $this, $status, $position);
        $this->perms()->check('page.change.status', $this, $status, $position);

        return $this->store()->changeStatus($status, $position);
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
     * Clones the current page object with basic
     * initial values for the clone
     *
     * @param array $props
     * @return self
     */
    public function clone(array $props = []): self
    {
        return new static(array_merge([
            'id'     => $this->id(),
            'parent' => $this->parent(),
            'root'   => $this->root(),
            'site'   => $this->site(),
            'url'    => $this->url(),
        ], $props));
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

        if ($site = $this->site()) {
            return $this->collection = $site->children();
        }

        return $this->collection = new Pages([$this]);
    }

    /**
     * Returns the Content class with
     * all ContentFields for the page
     *
     * @return Content
     */
    public function content(): Content
    {
        if (is_a($this->content, Content::class) === true) {
            return $this->content;
        }

        return $this->content = $this->store()->content();
    }

    /**
     * Creates a new page
     *
     * @param array $props
     * @return self
     */
    public static function create(array $props): self
    {
        $defaults = [
            'parent'   => null,
            'template' => 'default',
            'content'  => [],
            'slug'     => null
        ];

        $props = array_merge($defaults, $props);

        // convert all array items to variables
        extract($props);

        if (empty($slug) === true) {
            $slug = $content['title'] ?? uniqid();
        }

        $slug = Str::slug($slug);

        static::rules()->check('page.create', $parent, $slug, $template, $content);
        static::perms()->check('page.create', $parent, $slug, $template, $content);

        return static::store()->commit('page.create', $parent, $slug, $template, $content);
    }

    /**
     * Deletes the page
     *
     * @return bool
     */
    public function delete(): bool
    {
        $this->rules()->check('page.delete', $this);
        $this->perms()->check('page.delete', $this);

        return $this->store()->delete();
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

        if (empty($props['root']) === false) {
            foreach (static::$models as $template => $class) {
                if (is_file($props['root'] . '/' . $template . '.txt')) {
                    return static::model($template, $props);
                }
            }
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
     * Changes the status to unlisted
     *
     * @return self
     */
    public function hide(): self
    {
        return $this->changeStatus('unlisted');
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
     * Checks if the page is the error page
     *
     * @return bool
     */
    public function isErrorPage(): bool
    {
        return $this->site()->errorPage()->is($this);
    }

    /**
     * Checks if the page is the home page
     *
     * @return bool
     */
    public function isHomePage(): bool
    {
        return $this->site()->homePage()->is($this);
    }

    /**
     * Checks if the page is invisible
     *
     * @return bool
     */
    public function isInvisible(): bool
    {
        return $this->isVisible() === false;
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
     * Checks if the page is visible
     *
     * @return bool
     */
    public function isVisible(): bool
    {
        return $this->num() !== null;
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
     * Setter and getter for Page models
     *
     * @param null|array $models
     * @return array
     */
    public static function models(array $models = null): array
    {
        if ($models === null) {
            return static::$models;
        }

        return static::$models = $models;
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
        return $this->prevAll()->visible()->first();
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
     * @param PageBlueprint|null $blueprint
     * @return self
     */
    protected function setBlueprint(PageBlueprint $blueprint = null): self
    {
        $this->blueprint = $blueprint;
        return $this;
    }

    /**
     * Sets the Content object
     *
     * @param Content|null $content
     * @return self
     */
    protected function setContent(Content $content = null): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Sets the Page id
     *
     * @param string $id
     * @return self
     */
    protected function setId(string $id): self
    {
        $this->id = trim($id, '/');
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
        $this->num = $num === null ?: intval($num);
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
     * Sets the template name
     *
     * @param string $template
     * @return self
     */
    protected function setTemplate(string $template = null): self
    {
        $this->template = $template;
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
        $this->url = rtrim($url, '/');
        return $this;
    }

    /**
     * Returns the slug of the page
     *
     * @return string
     */
    public function slug(): string
    {
        return basename($this->id());
    }

    /**
     * Changes the page number
     *
     * @param int $position
     * @return self
     */
    public function sort(int $position): self
    {
        return $this->changeStatus('listed', $position);
    }

    /**
     * @return PageStore
     */
    public function store(): PageStore
    {
        return App::instance()->component('PageStore', $this);
    }

    /**
     * Returns the template name
     *
     * @return string
     */
    public function template(): string
    {
        return $this->template = $this->template ?? $this->store()->template();
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
     * Updates the page content
     *
     * @param array $content
     * @return self
     */
    public function update(array $content = []): self
    {
        $this->rules()->check('page.update', $this, $content);
        $this->perms()->check('page.update', $this, $content);

        return $this->store()->update($content);
    }

    /**
     * Returns the Url
     *
     * @return string
     */
    public function url(): string
    {
        return $this->url ?? '/' . $this->id();
    }

}
