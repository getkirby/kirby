<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;

/**
 * Represents a content language
 * in a multi-language setup
 */
class Language extends Model
{

    /**
     * @var string
     */
    protected $code;

    /**
     * @var bool
     */
    protected $default;

    /**
     * @var string
     */
    protected $direction;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $url;

    /**
     * Creates a new language object
     *
     * @param array $props
     */
    public function __construct(array $props)
    {
        $this->setRequiredProperties($props, [
            'code'
        ]);

        $this->setOptionalProperties($props, [
            'default',
            'direction',
            'locale',
            'name',
            'url'
        ]);
    }

    /**
     * Improved var_dump output
     *
     * @return array
     */
    public function __debuginfo(): array
    {
        return $this->toArray();
    }

    /**
     * Returns the language code
     * when the language is converted to a string
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->code();
    }

    /**
     * Returns the language code/id.
     * The language code is used in
     * text file names as appendix.
     *
     * @return string
     */
    public function code(): string
    {
        return $this->code;
    }

    /**
     * Reading direction of this language
     *
     * @return string
     */
    public function direction(): string
    {
        return $this->direction;
    }

    /**
     * Checks if this is the default language
     * for the site.
     *
     * @return boolean
     */
    public function isDefault(): bool
    {
        return $this->default;
    }

    /**
     * Returns the PHP locale setting string
     *
     * @return string
     */
    public function locale(): string
    {
        return $this->locale;
    }

    /**
     * Returns the human-readable name
     * of the language
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Returns the routing pattern for the language
     *
     * @return string
     */
    public function pattern(): string
    {
        return $this->url;
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
     * @param boolean $default
     * @return self
     */
    protected function setDefault(bool $default = false): self
    {
        $this->default = $default;
        return $this;
    }

    /**
     * @param string $direction
     * @return self
     */
    protected function setDirection(string $direction = 'ltr'): self
    {
        $this->direction = $direction === 'rtl' ? 'rtl' : 'ltr';
        return $this;
    }

    /**
     * @param string $locale
     * @return self
     */
    protected function setLocale(string $locale = null): self
    {
        $this->locale = $locale ?? $this->code;
        return $this;
    }

    /**
     * @param string $name
     * @return self
     */
    protected function setName(string $name = null): self
    {
        $this->name = $name ?? $this->code;
        return $this;
    }

    /**
     * @param string $url
     * @return self
     */
    protected function setUrl(string $url = null): self
    {
        $this->url = $url !== null ? trim($url, '/') : $this->code;
        return $this;
    }

    /**
     * Returns the most important
     * properties as array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'code'      => $this->code(),
            'default'   => $this->isDefault(),
            'direction' => $this->direction(),
            'locale'    => $this->locale(),
            'name'      => $this->name(),
            'url'       => $this->url()
        ];
    }

    /**
     * Returns the absolute Url for the language
     *
     * @return string
     */
    public function url(): string
    {
        return Url::to($this->url);
    }
}
