<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
use Kirby\Exception\Exception;
use Kirby\Image\Image;
use Kirby\Toolkit\Str;
use Throwable;

/**
 * The File class is a wrapper around
 * the Kirby\Image\Image class, which
 * is used to handle all file methods.
 * In addition the File class handles
 * File meta data via Kirby\Cms\Content.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 */
class File extends ModelWithContent
{
    use FileActions;
    use HasMethods;
    use HasSiblings;
    use HasThumbs;

    /**
     * The parent asset object
     * This is used to do actual file
     * method calls, like size, mime, etc.
     *
     * @var Image
     */
    protected $asset;

    /**
     * Cache for the initialized blueprint object
     *
     * @var FileBlueprint
     */
    protected $blueprint;

    /**
     * Cache for the content file root
     *
     * @var string
     */
    protected $contentFile;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $filename;

    /**
     * All registered file methods
     *
     * @var array
     */
    public static $methods = [];

    /**
     * The parent object
     *
     * @var Model
     */
    protected $parent;

    /**
     * The absolute path to the file
     *
     * @var string|null
     */
    protected $root;

    /**
     * @var string
     */
    protected $template;

    /**
     * The public file Url
     *
     * @var string
     */
    protected $url;

    /**
     * Magic caller for file methods
     * and content fields. (in this order)
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

        // asset method proxy
        if (method_exists($this->asset(), $method)) {
            return $this->asset()->$method(...$arguments);
        }

        // file methods
        if ($this->hasMethod($method)) {
            return $this->callMethod($method, $arguments);
        }

        // content fields
        return $this->content()->get($method, $arguments);
    }

    /**
     * Creates a new File object
     *
     * @param array $props
     */
    public function __construct(array $props)
    {
        // properties
        $this->setProperties($props);
    }

    /**
     * Improved var_dump() output
     *
     * @return array
     */
    public function __debuginfo(): array
    {
        return array_merge($this->toArray(), [
            'content'  => $this->content(),
            'siblings' => $this->siblings(),
        ]);
    }

    /**
     * Converts the file object to a string
     * In case of an image, it will create an image tag
     * Otherwise it will return the url
     *
     * @return string
     */
    public function __toString(): string
    {
        if ($this->type() === 'image') {
            return '<img src="' . $this->url() . '" alt="' . $this->alt() . '">';
        }

        return $this->url();
    }

    /**
     * Returns the url to api endpoint
     *
     * @param bool $relative
     * @return string
     */
    public function apiUrl(bool $relative = false): string
    {
        return $this->parent()->apiUrl($relative) . '/files/' . $this->filename();
    }

    /**
     * Returns the Asset object
     *
     * @return Image
     */
    public function asset(): Image
    {
        return $this->asset = $this->asset ?? new Image($this->root(), $this->url());
    }

    /**
     * @return FileBlueprint
     */
    public function blueprint(): FileBlueprint
    {
        if (is_a($this->blueprint, 'Kirby\Cms\FileBlueprint') === true) {
            return $this->blueprint;
        }

        return $this->blueprint = FileBlueprint::factory('files/' . $this->template(), 'files/default', $this);
    }

    /**
     * Returns the parent Files collection
     *
     * @return Files
     */
    public function collection(): Files
    {
        if (is_a($this->collection, 'Kirby\Cms\Files') === true) {
            return $this->collection;
        }

        return $this->collection = $this->parent()->files();
    }

    /**
     * Absolute path to the meta text file
     *
     * @param string $languageCode
     * @return string
     */
    public function contentFile(string $languageCode = null): string
    {
        if ($languageCode !== null) {
            return $this->root() . '.' . $languageCode . '.' . $this->kirby()->contentExtension();
        }

        // use the cached version
        if ($this->contentFile !== null) {
            return $this->contentFile;
        }

        // create from template
        return $this->contentFile = $this->root() . '.' . $this->kirby()->contentExtension();
    }

    /**
     * Store the template in addition to the
     * other content.
     *
     * @return array
     */
    public function contentFileData(): array
    {
        $content = $this->content()->toArray();

        // store main information in the content file
        $content['template'] = $this->template();

        return $content;
    }

    /**
     * Provides a kirbytag or markdown
     * tag for the file, which will be
     * used in the panel, when the file
     * gets dragged onto a textarea
     *
     * @return string
     */
    public function dragText($type = 'kirbytext'): string
    {
        switch ($type) {
            case 'kirbytext':
                if ($this->type() === 'image') {
                    return '(image: ' . $this->id() . ')';
                } else {
                    return '(file: ' . $this->id() . ')';
                }
                // no break
            case 'markdown':
                if ($this->type() === 'image') {
                    return '![' . $this->alt() . '](' . $this->url() . ')';
                } else {
                    return '[' . $this->filename() . '](' . $this->url() . ')';
                }
        }
    }

    /**
     * Checks if the file exists on disk
     *
     * @return boolean
     */
    public function exists(): bool
    {
        return is_file($this->root()) === true;
    }

    /**
     * Returns the filename with extension
     *
     * @return string
     */
    public function filename(): string
    {
        return $this->filename;
    }

