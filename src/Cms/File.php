<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
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
class File extends Model
{
    use FileActions;

    use HasContent;
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
        if (is_a($this->blueprint, FileBlueprint::class) === true) {
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
        if (is_a($this->collection, Files::class) === true) {
            return $this->collection;
        }

        return $this->collection = $this->parent()->files();
    }

    /**
     * Returns the content object
     *
     * @return Content
     */
    public function content(): Content
    {
        if (is_a($this->content, Content::class) === true) {
            return $this->content;
        }

        try {
            $data = Data::read($this->contentFile());
        } catch (Throwable $e) {
            $data = [];
        }

        return $this->setContent($data)->content();
    }

    /**
     * Absolute path to the meta text file
     *
     * @return string
     */
    public function contentFile(): string
    {
        return $this->root() . '.txt';
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

        if (is_a($this->parent(), Page::class) === true) {
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
        return is_a($this->parent(), Page::class) === true ? $this->parent() : null;
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
     * Returns a collection of all parent pages
     *
     * @return Pages
     */
    public function parents(): Pages
    {
        if (is_a($this->parent(), Page::class) === true) {
            return $this->parent()->parents()->prepend($this->parent()->id(), $this->parent());
        }

        return new Pages;
    }

    /**
     * Returns the permissions object for this file
     *
     * @return FileBlueprintOptions
     */
    public function permissions(): FileBlueprintOptions
    {
        return $this->blueprint()->options();
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
        return is_a($this->parent(), Site::class) === true ? $this->parent() : $this->kirby()->site();
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
        return $this->url ?? $this->url = $this->kirby()->component('file::url')($this->kirby(), $this);
    }
}
