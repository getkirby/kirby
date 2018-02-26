<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Image\Image;
use Kirby\Util\Str;

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
class File extends Model
{

    use HasContent;
    use HasSiblings;
    use HasStore;
    use HasThumbs;

    /**
     * All properties that should be included
     * in File::toArray
     *
     * @var array
     */
    protected static $toArray = [
        'model',
        'url'
    ];

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
     * The parent object
     *
     * @var Model
     */
    protected $parent;

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
        if (method_exists($this->asset(), $method)) {
            return $this->asset()->$method(...$arguments);
        }

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

        if (is_a($this->parent(), Page::class) === true) {
            $this->id = $this->parent()->id() . '/' . $this->filename();
        } else {
            $this->id = $this->filename();
        }

    }

    /**
     * Returns the Asset object
     *
     * @return Image
     */
    public function asset(): Image
    {
        if (is_a($this->asset, Image::class)) {
            return $this->asset;
        }

        return $this->asset = $this->store()->asset();
    }

    /**
     * @return FileBlueprint
     */
    public function blueprint(): FileBlueprint
    {
        if (is_a($this->blueprint, FileBlueprint::class) === true) {
            return $this->blueprint;
        }

        return $this->blueprint = $this->store()->blueprint();
    }

    /**
     * Renames the file without touching the extension
     * The store is used to actually execute this.
     *
     * @param string $name
     * @param bool $sanitize
     * @return self
     */
    public function changeName(string $name, bool $sanitize = true): self
    {
        if ($sanitize === true) {
            $name = Str::slug($name);
        }

        // don't rename if not necessary
        if ($name === $this->name()) {
            return $this;
        }

        $this->rules()->changeName($this, $name);

        return $this->store()->changeName($name);
    }

    /**
     * Returns the parent Files collection
     *
     * @return Files
     */
    public function collection(): Files
    {
        if (is_a($this->collection, Files::class) === true) {
            return $this->collection;
        }

        if ($page = $this->page()) {
            return $this->collection = $this->page()->files();
        }

        if ($site = $this->site()) {
            return $this->collection = $this->site()->files();
        }

        return $this->collection = new Files([$this]);
    }

    /**
     * Creates a new file on disk and returns the
     * File object. The store is used to handle file
     * writing, so it can be replaced by any other
     * way of generating files.
     *
     * @param string $source
     * @param array $props
     * @return self
     */
    public static function create(string $source, array $props): self
    {
        // prefer the filename from the props
        $props['filename'] = $props['filename'] ?? basename($source);

        // create the basic file object
        $file = new static($props);

        // validate the source and file object
        $file->rules()->create($source, $file);

        // store the file
        return $file->store()->create($source, $file);
    }

    protected function defaultStore()
    {
        return FileStoreDefault::class;
    }

    /**
     * Deletes the file. The store is used to
     * manipulate the filesystem or whatever you prefer.
     *
     * @return bool
     */
    public function delete(): bool
    {
        $this->rules()->delete($this);

        return $this->store()->delete();
    }

    public function exists(): bool
    {
        return $this->store()->exists();
    }

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
        return $this->id;
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
        return is_a($this->page(), Page::class) ? $this->page() : $this->site();
    }

    /**
     * Returns the parent Page object
     *
     * @return Page
     */
    public function page()
    {
        return is_a($this->parent(), Page::class) === true ? $this->parent() : null;
    }

    /**
     * Returns the parent Model object
     *
     * @return Model
     */
    public function parent()
    {
        return $this->parent;
    }

    /**
     * Replaces the file. The source must
     * be an absolute path to a file or a Url.
     * The store handles the replacement so it
     * finally decides what it will support as
     * source.
     *
     * @param string $source
     * @return self
     */
    public function replace(string $source): self
    {
        return $this->store()->replace($source);
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
        return is_a($this->parent(), Site::class) === true ? $this->parent() : $this->kirby()->site();
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
     * @param array $options
     * @return self
     */
    public function thumb(array $options = []): self
    {
        $media = $this->kirby()->media();

        try {
            if ($this->page() === null) {
                return $media->create($this->site(), $this, $options);
            }

            return $media->create($this->page(), $this, $options);
        } catch (Exception $e) {
            return $this;
        }
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
    public function url()
    {
        return $this->url ?? $this->url = $this->parent()->mediaUrl() . '/' . $this->filename();
    }

}
