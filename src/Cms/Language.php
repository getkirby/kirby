<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
use Kirby\Exception\DuplicateException;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\PermissionException;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Str;
use Throwable;

/**
 * The `$language` object represents
 * a single language in a multi-language
 * Kirby setup. You can, for example,
 * use the methods of this class to get
 * the name or locale of a language,
 * check for the default language,
 * get translation strings and many
 * more things.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
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
     * @var array
     */
    protected $locale;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array|null
     */
    protected $translations;

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
            'translations',
            'url',
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
     * Internal converter to create or remove
     * translation files.
     *
     * @param string $from
     * @param string $to
     * @return boolean
     */
    protected static function converter(string $from, string $to): bool
    {
        $kirby = App::instance();
        $site  = $kirby->site();

        F::move($site->contentFile($from, true), $site->contentFile($to, true));

        // convert all pages
        foreach ($kirby->site()->index(true) as $page) {
            foreach ($page->files() as $file) {
                F::move($file->contentFile($from, true), $file->contentFile($to, true));
            }

            F::move($page->contentFile($from, true), $page->contentFile($to, true));
        }

        // convert all users
        foreach ($kirby->users() as $user) {
            foreach ($user->files() as $file) {
                F::move($file->contentFile($from, true), $file->contentFile($to, true));
            }

            F::move($user->contentFile($from, true), $user->contentFile($to, true));
        }

        return true;
    }

    /**
     * Creates a new language object
     *
     * @internal
     * @param array $props
     * @return self
     */
    public static function create(array $props): self
    {
        $props['code'] = Str::slug($props['code'] ?? null);
        $kirby         = App::instance();
        $languages     = $kirby->languages();
        $site          = $kirby->site();

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
            static::converter('', $language->code());
        }

        return $language;
    }

    /**
     * Delete the current language and
     * all its translation files
     *
     * @internal
     * @return boolean
     */
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
            return $this->converter($code, '');
        } else {
            return $this->deleteContentFiles($code);
        }
    }

    /**
     * When the language is deleted, all content files with
     * the language code must be removed as well.
     *
     * @return bool
     */
    protected function deleteContentFiles($code): bool
    {
        $kirby = App::instance();
        $site  = $kirby->site();

        F::remove($site->contentFile($code, true));

        foreach ($kirby->site()->index(true) as $page) {
            foreach ($page->files() as $file) {
                F::remove($file->contentFile($code, true));
            }

            F::remove($page->contentFile($code, true));
        }

        foreach ($kirby->users() as $user) {
            foreach ($user->files() as $file) {
                F::remove($file->contentFile($code, true));
            }

            F::remove($user->contentFile($code, true));
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
     * The id is required for collections
     * to work properly. The code is used as id
     *
     * @return string
     */
    public function id(): string
    {
        return $this->code;
    }

    /**
     * Returns the PHP locale setting array
     *
     * @param int $category If passed, returns the locale for the specified category (e.g. LC_ALL) as string
     * @return array|string
     */
    public function locale(int $category = null)
    {
        if ($category !== null) {
            return $this->locale[$category] ?? $this->locale[LC_ALL] ?? null;
        } else {
            return $this->locale;
        }
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
        if (empty($this->url) === true) {
            return $this->code;
        }

        return trim($this->url, '/');
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
     * @internal
     * @return self
     */
    public function save(): self
    {
        try {
            $existingData = Data::read($this->root());
        } catch (Throwable $e) {
            $existingData = [];
        }

        $props = [
            'code'         => $this->code(),
            'default'      => $this->isDefault(),
            'direction'    => $this->direction(),
            'locale'       => $this->locale(),
            'name'         => $this->name(),
            'translations' => $this->translations(),
            'url'          => $this->url,
        ];

        $data = array_merge($existingData, $props);

        ksort($data);

        Data::write($this->root(), $data);

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
     * @param string|array $locale
     * @return self
     */
    protected function setLocale($locale = null): self
    {
        if (is_array($locale)) {
            $this->locale = $locale;
        } elseif (is_string($locale)) {
            $this->locale = [LC_ALL => $locale];
        } elseif ($locale === null) {
            $this->locale = [LC_ALL => $this->code];
        } else {
            throw new InvalidArgumentException('Locale must be string or array');
        }

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
     * @param array $translations
     * @return self
     */
    protected function setTranslations(array $translations = null): self
    {
        $this->translations = $translations ?? [];
        return $this;
    }

    /**
     * @param string $url
     * @return self
     */
    protected function setUrl(string $url = null): self
    {
        $this->url = $url;
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
     * Returns the translation strings for this language
     *
     * @return array
     */
    public function translations(): array
    {
        return $this->translations;
    }

    /**
     * Returns the absolute Url for the language
     *
     * @return string
     */
    public function url(): string
    {
        return Url::to($this->pattern());
    }

    /**
     * Update language properties and save them
     *
     * @internal
     * @param array $props
     * @return self
     */
    public function update(array $props = null): self
    {
        $props['slug'] = Str::slug($props['slug'] ?? null);
        $kirby         = App::instance();
        $updated       = $this->clone($props);

        // convert the current default to a non-default language
        if ($updated->isDefault() === true) {
            if ($oldDefault = $kirby->defaultLanguage()) {
                $oldDefault->clone(['default' => false])->save();
            }

            $code = $this->code();
            $site = $kirby->site();

            touch($site->contentFile($code));

            foreach ($kirby->site()->index(true) as $page) {
                $files = $page->files();

                foreach ($files as $file) {
                    touch($file->contentFile($code));
                }

                touch($page->contentFile($code));
            }
        } elseif ($this->isDefault() === true) {
            throw new PermissionException('Please select another language to be the primary language');
        }

        return $updated->save();
    }
}
