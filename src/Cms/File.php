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
     * Blurs the image by the given amount of pixels
     *
     * @param boolean $pixels
     * @return self
     */
    public function blur($pixels = true)
    {
        return $this->thumb(['blur' => $pixels]);
    }

    /**
     * Converts the image to black and white
     *
     * @return self
     */
    public function bw()
    {
        return $this->thumb(['grayscale' => true]);
    }

    /**
     * Store the template in addition to the
     * other content.
     *
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
     * @return string
     */
    public function contentFileDirectory(): string
    {
        return dirname($this->root());
    }

    /**
     * Filename for the content file
     *
     * @return string
     */
    public function contentFileName(): string
    {
        return $this->filename();
    }

    /**
     * Crops the image by the given width and height
     *
     * @param integer $width
     * @param integer $height
     * @param string|array $options
     * @return self
     */
    public function crop(int $width, int $height = null, $options = null)
    {
        $quality = null;
        $crop    = 'center';

        if (is_int($options) === true) {
            $quality = $options;
        } elseif (is_string($options)) {
            $crop = $options;
        } elseif (is_a($options, 'Kirby\Cms\Field') === true) {
            $crop = $options->value();
        } elseif (is_array($options)) {
            $quality = $options['quality'] ?? $quality;
            $crop    = $options['crop']    ?? $crop;
        }

        return $this->thumb([
            'width'   => $width,
            'height'  => $height,
            'quality' => $quality,
            'crop'    => $crop
        ]);
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
                    return '(image: ' . $this->filename() . ')';
                } else {
                    return '(file: ' . $this->filename() . ')';
                }
                // no break
            case 'markdown':
                if ($this->type() === 'image') {
                    return '![' . $this->alt() . '](./' . $this->filename() . ')';
                } else {
                    return '[' . $this->filename() . '](./' . $this->filename() . ')';
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
        return $this->siblingsCollection();
    }

    /**
     * Converts the file to html
     *
     * @param  array  $attr
     * @return string
     */
    public function html(array $attr = []): string
    {
        if ($this->type() === 'image') {
            return Html::img($this->url(), array_merge(['alt' => $this->alt()], $attr));
        } else {
            return Html::a($this->url(), $attr);
        }
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
     * @return string
     */
    public function mediaHash(): string
    {
        return crc32($this->filename()) . '-' . $this->modified();
    }

    /**
     * Returns the absolute path to the file in the public media folder
     *
     * @return string
     */
    public function mediaRoot(): string
    {
        return $this->parent()->mediaRoot() . '/' . $this->mediaHash() . '/' . $this->filename();
    }

    /**
     * Returns the absolute Url to the file in the public media folder
     *
     * @return string
     */
    public function mediaUrl(): string
    {
        return $this->parent()->mediaUrl() . '/' . $this->mediaHash() . '/' . $this->filename();
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
     * Get the file's last modification time.
     *
     * @param  string $format
     * @param  string|null $handler date or strftime
     * @return mixed
     */
    public function modified(string $format = null, string $handler = null)
    {
        return F::modified($this->root(), $format, $handler ?? $this->kirby()->option('date.handler', 'date'));
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
     * Sets the JPEG compression quality
     *
     * @param integer $quality
     * @return self
     */
    public function quality(int $quality)
    {
        return $this->thumb(['quality' => $quality]);
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
     * Resizes the file with the given width and height
     * while keeping the aspect ratio.
     *
     * @param integer $width
     * @param integer $height
     * @param integer $quality
     * @return self
     */
    public function resize(int $width = null, int $height = null, int $quality = null)
    {
        return $this->thumb([
            'width'   => $width,
            'height'  => $height,
            'quality' => $quality
        ]);
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
     * Creates a modified version of images
     * The media manager takes care of generating
     * those modified versions and putting them
     * in the right place. This is normally the
     * /media folder of your installation, but
     * could potentially also be a CDN or any other
     * place.
     *
     * @param array|null $options
     * @return FileVersion|File
     */
    public function thumb(array $options = null)
    {
        if (empty($options) === true) {
            return $this;
        }

        $result = $this->kirby()->component('file::version')($this->kirby(), $this, $options);

        if (is_a($result, FileVersion::class) === false && is_a($result, File::class) === false) {
            throw new InvalidArgumentException('The file::version component must return a File or FileVersion object');
        }

        return $result;
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
