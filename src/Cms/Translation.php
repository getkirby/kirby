<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Data\Data;
use Kirby\Toolkit\Str;

/**
 * Wrapper around Kirby's localization files,
 * which are stored in `kirby/translations`.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Translation
{
    /**
     * @var string
     */
    protected $code;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param string $code
     * @param array $data
     */
    public function __construct(string $code, array $data)
    {
        $this->code = $code;
        $this->data = $data;
    }

    /**
     * Improved `var_dump` output
     *
     * @return array
     */
    public function __debugInfo(): array
    {
        return $this->toArray();
    }

    /**
     * Returns the translation author
     *
     * @return string
     */
    public function author(): string
    {
        return $this->get('translation.author', 'Kirby');
    }

    /**
     * Returns the official translation code
     *
     * @return string
     */
    public function code(): string
    {
        return $this->code;
    }

    /**
     * Returns an array with all
     * translation strings
     *
     * @return array
     */
    public function data(): array
    {
        return $this->data;
    }

    /**
     * Returns the translation data and merges
     * it with the data from the default translation
     *
     * @return array
     */
    public function dataWithFallback(): array
    {
        if ($this->code === 'en') {
            return $this->data;
        }

        // get the fallback array
        $fallback = App::instance()->translation('en')->data();

        return array_merge($fallback, $this->data);
    }

    /**
     * Returns the writing direction
     * (ltr or rtl)
     *
     * @return string
     */
    public function direction(): string
    {
        return $this->get('translation.direction', 'ltr');
    }

    /**
     * Returns a single translation
     * string by key
     *
     * @param string $key
     * @param string|null $default
     * @return string|null
     */
    public function get(string $key, string $default = null): ?string
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Returns the translation id,
     * which is also the code
     *
     * @return string
     */
    public function id(): string
    {
        return $this->code;
    }

    /**
     * Loads the translation from the
     * json file in Kirby's translations folder
     *
     * @param string $code
     * @param string $root
     * @param array $inject
     * @return static
     */
    public static function load(string $code, string $root, array $inject = [])
    {
        try {
            $data = array_merge(Data::read($root), $inject);
        } catch (Exception $e) {
            $data = [];
        }

        return new static($code, $data);
    }

    /**
     * Returns the PHP locale of the translation
     *
     * @return string
     */
    public function locale(): string
    {
        $default = $this->code;
        if (Str::contains($default, '_') !== true) {
            $default .= '_' . strtoupper($this->code);
        }

        return $this->get('translation.locale', $default);
    }

    /**
     * Returns the human-readable translation name.
     *
     * @return string
     */
    public function name(): string
    {
        return $this->get('translation.name', $this->code);
    }

    /**
     * Converts the most important
     * properties to an array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'code'   => $this->code(),
            'data'   => $this->data(),
            'name'   => $this->name(),
            'author' => $this->author(),
        ];
    }
}