    /**
     * Returns the parent Files collection
     *
     * @return Files
     */
    public function files(): Files
    {
        return $this->collection();
    }

    /**
     * Returns the id
     *
     * @return string
     */
    public function id(): string
    {
        if ($this->id !== null) {
            return $this->id;
        }

        if (is_a($this->parent(), 'Kirby\Cms\Page') === true) {
            return $this->id = $this->parent()->id() . '/' . $this->filename();
        }

        return $this->id = $this->filename();
    }

    /**
     * Compares the current object with the given file object
     *
     * @param File $file
     * @return bool
     */
    public function is(File $file): bool
    {
        return $this->id() === $file->id();
    }

    /**
     * Returns the absolute path to the file in the public media folder
     *
     * @return string
     */
    public function mediaRoot(): string
    {
        return $this->parent()->mediaRoot() . '/' . $this->filename();
    }

    /**
     * Returns the absolute Url to the file in the public media folder
     *
     * @return string
     */
    public function mediaUrl(): string
    {
        return $this->parent()->mediaUrl() . '/' . $this->filename();
    }

    /**
     * Alias for the old way of fetching File
     * content. Nowadays `File::content()` should
     * be used instead.
     *
     * @return Content
     */
    public function meta(): Content
    {
        return $this->content();
    }

    /**
     * Returns the parent model.
     * This is normally the parent page
     * or the site object.
     *
     * @return Site|Page
     */
    public function model()
    {
        return $this->parent();
    }

    /**
     * Returns the parent Page object
     *
     * @return Page
     */
    public function page()
    {
        return is_a($this->parent(), 'Kirby\Cms\Page') === true ? $this->parent() : null;
    }

    /**
     * Panel icon definition
     *
     * @return array
     */
    public function panelIcon(): array
    {
        return [
            'type' => 'file',
            'back' => 'black'
        ];
    }

    /**
     * Panel image definition
     *
     * @param string|array|false $settings
     * @param array $thumbSettings
     * @return array
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

        $image = $this->query($settings['query'] ?? null, 'Kirby\Cms\File');

        if ($image === null && $this->type() === 'image') {
            $image = $this;
        }

        if ($image) {
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
     * @param bool $relative
     * @return string
     */
    public function panelUrl(bool $relative = false): string
    {
        return $this->parent()->panelUrl($relative) . '/files/' . $this->filename();
    }

    /**
     * Returns the parent Model object
     *
     * @return Model
     */
    public function parent()
    {
        return $this->parent = $this->parent ?? $this->kirby()->site();
    }

    /**
     * Returns the parent id if a parent exists
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
     * Returns a collection of all parent pages
     *
     * @return Pages
     */
    public function parents(): Pages
    {
        if (is_a($this->parent(), 'Kirby\Cms\Page') === true) {
            return $this->parent()->parents()->prepend($this->parent()->id(), $this->parent());
        }

        return new Pages;
    }

    /**
     * Returns the permissions object for this file
     *
     * @return FilePermissions
     */
    public function permissions()
    {
        return new FilePermissions($this);
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
            'file'  => $this
        ]);

        if ($expect !== null && is_a($result, $expect) !== true) {
            return null;
        }

        return $result;
    }

    /**
     * Returns the absolute root to the file
     *
     * @return string|null
     */
    public function root(): ?string
    {
        return $this->root = $this->root ?? $this->parent()->root() . '/' . $this->filename();
    }

    /**
     * Returns the FileRules class to
     * validate any important action.
     *
     * @return FileRules
     */
    protected function rules()
    {
        return new FileRules();
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
            $this->blueprint = new FileBlueprint($blueprint);
        }

        return $this;
    }

    /**
     * Sets the filename
     *
     * @param string $filename
     * @return self
     */
    protected function setFilename(string $filename): self
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * Sets the parent model object
     *
     * @param Model $parent
     * @return self
     */
    protected function setParent(Model $parent = null): self
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @param string $template
     * @return self
     */
    protected function setTemplate(string $template = null): self
    {
        $this->template = $template;
        return $this;
    }

    /**
     * Sets the url
     *
     * @param string $url
     * @return self
     */
    protected function setUrl(string $url = null): self
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Returns the parent Site object
     *
     * @return Site
     */
    public function site(): Site
    {
        return is_a($this->parent(), 'Kirby\Cms\Site') === true ? $this->parent() : $this->kirby()->site();
    }

    /**
     * Returns the final template
     *
     * @return string|null
     */
    public function template(): ?string
    {
        return $this->template = $this->template ?? $this->content()->get('template')->value();
    }

    /**
     * Extended info for the array export
     * by injecting the information from
     * the asset.
     *
     * @return array
     */
    public function toArray(): array
    {
        return array_merge($this->asset()->toArray(), parent::toArray());
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
            'file'  => $this,
            'site'  => $this->site(),
            'kirby' => $this->kirby()
        ]);
    }

    /**
     * Returns the Url
     *
     * @return string
     */
    public function url(): string
    {
        return $this->url ?? $this->url = $this->kirby()->component('file::url')($this->kirby(), $this, []);
    }
}
