<?php

namespace Kirby\Http\Acceptance;

use Kirby\Http\Acceptance;
use Kirby\Toolkit\Str;

/**
 * HTTP Language Acceptance negotiation
 *
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Language extends Acceptance
{

    /**
     * Creates the Language Acceptance object.
     * When nothing is passed, the accepted language
     * will be taken from the `$_SERVER` global
     *
     * @param  string $input
     */
    public function __construct(string $input = null)
    {
        if ($input === null) {
            $input = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        }
        parent::__construct($input);
    }

    /**
     * Special parser for items in the acceptance chain
     * to extract the language code, locale and region
     *
     * @param  string $value
     * @param  float  $quality
     * @return array
     */
    public function item(string $value, float $quality): array
    {
        $parts  = Str::split($value, '-');
        $code   = isset($parts[0]) ? Str::lower($parts[0]) : null;
        $region = isset($parts[1]) ? Str::upper($parts[1]) : null;
        $item   = parent::item($value, $quality);

        $item['locale'] = $region ? $code . '_' . $region : $code;
        $item['code']   = $code;
        $item['region'] = $region;

        return $item;
    }

    /**
     * Returns the locale of the preferred item
     * in the acceptance chain
     *
     * @return string|null
     */
    public function locale()
    {
        return $this->info('locale');
    }

    /**
     * Returns the language code of the preferred item
     * in the acceptance chain
     *
     * @return string|null
     */
    public function code()
    {
        return $this->info('code');
    }

    /**
     * Returns the region of the preferred item
     * in the acceptance chain if specified
     *
     * @return string|null
     */
    public function region()
    {
        return $this->info('region');
    }

    /**
     * Special match method to match agains the locale
     * instead of the orginal value of each item
     *
     * @param  array  $item
     * @param  string $pattern
     * @return boolean
     */
    protected function match(array $item, string $pattern): bool
    {
        return $item['locale'] === $pattern;
    }
}
