<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Data\Data;
use Kirby\Exception\PermissionException;
use Kirby\Toolkit\F;
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
        $parent  = $this->parent();
        $content = $this->content ?? $parent->readContent($this->code());

        // merge with the default content
        if ($this->isDefault() === false && $defaultLanguage = $parent->kirby()->defaultLanguage()) {
            $default = $parent->translation($defaultLanguage->code())->content();
            $content = array_merge($default, $content);
        }

        return $content;
    }

    /**
     * Absolute path to the translation content file
     *
     * @return string
     */
    public function contentFile(): string
    {
        return $this->contentFile = $this->parent->contentFile($this->code, true);
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
     * Returns the translation code as id
     *
     * @return void
     */
    public function id()
    {
        return $this->code();
    }

    /**
     * Checks if the this is the default translation
     * of the model
     *
     * @return boolean
     */
    public function isDefault(): bool
    {
        if ($defaultLanguage = $this->parent->kirby()->defaultLanguage()) {
            return $this->code() === $defaultLanguage->code();
        }

        return false;
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
     * Merge the old and new data
     *
     * @param array|null $data
     * @param bool $overwrite
     * @return self
     */
    public function update(array $data = null, bool $overwrite = false)
    {
        $this->content = $overwrite === true ? (array)$data : array_merge($this->content(), (array)$data);
        return $this;
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
