<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Data\Data;
use Kirby\Toolkit\Properties;

/**
 * Each page, file or site can have multiple
 * translated versions of their content,
 * represented by this class
 */
class ContentTranslation
{
    use Properties;

    /**
     * @var string
     */
    protected $code;

    /**
     * @var array
     */
    protected $content;

    /**
     * @var string
     */
    protected $contentFile;

    /**
     * @var Page|Site|File
     */
    protected $parent;

    /**
     * @var string
     */
    protected $slug;

    /**
     * Creates a new translation object
     *
     * @param array $props
     */
    public function __construct(array $props)
    {
        $this->setRequiredProperties($props, ['parent', 'code']);
        $this->setOptionalProperties($props, ['slug', 'content']);
    }

    /**
     * Improve var_dump() output
     *
     * @return array
     */
    public function __debuginfo(): array
    {
        return $this->toArray();
    }

    /**
     * Returns the language code of the
     * translation
     *
     * @return string
     */
    public function code(): string
    {
        return $this->code;
    }

    /**
     * Returns the translation content
     * as plain array
     *
     * @return array
     */
    public function content(): array
    {
        if ($this->content !== null) {
            return $this->content;
        }

        // try to fallback to the content file without language code
        if ($this->exists() === false && $this->isDefault() === true) {
            $file = $this->parent()->contentFile();
        } else {
            $file = $this->contentFile();
        }

        try {
            return $this->content = Data::read($file);
        } catch (Exception $e) {
            return $this->content = [];
        }
    }

    /**
     * Absolute path to the translation content file
     *
     * @return string
     */
    public function contentFile(): string
    {
        return $this->contentFile = $this->parent->contentFile($this->code);
    }

    /**
     * Checks if the translation file exists
     *
     * @return boolean
     */
    public function exists(): bool
    {
        return file_exists($this->contentFile()) === true;
    }

    /**
     * Checks if the this is the default translation
     * of the model
     *
     * @return boolean
     */
    public function isDefault(): bool
    {
        return $this->code() === $this->parent->kirby()->languages()->default()->code();
    }

    /**
     * Returns the parent Page, File or Site object
     *
     * @return Page|File|Site
     */
    public function parent()
    {
        return $this->parent;
    }

    /**
     * @param string $code
     * @return self
     */
    protected function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @param array $content
     * @return self
     */
    protected function setContent(array $content = null): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @param Model $parent
     * @return self
     */
    protected function setParent(Model $parent): self
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @param string $slug
     * @return self
     */
    protected function setSlug(string $slug = null): self
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * Returns the custom translation slug
     *
     * @return string|null
     */
    public function slug(): ?string
    {
        return $this->slug = $this->slug ?? ($this->content()['slug'] ?? null);
    }

    /**
     * Converts the most imporant translation
     * props to an array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'code'    => $this->code(),
            'content' => $this->content(),
            'exists'  => $this->exists(),
            'slug'    => $this->slug(),
        ];
    }
}
