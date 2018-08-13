<?php

namespace Kirby\Cms;

use Kirby\Exception\DuplicateExceptio;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\PermissionException;
use Kirby\Toolkit\F;

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
     * Creates a new language object
     *
     * @param array $props
     * @return self
     */
    public static function create(array $props): self
    {
        $kirby     = App::instance();
        $languages = $kirby->languages();
        $site      = $kirby->site();

        // make the first language the default language
        if ($languages->count() === 0) {
            $props['default'] = true;
        }

        $language = new static($props);

        if ($language->exists() === true) {
            throw new DuplicateException('The language already exists');
        }

        $language->save();

        if ($languages->count() === 0) {

            $code = $language->code();

            F::move($site->contentFile(), $site->contentFile($code));

            foreach ($kirby->site()->index() as $page) {
                $files = $page->files();

                foreach ($files as $file) {
                    F::move($file->contentFile(), $file->contentFile($code));
                }

                F::move($page->contentFile(), $page->contentFile($code));
            }

        }

        return $language;
    }

    public function delete(): bool
    {
        if ($this->exists() === false) {
            return true;
        }

        $kirby     = App::instance();
        $languages = $kirby->languages();
        $site      = $kirby->site();
        $code      = $this->code();

        if (F::remove($this->root()) !== true) {
            throw new Exception('The language could not be deleted');
        }

        if ($languages->count() === 1) {

            F::move($site->contentFile($code), $site->contentFile());

            foreach ($kirby->site()->index() as $page) {
                $files = $page->files();

                foreach ($files as $file) {
                    F::move($file->contentFile($code), $file->contentFile());
                }

                F::move($page->contentFile($code), $page->contentFile());
            }
        }

        return true;

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
     * Check if the language file exists
     *
     * @return boolean
     */
    public function exists(): bool
    {
        return file_exists($this->root());
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
     * Returns the absolute path to the language file
     *
     * @return string
     */
    public function root(): string
    {
        return App::instance()->root('languages') . '/' . $this->code() . '.php';
    }

    /**
     * Saves the language settings in the languages folder
     *
     * @return self
     */
    public function save(): self
    {
        $data = $this->toArray();

        unset($data['url']);

        $export = '<?php' . PHP_EOL . PHP_EOL . var_export($data, true);
        $export = str_replace('array (', 'return [', $export);
        $export = str_replace(PHP_EOL . ')', PHP_EOL . '];', $export);

        F::write($this->root(), $export);

        return $this;
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

    /**
     * Update language properties and save them
     *
     * @param array $props
     * @return self
     */
    public function update(array $props = null): self
    {
        $updated = $this->clone($props);
        return $updated->save();
    }

}
