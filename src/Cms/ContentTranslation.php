<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Properties;

/**
 * Each page, file or site can have multiple
 * translated versions of their content,
 * represented by this class
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
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
     * @var Model
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
     * Improve `var_dump` output
     *
     * @return array
     */
    public function __debugInfo(): array
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
        $parent = $this->parent();

        if ($this->content === null) {
            $this->content = $parent->readContent($this->code());
        }

        $content = $this->content;

        // merge with the default content
        if ($this->isDefault() === false && $defaultLanguage = $parent->kirby()->defaultLanguage()) {
            $default = [];

            if ($defaultTranslation = $parent->translation($defaultLanguage->code())) {
                $default = $defaultTranslation->content();
            }

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
     * @return bool
     */
    public function exists(): bool
    {
        return file_exists($this->contentFile()) === true;
    }

    /**
     * Returns the translation code as id
     *
     * @return string
     */
    public function id(): string
    {
        return $this->code();
    }

    /**
     * Checks if the this is the default translation
     * of the model
     *
     * @return bool
     */
    public function isDefault(): bool
    {
        if ($defaultLanguage = $this->parent->kirby()->defaultLanguage()) {
            return $this->code() === $defaultLanguage->code();
        }

        return false;
    }

    /**
     * Returns the parent page, file or site object
     *
     * @return \Kirby\Cms\Model
     */
    public function parent()
    {
        return $this->parent;
    }

    /**
     * @param string $code
     * @return $this
     */
    protected function setCode(string $code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @param array|null $content
     * @return $this
     */
    protected function setContent(array $content = null)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @param \Kirby\Cms\Model $parent
     * @return $this
     */
    protected function setParent(Model $parent)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @param string|null $slug
     * @return $this
     */
    protected function setSlug(string $slug = null)
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
        return $this->slug ??= ($this->content()['slug'] ?? null);
    }

    /**
     * Merge the old and new data
     *
     * @param array|null $data
     * @param bool $overwrite
     * @return $this
     */
    public function update(array $data = null, bool $overwrite = false)
    {
        $this->content = $overwrite === true ? (array)$data : array_merge($this->content(), (array)$data);
        return $this;
    }

    /**
     * Converts the most important translation
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
