<?php

namespace Kirby\Cms;

use Kirby\File\HasFile;
use Kirby\Toolkit\A;
use Kirby\Toolkit\F;
use Throwable;

/**
 * The `$file` object provides a set
 * of methods that can be used when
 * dealing with a single image or
 * other media file, like getting the
 * URL or resizing an image. It also
 * handles file meta data.
 *
 * The File class proxies the `Kirby\File\File`
 * or `Kirby\File\Image` class, which
 * is used to handle all asset file methods.
 * In addition the File class handles
 * meta data via `Kirby\Cms\Content`.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class File extends ModelWithContent
{
    const CLASS_ALIAS = 'file';

    use HasFile;
    use FileActions;
    use FileModifications;
    use HasMethods;
    use HasSiblings;

    /**
     * Cache for the initialized blueprint object
     *
     * @var \Kirby\Cms\FileBlueprint
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
     * @var \Kirby\Cms\Model
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
        // Public property access
        if (isset($this->$method) === true) {
            return $this->$method;
        }

        // Asset method proxy
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
     * Improved `var_dump` output
     *
     * @return array
     */
    public function __debugInfo(): array
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
     * Returns the FileBlueprint object for the file
     *
     * @return \Kirby\Cms\FileBlueprint
     */
    public function blueprint()
    {
        if (is_a($this->blueprint, 'Kirby\Cms\FileBlueprint') === true) {
            return $this->blueprint;
        }

        return $this->blueprint = FileBlueprint::factory('files/' . $this->template(), 'files/default', $this);
    }

    /**
     * Store the template in addition to the
     * other content.
     *
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
     * @param string|null $type (null|auto|kirbytext|markdown)
     * @param bool $absolute
     * @return string
     */
    public function dragText(string $type = null, bool $absolute = false): string
    {
        $type = $this->dragTextType($type);
        $url  = $absolute ? $this->id() : $this->filename();

        if ($dragTextFromCallback = $this->dragTextFromCallback($type, $url)) {
            return $dragTextFromCallback;
        }

        if ($type === 'markdown') {
            if ($this->type() === 'image') {
                return '![' . $this->alt() . '](' . $url . ')';
            } else {
                return '[' . $this->filename() . '](' . $url . ')';
            }
        } else {
            if ($this->type() === 'image') {
                return '(image: ' . $url . ')';
            } else {
                return '(file: ' . $url . ')';
            }
        }
    }

    /**
     * Constructs a File object
     *
     * @internal
     * @param mixed $props
     * @return static
     */
    public static function factory($props)
    {
        return new static($props);
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
     * @return \Kirby\Cms\Files
     */
    public function files()
    {
        return $this->siblingsCollection();
    }

    /**
     * Converts the file to html
     *
     * @param array $attr
     * @return string
     */
    public function html(array $attr = []): string
    {
        return $this->asset()->html(array_merge(
            ['alt' => $this->alt()],
            $attr
        ));
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
     * @param \Kirby\Cms\File $file
     * @return bool
     */
    public function is(File $file): bool
    {
        return $this->id() === $file->id();
    }

    /**
     * Check if the file can be read by the current user
     *
     * @return bool
     */
    public function isReadable(): bool
    {
        static $readable = [];

        $template = $this->template();

        if (isset($readable[$template]) === true) {
            return $readable[$template];
        }

        return $readable[$template] = $this->permissions()->can('read');
    }

    /**
     * Creates a unique media hash
     *
     * @internal
     * @return string
     */
    public function mediaHash(): string
    {
        return $this->mediaToken() . '-' . $this->modifiedFile();
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
     * Creates a non-guessable token string for this file
     *
     * @internal
     * @return string
     */
    public function mediaToken(): string
    {
        $token = $this->kirby()->contentToken($this, $this->id());
        return substr($token, 0, 10);
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
     * Get the file's last modification time.
     *
     * @param string|null $format
     * @param string|null $handler date or strftime
     * @param string|null $languageCode
     * @return mixed
     */
    public function modified(string $format = null, string $handler = null, string $languageCode = null)
    {
        $file     = $this->modifiedFile();
        $content  = $this->modifiedContent($languageCode);
        $modified = max($file, $content);

        if (is_null($format) === true) {
            return $modified;
        }

        $handler = $handler ?? $this->kirby()->option('date.handler', 'date');

        return $handler($format, $modified);
    }

    /**
     * Timestamp of the last modification
     * of the content file
     *
     * @param string|null $languageCode
     * @return int
     */
    protected function modifiedContent(string $languageCode = null): int
    {
        return F::modified($this->contentFile($languageCode));
    }

    /**
     * Timestamp of the last modification
     * of the source file
     *
     * @return int
     */
    protected function modifiedFile(): int
    {
        return F::modified($this->root());
    }

    /**
     * Returns the parent Page object
     *
     * @return \Kirby\Cms\Page|null
     */
    public function page()
    {
        return is_a($this->parent(), 'Kirby\Cms\Page') === true ? $this->parent() : null;
    }

    /**
     * Panel icon definition
     *
     * @internal
     * @param array|null $params
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

        $params['type']  = $definition['type']  ?? 'file';
        $params['color'] = $definition['color'] ?? $colorWhite;

        return parent::panelIcon($params);
    }

    /**
     * Returns the image file object based on provided query
     *
     * @internal
     * @param string|null $query
     * @return \Kirby\Cms\File|\Kirby\File\Asset|null
     */
    protected function panelImageSource(string $query = null)
    {
        if ($query === null && $this->isViewable()) {
            return $this;
        }

        return parent::panelImageSource($query);
    }

    /**
     * Returns an array of all actions
     * that can be performed in the Panel
     *
     * @since 3.3.0 This also checks for the lock status
     * @since 3.5.1 This also checks for matching accept settings
     *
     * @param array $unlock An array of options that will be force-unlocked
     * @return array
     */
    public function panelOptions(array $unlock = []): array
    {
        $options = parent::panelOptions($unlock);

        try {
            // check if the file type is allowed at all,
            // otherwise it cannot be replaced
            $this->match($this->blueprint()->accept());
        } catch (Throwable $e) {
            $options['replace'] = false;
        }

        return $options;
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
     * Prepares the response data for file pickers
     * and file fields
     *
     * @param array|null $params
     * @return array
     */
    public function panelPickerData(array $params = []): array
    {
        $image = $this->panelImage($params['image'] ?? []);
        $icon  = $this->panelIcon($image);
        $uuid  = $this->id();

        if (empty($params['model']) === false) {
            $uuid = $this->parent() === $params['model'] ? $this->filename() : $this->id();
            $absolute = $this->parent() !== $params['model'];
        }

        return [
            'filename' => $this->filename(),
            'dragText' => $this->dragText('auto', $absolute ?? false),
            'icon'     => $icon,
            'id'       => $this->id(),
            'image'    => $image,
            'info'     => $this->toString($params['info'] ?? false),
            'link'     => $this->panelUrl(true),
            'text'     => $this->toString($params['text'] ?? '{{ file.filename }}'),
            'type'     => $this->type(),
            'url'      => $this->url(),
            'uuid'     => $uuid,
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
        return $this->parent()->panelUrl($relative) . '/' . $this->panelPath();
    }

    /**
     * Returns the parent Model object
     *
     * @return \Kirby\Cms\Model
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
     * @return \Kirby\Cms\Pages
     */
    public function parents()
    {
        if (is_a($this->parent(), 'Kirby\Cms\Page') === true) {
            return $this->parent()->parents()->prepend($this->parent()->id(), $this->parent());
        }

        return new Pages();
    }

    /**
     * Returns the permissions object for this file
     *
     * @return \Kirby\Cms\FilePermissions
     */
    public function permissions()
    {
        return new FilePermissions($this);
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
     * @return \Kirby\Cms\FileRules
     */
    protected function rules()
    {
        return new FileRules();
    }

    /**
     * Sets the Blueprint object
     *
     * @param array|null $blueprint
     * @return $this
     */
    protected function setBlueprint(array $blueprint = null)
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
     * @return $this
     */
    protected function setFilename(string $filename)
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * Sets the parent model object
     *
     * @param \Kirby\Cms\Model|null $parent
     * @return $this
     */
    protected function setParent(Model $parent = null)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Always set the root to null, to invoke
     * auto root detection
     *
     * @param string|null $root
     * @return $this
     */
    protected function setRoot(string $root = null)
    {
        $this->root = null;
        return $this;
    }

    /**
     * @param string|null $template
     * @return $this
     */
    protected function setTemplate(string $template = null)
    {
        $this->template = $template;
        return $this;
    }

    /**
     * Sets the url
     *
     * @param string|null $url
     * @return $this
     */
    protected function setUrl(string $url = null)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Returns the parent Files collection
     * @internal
     *
     * @return \Kirby\Cms\Files
     */
    protected function siblingsCollection()
    {
        return $this->parent()->files();
    }

    /**
     * Returns the parent Site object
     *
     * @return \Kirby\Cms\Site
     */
    public function site()
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
     * @return \Kirby\Cms\Files
     */
    public function templateSiblings(bool $self = true)
    {
        return $this->siblings($self)->filter('template', $this->template());
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
     * Returns the Url
     *
     * @return string
     */
    public function url(): string
    {
        return $this->url ?? $this->url = ($this->kirby()->component('file::url'))($this->kirby(), $this);
    }
}
