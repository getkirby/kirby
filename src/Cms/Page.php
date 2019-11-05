<?php

namespace Kirby\Cms;

use Kirby\Exception\Exception;
use Kirby\Exception\NotFoundException;
use Kirby\Http\Uri;
use Kirby\Toolkit\A;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Str;

/**
 * The `$page` object is the heart and
 * soul of Kirby. It is used to construct
 * pages and all their dependencies like
 * children, files, content, etc.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Page extends ModelWithContent
{
    const CLASS_ALIAS = 'page';

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
        return $this->content()->get($method, $arguments);
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
            return 'pages/' . $this->panelId();
        } else {
            return $this->kirby()->url('api') . '/pages/' . $this->panelId();
        }
    }

    /**
     * Returns the blueprint object
     *
     * @return \Kirby\Cms\PageBlueprint
     */
    public function blueprint()
    {
        if (is_a($this->blueprint, 'Kirby\Cms\PageBlueprint') === true) {
            return $this->blueprint;
        }

        return $this->blueprint = PageBlueprint::factory('pages/' . $this->intendedTemplate(), 'pages/default', $this);
    }

    /**
     * Returns an array with all blueprints that are available for the page
     *
     * @param string $inSection
     * @return array
     */
    public function blueprints(string $inSection = null): array
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
            } catch (Exception $e) {
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
     * @param string $languageCode
     * @return array
     */
    public function contentFileData(array $data, string $languageCode = null): array
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
     * @param string $languageCode
     * @return string
     */
    public function contentFileName(string $languageCode = null): string
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
     * @return int
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
        if ($this->dirname !== null) {
            return $this->dirname;
        }

        if ($this->num() !== null) {
            return $this->dirname = $this->num() . Dir::$numSeparator . $this->uid();
        } else {
            return $this->dirname = $this->uid();
        }
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
     * @internal
     * @param string $type (null|auto|kirbytext|markdown)
     * @return string
     */
    public function dragText(string $type = null): string
    {
        $type = $type ?? 'auto';

        if ($type === 'auto') {
            $type = option('panel.kirbytext', true) ? 'kirbytext' : 'markdown';
        }

        switch ($type) {
            case 'markdown':
                return '[' . $this->title() . '](' . $this->url() . ')';
            default:
                return '(link: ' . $this->id() . ' text: ' . $this->title() . ')';
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
     * @internal
     * @param mixed $props
     * @return self
     */
    public static function factory($props)
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
        if (is_a($page, 'Kirby\Cms\Page') === false) {
            if (is_string($page) === false) {
                return false;
            }

            $page = $this->kirby()->page($page);
        }

        if (is_a($page, 'Kirby\Cms\Page') === false) {
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
     * Checks if the page is a child of the given page
     *
     * @param \Kirby\Cms\Page|string $parent
     * @return bool
     */
    public function isChildOf($parent): bool
    {
        if ($parentObj = $this->parent()) {
            return $parentObj->is($parent);
        }

        return false;
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
     * @deprecated 3.0.0 Use `Page::isUnlisted()` instead
     * @return bool
     */
    public function isInvisible(): bool
    {
        deprecated('$page->isInvisible() is deprecated, use $page->isUnlisted() instead. $page->isInvisible() will be removed in Kirby 3.5.0.');

        return $this->isUnlisted();
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

        if ($page = $this->site()->page()) {
            if ($page->parents()->has($this->id()) === true) {
                return true;
            }
        }

        return false;
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
     * @deprecated 3.0.0 Use `Page::isListed()` instead
     * @return bool
     */
    public function isVisible(): bool
    {
        deprecated('$page->isVisible() is deprecated, use $page->isListed() instead. $page->isVisible() will be removed in Kirby 3.5.0.');

        return $this->isListed();
    }

    /**
     * Checks if the page access is verified.
     * This is only used for drafts so far.
     *
     * @internal
     * @param string $token
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
     * @return self
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
     * @param string|null $handler
     * @return int|string
     */
    public function modified(string $format = null, string $handler = null)
    {
        return F::modified($this->contentFile(), $format, $handler ?? $this->kirby()->option('date.handler', 'date'));
    }

    /**
     * Returns the sorting number
     *
     * @return int|null
     */
    public function num(): ?int
    {
        return $this->num;
    }

    /**
     * Returns the panel icon definition
     * according to the blueprint settings
     *
     * @internal
     * @param array $params
     * @return array
     */
    public function panelIcon(array $params = null): array
    {
        if ($icon = $this->blueprint()->icon()) {
            $params['type'] = $icon;

            // check for emojis
            if (strlen($icon) !== Str::length($icon)) {
                $params['emoji'] = true;
            }
        }

        return parent::panelIcon($params);
    }

    /**
     * Returns the escaped Id, which is
     * used in the panel to make routing work properly
     *
     * @internal
     * @return string
     */
    public function panelId(): string
    {
        return str_replace('/', '+', $this->id());
    }

    /**
     * Returns the image file object based on provided query
     *
     * @internal
     * @param string|null $query
     * @return \Kirby\Cms\File|\Kirby\Cms\Asset|null
     */
    protected function panelImageSource(string $query = null)
    {
        if ($query === null) {
            $query = 'page.image';
        }

        return parent::panelImageSource($query);
    }

    /**
     * Returns the full path without leading slash
     *
     * @internal
     * @return string
     */
    public function panelPath(): string
    {
        return 'pages/' . $this->panelId();
    }

    /**
     * Prepares the response data for page pickers
     * and page fields
     *
     * @param array|null $params
     * @return array
     */
    public function panelPickerData(array $params = []): array
    {
        $image = $this->panelImage($params['image'] ?? []);
        $icon  = $this->panelIcon($image);

        return [
            'dragText'    => $this->dragText(),
            'hasChildren' => $this->hasChildren(),
            'icon'        => $icon,
            'id'          => $this->id(),
            'image'       => $image,
            'info'        => $this->toString($params['info'] ?? false),
            'link'        => $this->panelUrl(true),
            'text'        => $this->toString($params['text'] ?? '{{ page.title }}'),
            'url'         => $this->url(),
        ];
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
     * @param int $code
     * @return string
     */
    public function render(array $data = [], $contentType = 'html'): string
    {
        $kirby = $this->kirby();
        $cache = $cacheId = $html = null;

        // try to get the page from cache
        if (empty($data) === true && $this->isCacheable() === true) {
            $cache    = $kirby->cache('pages');
            $cacheId  = $this->cacheId($contentType);
            $result   = $cache->get($cacheId);
            $html     = $result['html'] ?? null;
            $response = $result['response'] ?? [];

            // reconstruct the response configuration
            if (empty($html) === false && empty($response) === false) {
                $kirby->response()->fromArray($response);
            }
        }

        // fetch the page regularly
        if ($html === null) {
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
            $html = $template->render($kirby->data);

            // convert the response configuration to an array
            $response = $kirby->response()->toArray();

            // cache the result
            if ($cache !== null) {
                $cache->set($cacheId, [
                    'html'     => $html,
                    'response' => $response
                ]);
            }
        }

        return $html;
    }

    /**
     * @internal
     * @param mixed $type
     * @return \Kirby\Cms\Template
     */
    public function representation($type)
    {
        $kirby          = $this->kirby();
        $template       = $this->intendedTemplate();
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
     * @return \Kirby\Cms\PageRules
     */
    protected function rules()
    {
        return new PageRules();
    }

    /**
     * Search all pages within the current page
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
            $this->blueprint = new PageBlueprint($blueprint);
        }

        return $this;
    }

    /**
     * Sets the dirname manually, which works
     * more reliable in connection with the inventory
     * than computing the dirname afterwards
     *
     * @param string $dirname
     * @return self
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
     * @return self
     */
    protected function setIsDraft(bool $isDraft = null)
    {
        $this->isDraft = $isDraft ?? false;
        return $this;
    }

    /**
     * Sets the sorting number
     *
     * @param int $num
     * @return self
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
     * @return self
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
     * @return self
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
     * @return self
     */
    protected function setSlug(string $slug)
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
     * @param string $url
     * @return self
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

            if ($translation = $this->translations()->find($languageCode)) {
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
            if ($parent->isHomePage() === true) {
                return $this->url = $this->kirby()->url('base') . '/' . $parent->uid() . '/' . $this->uid();
            } else {
                return $this->url = $this->parent()->url() . '/' . $this->uid();
            }
        }

        return $this->url = $this->kirby()->url('base') . '/' . $this->uid();
    }

    /**
     * Builds the Url for a specific language
     *
     * @internal
     * @param string $language
     * @param array $options
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
            } else {
                return $this->url = $this->parent()->urlForLanguage($language) . '/' . $this->slug($language);
            }
        }

        return $this->url = $this->site()->urlForLanguage($language) . '/' . $this->slug($language);
    }
}
