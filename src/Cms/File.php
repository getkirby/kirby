<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Image\Image;
use Kirby\Toolkit\A;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Str;
use Throwable;

/**
 * The `$file` object provides a set
 * of methods that can be used when
 * dealing with a single image or
 * other media file, like getting the
 * URL or resizing an image. It also
 * handles file meta data.
 *
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
    const CLASS_ALIAS = 'file';

    use FileActions;
    use FileFoundation;
    use FileModifications;
    use HasMethods;
    use HasSiblings;

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
     * Returns the url to api endpoint
     *
     * @internal
     * @param bool $relative
     * @return string
     */
    public function apiUrl(bool $relative = false): string
    {
        return $this->parent()->apiUrl($relative) . '/files/' . $this->filename();
    }

    /**
     * Returns the Image object
     *
     * @internal
     * @return Image
     */
    public function asset(): Image
    {
        return $this->asset = $this->asset ?? new Image($this->root());
    }

    /**
     * Returns the FileBlueprint object for the file
     *
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
     * Store the template in addition to the
     * other content.

     * @internal
     * @param array $data
     * @param string|null $languageCode
     * @return array
     */
    public function contentFileData(array $data, string $languageCode = null): array
    {
        return A::append($data, [
            'template' => $this->template(),
        ]);
    }

    /**
     * Returns the directory in which
     * the content file is located
     *
     * @internal
     * @return string
     */
    public function contentFileDirectory(): string
    {
        return dirname($this->root());
    }

    /**
     * Filename for the content file
     *
     * @internal
     * @return string
     */
    public function contentFileName(): string
    {
        return $this->filename();
    }

    /**
     * Provides a kirbytag or markdown
     * tag for the file, which will be
     * used in the panel, when the file
     * gets dragged onto a textarea
     *
     * @internal
     * @param string $type
     * @param bool $absolute
     * @return string
     */
    public function dragText($type = 'kirbytext', bool $absolute = false): string
    {
        $url = $absolute ? $this->id() : $this->filename();

        switch ($type) {
            case 'kirbytext':
                if ($this->type() === 'image') {
                    return '(image: ' . $url . ')';
                } else {
                    return '(file: ' . $url . ')';
                }
                // no break
            case 'markdown':
                if ($this->type() === 'image') {
                    return '![' . $this->alt() . '](' . $url . ')';
                } else {
                    return '[' . $this->filename() . '](' . $url . ')';
                }
        }
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
        return $this->siblingsCollection();
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
        } elseif (is_a($this->parent(), 'Kirby\Cms\User') === true) {
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
     * Create a unique media hash
     *
     * @internal
     * @return string
     */
    public function mediaHash(): string
    {
        return crc32($this->filename()) . '-' . $this->modified();
    }

    /**
     * Returns the absolute path to the file in the public media folder
     *
     * @internal
     * @return string
     */
    public function mediaRoot(): string
    {
        return $this->parent()->mediaRoot() . '/' . $this->mediaHash() . '/' . $this->filename();
    }

    /**
     * Returns the absolute Url to the file in the public media folder
     *
     * @internal
     * @return string
     */
    public function mediaUrl(): string
    {
        return $this->parent()->mediaUrl() . '/' . $this->mediaHash() . '/' . $this->filename();
    }

    /**
     * @deprecated 3.0.0 Use `File::content()` instead
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
     * @internal
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
     * @internal
     * @param array $params
     * @return array
     */
    public function panelIcon(array $params = null): array
    {
        $colorBlue   = '#81a2be';
        $colorPurple = '#b294bb';
        $colorOrange = '#de935f';
        $colorGreen  = '#a7bd68';
        $colorAqua   = '#8abeb7';
        $colorYellow = '#f0c674';
        $colorRed    = '#d16464';
        $colorWhite  = '#c5c9c6';

        $types = [
            'image'    => ['color' => $colorOrange, 'type' => 'file-image'],
            'video'    => ['color' => $colorYellow, 'type' => 'file-video'],
            'document' => ['color' => $colorRed, 'type' => 'file-document'],
            'audio'    => ['color' => $colorAqua, 'type' => 'file-audio'],
            'code'     => ['color' => $colorBlue, 'type' => 'file-code'],
            'archive'  => ['color' => $colorWhite, 'type' => 'file-zip'],
        ];

        $extensions = [
            'indd'  => ['color' => $colorPurple],
            'xls'   => ['color' => $colorGreen, 'type' => 'file-spreadsheet'],
            'xlsx'  => ['color' => $colorGreen, 'type' => 'file-spreadsheet'],
            'csv'   => ['color' => $colorGreen, 'type' => 'file-spreadsheet'],
            'docx'  => ['color' => $colorBlue, 'type' => 'file-word'],
            'doc'   => ['color' => $colorBlue, 'type' => 'file-word'],
            'rtf'   => ['color' => $colorBlue, 'type' => 'file-word'],
            'mdown' => ['type' => 'file-text'],
            'md'    => ['type' => 'file-text']
        ];

        $definition = array_merge($types[$this->type()] ?? [], $extensions[$this->extension()] ?? []);

        $settings = [
            'type'  => $definition['type'] ?? 'file',
            'back'  => 'pattern',
            'color' => $definition['color'] ?? $colorWhite,
            'ratio' => $params['ratio'] ?? null,
        ];

        return $settings;
    }

    /**
     * Panel image definition
     *
     * @internal
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

        if ($image === null && $this->isViewable() === true) {
            $image = $this;
        }

        if ($image) {
            $settings['url'] = $image->thumb($thumbSettings)->url(true);
            unset($settings['query']);
        }

        return array_merge($defaults, (array)$settings);
    }

    /**
     * Returns the full path without leading slash
     *
     * @internal
     * @return string
     */
    public function panelPath(): string
    {
        return 'files/' . $this->filename();
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
        return $this->parent()->panelUrl($relative) . '/' . $this->panelPath();
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
     * @internal
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
     * Always set the root to null, to invoke
     * auto root detection
     *
     * @param string|null $root
     * @return self
     */
    protected function setRoot(string $root = null)
    {
        $this->root = null;
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
     * Returns the parent Files collection
     * @internal
     *
     * @return Files
     */
    protected function siblingsCollection()
    {
        return $this->parent()->files();
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
     * Returns siblings with the same template
     *
     * @param bool $self
     * @return self
     */
    public function templateSiblings(bool $self = true)
    {
        return $this->siblings($self)->filterBy('template', $this->template());
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
