<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Data\Data;
use Kirby\Exception\Exception;
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
class Page extends ModelWithContent
{
    use PageActions;
    use PageSiblings;
    use HasChildren;
    use HasFiles;
    use HasMethods;
    use HasSiblings;

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
     * @param bool $relative
     * @return string
     */
    public function apiUrl(bool $relative = false): string
    {
        if ($relative === true) {
            return 'pages/' . $this->panelId();
        } else {
            return $this->kirby()->url('api') . '/pages/' . $this->panelId();
        }
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
     * @params string $inSection
     * @return array
     */
    public function blueprints(string $inSection = null): array
    {
        $blueprints = [];
        $blueprint  = $this->blueprint();
        $sections   = $inSection !== null ? [$blueprint->section($inSection)] : $blueprint->sections();

        foreach ($sections as $section) {
            if ($section === null || $section->type() !== 'pages') {
                continue;
            }

            foreach ($section->blueprints() as $blueprint) {
                $blueprints[$blueprint['name']] = $blueprint;
            }
        }

        return array_values($blueprints);
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
     * @param string $languageCode
     * @return string
     */
    public function contentFile(string $languageCode = null): string
    {
        if ($languageCode !== null) {
            return $this->root() . '/' . $this->intendedTemplate() . '.' . $languageCode . '.' . $this->kirby()->contentExtension();
        }

        // use the cached version
        if ($this->contentFile !== null) {
            return $this->contentFile;
        }

        // create from template
        return $this->contentFile = $this->root() . '/' . $this->intendedTemplate() . '.' . $this->kirby()->contentExtension();
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
        return $this->num() !== null ? $this->num() . Dir::$numSeparator . $this->uid() : $this->uid();
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
            return $this->diruri = $this->parent()->diruri() . '/' . $dirname;
        } else {
            return $this->diruri = $dirname;
        }
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
            return $this->id = $parent->id() . '/' . $this->uid();
        }

        return $this->id = $this->uid();
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
        return $this->isDraft;
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
     * Checks if the page access is verified.
     * This is only used for drafts so far.
     *
     * @param string $token
     * @return boolean
     */
    public function isVerified(string $token = null)
    {
        if ($this->isDraft() === false) {
            return true;
        }

        if ($token === null) {
            return false;
        }

        return $this->token() === $token;
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
     * Returns the panel icon definition
     * according to the blueprint settings
     *
     * @return array
     */
    public function panelIcon(): array
    {
        if ($icon = $this->blueprint()->icon()) {

            // check for emojis
            if (strlen($icon) !== Str::length($icon)) {
                return [
                    'type'  => $icon,
                    'back'  => 'black',
                    'emoji' => true
                ];
            }

            return [
                'type' => $icon,
                'back' => 'black',
            ];
        }

        return [
            'type' => 'file',
            'back' => 'black',
        ];
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
     * @param string|array|false $settings
     * @param array|null $thumbSettings
     * @return array|null
     */
    public function panelImage($settings = null, array $thumbSettings = null): ?array
    {
        $defaults = [
            'ratio' => '3/2',
            'back'  => 'pattern',
            'cover' => false
        ];

        // switch the image off
        if ($settings === false) {
            return null;
        }

        if (is_string($settings) === true) {
            $settings = [
                'query' => $settings
            ];
        }

        if ($image = $this->query($settings['query'] ?? 'page.image', 'Kirby\Cms\File')) {
            $settings['url'] = $image->thumb($thumbSettings)->url(true) . '?t=' . $image->modified();

            unset($settings['query']);

            return array_merge($defaults, $settings);
        }

        return null;
    }

    /**
     * Returns the url to the editing view
     * in the panel
     *
     * @return string
     */
    public function panelUrl(bool $relative = false): string
    {
        if ($relative === true) {
            return '/pages/' . $this->panelId();
        } else {
            return $this->kirby()->url('panel') . '/pages/' . $this->panelId();
        }
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
     * Returns the parent id, if a parent exists
     *
     * @return string|null
     */
    public function parentId(): ?string
    {
        if ($parent = $this->parent()) {
            return $parent->id();
        }

        return null;
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
     * @return PagePermissions
     */
    public function permissions()
    {
        return new PagePermissions($this);
    }

    /**
     * Draft preview Url
     *
     * @return string
     */
    public function previewUrl(): string
    {
        if ($this->isDraft() === true) {
            return $this->url() . '?token=' . $this->token();
        } else {
            return $this->url();
        }
    }

    /**
     * Creates a string query, starting from the model
     *
     * @param string|null $query
     * @param string|null $expect
     * @return mixed
     */
    public function query(string $query = null, string $expect = null)
    {
        if ($query === null) {
            return null;
        }

        $result = Str::query($query, [
            'kirby' => $this->kirby(),
            'site'  => $this->site(),
            'page'  => $this
        ]);

        if ($expect !== null && is_a($result, $expect) !== true) {
            return null;
        }

        return $result;
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
     * Sets the draft flag
     *
     * @param boolean $isDraft
     * @return self
     */
    protected function setIsDraft(bool $isDraft = null): self
    {
        $this->isDraft = $isDraft ?? false;
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
     * @param string|null $languageCode
     * @return string
     */
    public function slug(string $languageCode = null): string
    {
        if ($this->kirby()->multilang() === true) {
            if ($languageCode === null) {
                $languageCode = $this->kirby()->language()->code();
            }
            return $this->translations()->find($languageCode)->slug() ?? $this->slug;
        } else {
            return $this->slug;
        }
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
            'children'     => $this->children()->keys(),
            'content'      => $this->content()->toArray(),
            'files'        => $this->files()->keys(),
            'id'           => $this->id(),
            'mediaUrl'     => $this->mediaUrl(),
            'mediaRoot'    => $this->mediaRoot(),
            'num'          => $this->num(),
            'parent'       => $this->parent() ? $this->parent()->id(): null,
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
        return sha1($this->id() . $this->template());
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
            } else {
                return $this->urlForLanguage(null, $options);
            }
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
            return $this->url = $this->parent()->url() . '/' . $this->uid();
        }

        return $this->url = $this->kirby()->url('base') . '/' . $this->uid();
    }

    public function urlForLanguage($language = null, array $options = null): string
    {
        if ($options !== null) {
            return Url::to($this->urlForLanguage($language), $options);
        }

        if ($this->isHomePage() === true) {
            return $this->url = $this->site()->urlForLanguage($language);
        }

        if ($parent = $this->parent()) {
            return $this->url = $this->parent()->urlForLanguage($language) . '/' . $this->slug($language);
        }

        return $this->url = $this->site()->urlForLanguage($language) . '/' . $this->slug($language);
    }
}
