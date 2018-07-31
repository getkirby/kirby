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

        try {
            return $this->content = Data::read($this->contentFile());
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
        return $this->contentFile = $this->contentFile ?? preg_replace('!.txt$!', '.' . $this->code . '.txt', $this->parent->contentFile());
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
}
