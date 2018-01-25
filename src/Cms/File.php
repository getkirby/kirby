<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Image\Image;

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
    use HasThumbs;

    /**
     * All properties that should be included
     * in File::toArray
     *
     * @var array
     */
    protected static $toArray = [
        'id',
        'model',
        'root',
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
     * The File id
     *
     * @var string
     */
    protected $id;

    /**
     * The original File object
     * after File manipulations
     *
     * @var File
     */
    protected $original;

    /**
     * The parent object
     *
     * @var Model
     */
    protected $parent;

    /**
     * The path to the file
     *
     * @var string
     */
    protected $root;

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

        return parent::__call($method, $arguments);
    }

    /**
     * Creates a new File object
     *
     * @param array $props
     */
    public function __construct(array $props)
    {
        // properties
        $this->setRequiredProperties($props, ['id', 'root', 'url']);
        $this->setOptionalProperties($props, ['asset', 'content', 'original', 'parent']);
    }

    /**
     * Clones a File object and throws out
     * all stuff that is too specific to provide
     * a clean slate. You can pass additional
     * props that will be merged with the existing
     * props with the `$props` argument.
     *
     * @param array $props
     * @return self
     */
    public function clone(array $props = []): self
    {
        return new static(array_merge([
            'id'     => $this->id(),
            'root'   => $this->root(),
            'url'    => $this->url(),
            'parent' => $this->parent()
        ], $props));
    }

    /**
     * Creates a new file on disk and returns the
     * File object. The store is used to handle file
     * writing, so it can be replaced by any other
     * way of generating files.
     *
     * @param Page $parent
     * @param string $source
     * @param string $filename
     * @param array $content
     * @return self
     */
    public static function create(Page $parent = null, string $source, string $filename, array $content = []): self
    {
        throw new Exception('not yet implemented');
    }

    /**
     * Deletes the file. The store is used to
     * manipulate the filesystem or whatever you prefer.
     *
     * @return bool
     */
    public function delete(): bool
    {
        $this->rules()->check('file.delete', $this);
        $this->perms()->check('file.delete', $this);

        return $this->store()->delete();
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

        return $this->asset = new Image($this->root(), $this->url());
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
     * Returns the file's Content object
     *
     * @return Content
     */
    public function content(): Content
    {
        if (is_a($this->content, Content::class)) {
            return $this->content;
        }

        return $this->content = $this->store()->content();
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
     * Returns the original File object
     *
     * @return File
     */
    public function original()
    {
        return $this->original;
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
     * Renames the file without touching the extension
     * The store is used to actually execute this.
     *
     * @param string $name
     * @return self
     */
    public function rename(string $name): self
    {
        $this->rules()->check('file.rename', $this, $name);
        $this->perms()->check('file.rename', $this, $name);

        return $this->store()->rename($name);
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
     * Returns the root
     *
     * @return string
     */
    public function root(): string
    {
        return $this->root;
    }

    /**
     * Sets the asset object
     *
     * @param Image $asset
     * @return self
     */
    protected function setAsset(Image $asset = null): self
    {
        $this->asset = $asset;
        return $this;
    }

    /**
     * Sets the id
     *
     * @param string $id
     * @return self
     */
    protected function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Sets the original File object
     * after a File has been modified
     *
     * @param File $original
     * @return self
     */
    protected function setOriginal(File $original = null): self
    {
        $this->original = $original;
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
     * Sets the root
     *
     * @param string $root
     * @return self
     */
    protected function setRoot(string $root): self
    {
        $this->root = $root;
        return $this;
    }

    /**
     * Sets the url
     *
     * @param string $url
     * @return self
     */
    protected function setUrl(string $url): self
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
        return is_a($this->parent(), Site::class) === true ? $this->parent() : App::instance()->site();
    }

    /**
     * @return FileStore
     */
    protected function store(): FileStore
    {
        return App::instance()->component('FileStore', $this);
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
        $media = App::instance()->media();

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
     * Updates the file content.
     * The store writes content into text
     * files or any other place you determin in
     * custom store implementations.
     *
     * @param array $content
     * @return self
     */
    public function update(array $content = []): self
    {
        $this->rules()->check('file.update', $this, $content);
        $this->perms()->check('file.update', $this, $content);

        return $this->store()->update($content);
    }

    /**
     * Returns the Url
     *
     * @return string
     */
    public function url(): string
    {
        return $this->url;
    }

}
