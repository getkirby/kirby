<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Data\Data;
use Kirby\Exception\NotFoundException;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;
use Throwable;

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
    use PageSiblings;
    use HasChildren;
    use HasContent;
    use HasFiles;
    use HasMethods;
    use HasSiblings;
    use HasTranslations;

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
     * @var PageBlueprint
     */
    protected $blueprint;

    /**
     * @var string
     */
    protected $contentFile;

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
     * The template, that should be loaded
     * if it exists
     *
     * @var Template
     */
    protected $intendedTemplate;

    /**
     * @var array
     */
    protected $inventory;

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
     * Absolute path to the page directory
     *
     * @var string
     */
    protected $root;

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
     * The intended page template
     *
     * @var string
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
     * @param array $args
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
        return $this->content()->get($method, $arguments);
    }

    /**
     * Creates a new page object
     *
     * @param array $props
     */
    public function __construct(array $props)
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
            'content'    => $this->content(),
            'children'   => $this->children(),
            'siblings'   => $this->siblings(),
            'files'      => $this->files(),
        ]);
    }

    /**
     * Returns the blueprint object
     *
     * @return PageBlueprint
     */
    public function blueprint(): PageBlueprint
    {
        if (is_a($this->blueprint, 'Kirby\Cms\PageBlueprint') === true) {
            return $this->blueprint;
        }

        return $this->blueprint = PageBlueprint::factory('pages/' . $this->intendedTemplate(), 'pages/default', $this);
    }

    /**
     * Returns an array with all blueprints that are available for the page
     *
     * @return array
     */
    public function blueprints(): array
    {
        if ($parent = $this->parentModel()) {
            $blueprints = [];

            foreach ($parent->blueprint()->sections() as $section) {
                if (is_a($section, 'Kirby\Cms\BlueprintPagesSection') === false) {
                    continue;
                }

                $blueprints = array_map("unserialize", array_unique(array_map("serialize", array_merge($blueprints, $section->blueprints()))));
            }

            return $blueprints;
        }

        return [];
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
        if (is_a($ignore, 'Closure') === true) {
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
        if (is_a($this->collection, 'Kirby\Cms\Collection')) {
            return $this->collection;
        }

        if ($parent = $this->parentModel()) {
            return $this->collection = $parent->children();
        }

        return $this->collection = new Pages([$this]);
    }

    /**
     * Returns the content text file
     * which is found by the inventory method
     *
     * @return string|null
     */
    public function contentFile(): ?string
    {
        // use the cached version
        if ($this->contentFile !== null) {
            return $this->contentFile;
        }

        // create from template if the template is already set
        if ($template = $this->intendedTemplate()) {
            return $this->contentFile = $this->root() . '/' . $template . '.txt';
        }

        // detect from the inventory
        return $this->contentFile = $this->inventory()['content'];
    }

    /**
     * Call the page controller
     *
     * @param array $data
     * @param string $contentType
     * @return array
     */
    public function controller($data = [], $contentType = 'html'): array
    {
        // create the template data
        $data = array_merge($data, [
            'kirby' => $kirby = $this->kirby(),
            'site'  => $site  = $this->site(),
            'pages' => $site->children(),
            'page'  => $site->visit($this)
        ]);

        // call the template controller if there's one.
        return array_merge($kirby->controller($this->template()->name(), $data, $contentType), $data);
    }

    /**
     * Returns a number indicating how deep the page
     * is nested within the content folder
     *
     * @return integer
     */
    public function depth(): int
    {
        return $this->depth = $this->depth ?? (substr_count($this->id(), '/') + 1);
    }

    /**
     * Sorting number + Slug
     *
     * @return string
     */
    public function dirname(): string
    {
        return $this->num() !== null ? $this->num() . Dir::$numSeparator . $this->slug() : $this->slug();
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
     * Provides a kirbytag or markdown
     * tag for the page, which will be
     * used in the panel, when the page
     * gets dragged onto a textarea
     *
     * @return string
     */
    public function dragText($type = 'kirbytext'): string
    {
        switch ($type) {
            case 'kirbytext':
                return '(link: ' . $this->id() . ' text: ' . $this->title() . ')';
            case 'markdown':
                return '[' . $this->title() . '](' . $this->url() . ')';
        }
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
            $errors = array_merge($errors, $section->errors());
        }

        return $errors;
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
     * @return self
     */
    public static function factory($props): self
    {
        if (empty($props['model']) === false) {
            return static::model($props['model'], $props);
        }

        return new static($props);
    }

    /**
     * Checks if the intended template
     * for the page exists.
     *
     * @return boolean
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
            return $this->id = $parent->id() . '/' . $this->slug();
        }

        return $this->id = $this->slug();
    }

    /**
     * Returns the template that should be
     * loaded if it exists.
     *
     * @return Template
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
     * @return array
     */
    public function inventory(): array
    {
        return $this->inventory = $this->inventory ?? Dir::inventory($this->root());
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
        if ($page = $this->site()->page()) {
            if ($page->is($this) === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the page is a direct or indirect ancestor of the given $page object
     *
     * @return boolean
     */
    public function isAncestorOf(Page $child): bool
    {
        return $child->parents()->has($this->id()) === true;
    }

    /**
     * Checks if the page is a child of the given page
     *
     * @return boolean
     */
    public function isChildOf(Page $parent): bool
    {
        return $this->parent()->is($parent);
    }

    /**
     * Checks if the page is a descendant of the given page
     *
     * @return boolean
     */
    public function isDescendantOf(Page $parent): bool
    {
        return $this->parents()->has($parent->id()) === true;
    }

    /**
     * Checks if the page is a descendant of the currently active page
     *
     * @return boolean
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
     * @return boolean
     */
    public function isDraft(): bool
    {
        return static::class === 'Kirby\Cms\PageDraft';
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
        if ($this->isActive() === true) {
            return true;
        }

        if ($page = $this->site()->page()) {
            if ($page->parents()->has($this->id()) === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a page can be sorted
     *
     * @return boolean
     */
    public function isSortable(): bool
    {
        if ($this->isErrorPage() === true) {
            return false;
        }

        if ($this->isListed() !== true) {
            return false;
        }

        if ($this->blueprint()->num() !== 'default') {
            return false;
        }

        if ($this->blueprint()->options()->sort() !== true) {
            return false;
        }

        return true;
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
     * Returns the root to the media folder for the page
     *
     * @return string
     */
    public function mediaRoot(): string
    {
        return $this->kirby()->root('media') . '/pages/' . $this->id();
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

            if (is_a($object, 'Kirby\Cms\Page') === true) {
                return $object;
            }
        }

        return new static($props);
    }

    /**
     * Returns the last modification date of the page
     *
     * @param string $format
     * @return int|string
     */
    public function modified(string $format = 'U')
    {
        return date($format, filemtime($this->contentFile()));
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
     * Returns the escaped Id, which is
     * used in the panel to make routing work properly
     *
     * @return string
     */
    public function panelId(): string
    {
        return str_replace('/', '+', $this->id());
    }

    /**
     * Returns the url to the editing view
     * in the panel
     *
     * @return string
     */
    public function panelUrl(): string
    {
        return $this->kirby()->url('panel') . '/pages/' . $this->panelId();
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
     * Returns the permissions object for this page
     *
     * @return BlueprintOptions
     */
    public function permissions()
    {
        return $this->blueprint()->options();
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
     */
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

        // fetch all data for the page
        $kirby->data = $this->controller($data, $contentType);

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

        // render the page
        $result = $template->render($kirby->data);

        // render the template and cache the result
        if ($cache !== null) {
            $cache->set($cacheId, $result);
        }

        return new Response($result, $contentType);
    }

    /**
     * @return Template
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
        return $this->root = $this->root ?? $this->kirby()->root('content') . '/' . $this->diruri();
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
     * @param array|null $blueprint
     * @return self
     */
    protected function setBlueprint(array $blueprint = null): self
    {
        if ($blueprint !== null) {
            $blueprint['model'] = $this;
            $this->blueprint = new PageBlueprint($blueprint);
        }

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
        $this->slug = $slug;
        return $this;
    }

    /**
     * Sets the intended template
     *
     * @param string $template
     * @return self
     */
    protected function setTemplate(string $template = null): self
    {
        if ($template !== null) {
            $this->intendedTemplate = $this->kirby()->template($template);
        }

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
     * Returns the page status, which
     * can be `draft`, `listed` or `unlisted`
     *
     * @return string
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
     * Returns the final template
     *
     * @return Template
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
     * @return Field
     */
    public function title(): Field
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
            'children'  => $this->children()->keys(),
            'content'   => $this->content()->toArray(),
            'files'     => $this->files()->keys(),
            'id'        => $this->id(),
            'mediaUrl'  => $this->mediaUrl(),
            'mediaRoot' => $this->mediaRoot(),
            'num'       => $this->num(),
            'parent'    => $this->parent() ? $this->parent()->id() : null,
            'slug'      => $this->slug(),
            'template'  => $this->template(),
            'url'       => $this->url()
        ];
    }

    /**
     * String template builder
     *
     * @param string|null $template
     * @return string
     */
    public function toString(string $template = null): string
    {
        if ($template === null) {
            return $this->id();
        }

        return Str::template($template, [
            'page'  => $this,
            'site'  => $this->site(),
            'kirby' => $this->kirby()
        ]);
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
     * @param array|null $options
     * @return string
     */
    public function url($options = null): string
    {
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
            return $this->url = $this->parent()->url() . '/' . $this->slug();
        }

        return $this->url = $this->kirby()->url('base') . '/' . $this->slug();
    }
}
