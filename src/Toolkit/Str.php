<?php

namespace Kirby\Toolkit;

use Exception;

/**
 * The String class provides a set
 * of handy methods for string
 * handling and manipulation.
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Str
{

    /**
     * Language translation table
     *
     * @var array
     */
    public static $language = [];

    /**
     * Ascii translation table
     *
     * @var array
     */
    public static $ascii = [
        '/°|₀/' => '0',
        '/¹|₁/' => '1',
        '/²|₂/' => '2',
        '/³|₃/' => '3',
        '/⁴|₄/' => '4',
        '/⁵|₅/' => '5',
        '/⁶|₆/' => '6',
        '/⁷|₇/' => '7',
        '/⁸|₈/' => '8',
        '/⁹|₉/' => '9',
        '/À|Á|Â|Ã|Å|Ǻ|Ā|Ă|Ą|Ǎ|Ä|A/' => 'A',
        '/à|á|â|ã|å|ǻ|ā|ă|ą|ǎ|ª|æ|ǽ|ä|a|а/' => 'a',
        '/Б/' => 'B',
        '/б/' => 'b',
        '/Ç|Ć|Ĉ|Ċ|Č|Ц/' => 'C',
        '/ç|ć|ĉ|ċ|č|ц/' => 'c',
        '/Ð|Ď|Đ/' => 'Dj',
        '/ð|ď|đ/' => 'dj',
        '/Д/' => 'D',
        '/д/' => 'd',
        '/È|É|Ê|Ë|Ē|Ĕ|Ė|Ę|Ě|Е|Ё|Э/' => 'E',
        '/è|é|ê|ë|ē|ĕ|ė|ę|ě|е|ё|э/' => 'e',
        '/Ф/' => 'F',
        '/ƒ|ф/' => 'f',
        '/Ĝ|Ğ|Ġ|Ģ|Г/' => 'G',
        '/ĝ|ğ|ġ|ģ|г/' => 'g',
        '/Ĥ|Ħ|Х/' => 'H',
        '/ĥ|ħ|х/' => 'h',
        '/Ì|Í|Î|Ï|Ĩ|Ī|Ĭ|Ǐ|Į|İ|И/' => 'I',
        '/ì|í|î|ï|ĩ|ī|ĭ|ǐ|į|ı|и|i̇/' => 'i',
        '/Ĵ|Й/' => 'J',
        '/ĵ|й/' => 'j',
        '/Ķ|К/' => 'K',
        '/ķ|к/' => 'k',
        '/Ĺ|Ļ|Ľ|Ŀ|Ł|Л/' => 'L',
        '/ĺ|ļ|ľ|ŀ|ł|л/' => 'l',
        '/М/' => 'M',
        '/м/' => 'm',
        '/Ñ|Ń|Ņ|Ň|Н/' => 'N',
        '/ñ|ń|ņ|ň|ŉ|н/' => 'n',
        '/Ò|Ó|Ô|Õ|Ō|Ŏ|Ǒ|Ő|Ơ|Ø|Ǿ|Ö|O/' => 'O',
        '/ò|ó|ô|õ|ō|ŏ|ǒ|ő|ơ|ø|ǿ|º|ö|o|о/' => 'o',
        '/П/' => 'P',
        '/п/' => 'p',
        '/Ŕ|Ŗ|Ř|Р/' => 'R',
        '/ŕ|ŗ|ř|р/' => 'r',
        '/Ś|Ŝ|Ş|Ș|Š|С/' => 'S',
        '/ś|ŝ|ş|ș|š|ſ|с/' => 's',
        '/Ţ|Ț|Ť|Ŧ|Т/' => 'T',
        '/ţ|ț|ť|ŧ|т/' => 't',
        '/Ù|Ú|Û|Ũ|Ū|Ŭ|Ů|Ű|Ų|Ư|Ǔ|Ǖ|Ǘ|Ǚ|Ǜ|У|Ü|U/' => 'U',
        '/ù|ú|û|ũ|ū|ŭ|ů|ű|ų|ư|ǔ|ǖ|ǘ|ǚ|ǜ|у|ü|u/' => 'u',
        '/В/' => 'V',
        '/в/' => 'v',
        '/Ý|Ÿ|Ŷ|Ы/' => 'Y',
        '/ý|ÿ|ŷ|ы/' => 'y',
        '/Ŵ/' => 'W',
        '/ŵ/' => 'w',
        '/Ź|Ż|Ž|З/' => 'Z',
        '/ź|ż|ž|з/' => 'z',
        '/Æ|Ǽ/' => 'AE',
        '/ß/' => 'ss',
        '/Ĳ/' => 'IJ',
        '/ĳ/' => 'ij',
        '/Œ/' => 'OE',
        '/Ч/' => 'Ch',
        '/ч/' => 'ch',
        '/Ю/' => 'Ju',
        '/ю/' => 'ju',
        '/Я/' => 'Ja',
        '/я/' => 'ja',
        '/Ш/' => 'Sh',
        '/ш/' => 'sh',
        '/Щ/' => 'Shch',
        '/щ/' => 'shch',
        '/Ж/' => 'Zh',
        '/ж/' => 'zh',
    ];

    /**
     * Default settings for class methods
     *
     * @var array
     */
    public static $defaults = [
        'slug' => [
            'separator' => '-',
            'allowed'   => 'a-z0-9'
        ]
    ];

    /**
     * Parse accepted values and their quality from an
     * accept string like an Accept or Accept-Language header
     *
     * @param string $input
     * @return array
     */
    public static function accepted(string $input): array
    {
        $items = [];

        // check each type in the Accept header
        foreach (static::split($input, ',') as $item) {
            $parts   = static::split($item, ';');
            $value   = A::first($parts); // $parts now only contains params
            $quality = 1;

            // check for the q param ("quality" of the type)
            foreach ($parts as $param) {
                $param = static::split($param, '=');
                if (A::get($param, 0) === 'q' && !empty($param[1])) {
                    $quality = $param[1];
                }
            }

            $items[$quality][] = $value;
        }

        // sort items by quality
        krsort($items);

        $result = [];

        foreach ($items as $quality => $values) {
            foreach ($values as $value) {
                $result[] = [
                    'quality' => $quality,
                    'value'   => $value
                ];
            }
        }

        return $result;
    }

    /**
     * Returns the rest of the string after the given character
     *
     * @param  string   $string
     * @param  string   $needle
     * @param  bool     $caseInsensitive
     * @return string
     */
    public static function after(string $string, string $needle, bool $caseInsensitive = false): string
    {
        $position = static::position($string, $needle, $caseInsensitive);

        if ($position === false) {
            return false;
        } else {
            return static::substr($string, $position + static::length($needle));
        }
    }

    /**
     * Convert a string to 7-bit ASCII.
     *
     * @param  string  $string
     * @return string
     */
    public static function ascii(string $string): string
    {
        $string  = str_replace(
            array_keys(static::$language),
            array_values(static::$language),
            $string
        );

        $string  = preg_replace(
            array_keys(static::$ascii),
            array_values(static::$ascii),
            $string
        );

        return preg_replace('/[^\x09\x0A\x0D\x20-\x7E]/', '', $string);
    }

    /**
     * Returns the beginning of a string before the given character
     *
     * @param  string   $string
     * @param  string   $needle
     * @param  bool     $caseInsensitive
     * @return string
     */
    public static function before(string $string, string $needle, bool $caseInsensitive = false): string
    {
        $position = static::position($string, $needle, $caseInsensitive);

        if ($position === false) {
            return false;
        } else {
            return static::substr($string, 0, $position);
        }
    }

    /**
     * Returns everything between two strings from the first occurrence of a given string
     *
     * @param string $string
     * @param string $start
     * @param string $end
     * @return string
     */
    public static function between(string $string = null, string $start, string $end): string
    {
        return static::before(static::after($string, $start), $end);
    }

    /**
     * Checks if a str contains another string
     *
     * @param  string  $string
     * @param  string  $needle
     * @param  bool $caseInsensitive
     * @return bool
     */
    public static function contains(string $string = null, string $needle, bool $caseInsensitive = false): bool
    {
        return call_user_func($caseInsensitive === true ? 'stristr' : 'strstr', $string, $needle) !== false;
    }

    /**
     * Converts a string to a different encoding
     *
     * @param string $string
     * @param string $targetEncoding
     * @param string $sourceEncoding (optional)
     * @return string
     */
    public static function convert($string, $targetEncoding, $sourceEncoding = null)
    {
        // detect the source encoding if not passed as third argument
        if ($sourceEncoding === null) {
            $sourceEncoding = static::encoding($string);
        }

        // no need to convert if the target encoding is the same
        if (strtolower($sourceEncoding) === strtolower($targetEncoding)) {
            return $string;
        }

        return iconv($sourceEncoding, $targetEncoding, $string);
    }

    /**
     * Encode a string (used for email addresses)
     *
     * @param  string  $string
     * @return string
     */
    public static function encode(string $string): string
    {
        $encoded = '';

        for ($i = 0; $i < static::length($string); $i++) {
            $char = static::substr($string, $i, 1);
            list(, $code) = unpack('N', mb_convert_encoding($char, 'UCS-4BE', 'UTF-8'));
            $encoded .= rand(1, 2) == 1 ? '&#' . $code . ';' : '&#x' . dechex($code) . ';';
        }

        return $encoded;
    }

    /**
     * Tries to detect the string encoding
     *
     * @param  string $string
     * @return string
     */
    public static function encoding(string $string): string
    {
        return mb_detect_encoding($string, 'UTF-8, ISO-8859-1, windows-1251', true);
    }

    /**
     * Checks if a string ends with the passed needle
     *
     * @param  string   $string
     * @param  string   $needle
     * @param  bool     $caseInsensitive
     * @return bool
     */
    public static function endsWith(string $string, string $needle, bool $caseInsensitive = false): bool
    {
        if ($needle === '') {
            return true;
        }

        $probe = static::substr($string, -static::length($needle));

        if ($caseInsensitive === true) {
            $needle = static::lower($needle);
            $probe  = static::lower($probe);
        }

        return $needle === $probe;
    }

    /**
     * Creates an excerpt of a string
     * It removes all html tags first and then cuts the string
     * according to the specified number of chars.
     *
     * @param  string  $string The string to be shortened
     * @param  int     $chars The final number of characters the string should have
     * @param  boolean $strip True: remove the HTML tags from the string first
     * @param  string  $rep The element, which should be added if the string is too long. Ellipsis is the default.
     * @return string  The shortened string
     */
    public static function excerpt($string, $chars = 140, $strip = true, $rep = '…')
    {
        if ($strip === true) {
            $string = strip_tags(str_replace('<', ' <', $string));
        }

        // replace line breaks with spaces
        $string = str_replace(PHP_EOL, ' ', trim($string));

        // remove double spaces
        $string = preg_replace('![ ]{2,}!', ' ', $string);

        if ($chars === 0) {
            return $string;
        }

        if (static::length($string) <= $chars) {
            return $string;
        }

        return static::substr($string, 0, strrpos(static::substr($string, 0, $chars), ' ')) . ' ' . $rep;
    }

    /**
     * Convert the value to a float with a decimal
     * point, no matter what the locale setting is
     *
     * @param string|int|float $value
     * @return string
     */
    public static function float($value): string
    {
        $value   = str_replace(',', '.', $value);
        $decimal = strlen(substr(strrchr($value, '.'), 1));
        return number_format((float)$value, $decimal, '.', false);
    }

    /**
     * Returns the rest of the string starting from the given character
     *
     * @param  string   $string
     * @param  string   $needle
     * @param  bool     $caseInsensitive
     * @return string
     */
    public static function from(string $string, string $needle, bool $caseInsensitive = false): string
    {
        $position = static::position($string, $needle, $caseInsensitive);

        if ($position === false) {
            return false;
        } else {
            return static::substr($string, $position);
        }
    }

    /**
     * Checks if the given string is a URL
     *
     * @param string|null $string
     * @return boolean
     */
    public static function isURL(string $string = null): bool
    {
        return filter_var($string, FILTER_VALIDATE_URL);
    }

    /**
     * Convert a string to kebab case.
     *
     * @param  string  $value
     * @return string
     */
    public static function kebab(string $value = null): string
    {
        return static::snake($value, '-');
    }

    /**
     * A UTF-8 safe version of strlen()
     *
     * @param  string  $string
     * @return int
     */
    public static function length(string $string = null): int
    {
        return mb_strlen($string, 'UTF-8');
    }

    /**
     * A UTF-8 safe version of strtolower()
     *
     * @param  string  $string
     * @return string
     */
    public static function lower(string $string = null): string
    {
        return mb_strtolower($string, 'UTF-8');
    }

    /**
     * Safe ltrim alternative
     *
     * @param string $string
     * @param string $trim
     * @return string
     */
    public static function ltrim(string $string, string $trim = ' '): string
    {
        return preg_replace('!^(' . preg_quote($trim) . ')+!', '', $string);
    }


    /**
     * Get a character pool with various possible combinations
     *
     * @param  string|array $type
     * @param  boolean $array
     * @return string|array
     */
    public static function pool($type, bool $array = true)
    {
        $pool = [];

        if (is_array($type) === true) {
            foreach ($type as $t) {
                $pool = array_merge($pool, static::pool($t));
            }

            return $pool;
        } else {
            switch ($type) {
                case 'alphaLower':
                    $pool = range('a', 'z');
                    break;
                case 'alphaUpper':
                    $pool = range('A', 'Z');
                    break;
                case 'alpha':
                    $pool = static::pool(['alphaLower', 'alphaUpper']);
                    break;
                case 'num':
                    $pool = range(0, 9);
                    break;
                case 'alphaNum':
                    $pool = static::pool(['alpha', 'num']);
                    break;
            }
        }

        return $array ? $pool : implode('', $pool);
    }

    /**
     * Returns the position of a needle in a string
     * if it can be found
     *
     * @param  string   $string
     * @param  string   $needle
     * @param  bool     $caseInsensitive
     * @return int|bool
     */
    public static function position(string $string = null, string $needle, bool $caseInsensitive = false)
    {
        if ($caseInsensitive === true) {
            $string = static::lower($string);
            $needle = static::lower($needle);
        }

        return mb_strpos($string, $needle, 0, 'UTF-8');
    }

    /**
     * Runs a string query.
     * Check out the Query class for more information.
     *
     * @param string $query
     * @param array $data
     * @return string|null
     */
    public static function query(string $query, array $data = [])
    {
        return (new Query($query, $data))->result();
    }

    /**
     * Generates a random string that may be used for cryptographic purposes
     *
     * @param int $length The length of the random string
     * @param string $type Pool type (type of allowed characters)
     * @return string
     */
    public static function random(int $length = null, string $type = 'alphaNum')
    {
        if ($length === null) {
            $length = random_int(5, 10);
        }

        $pool = static::pool($type, false);

        // catch invalid pools
        if (!$pool) {
            return false;
        }

        // regex that matches all characters *not* in the pool of allowed characters
        $regex = '/[^' . $pool . ']/';

        // collect characters until we have our required length
        $result = '';

        while (($currentLength = strlen($result)) < $length) {
            $missing = $length - $currentLength;
            $bytes   = random_bytes($missing);
            $result .= substr(preg_replace($regex, '', base64_encode($bytes)), 0, $missing);
        }

        return $result;
    }

    /**
     * Replaces all or some occurrences of the search string with the replacement string
     * Extension of the str_replace() function in PHP with an additional $limit parameter
     *
     * @param  string|array $string  String being replaced on (haystack);
     *                               can be an array of multiple subject strings
     * @param  string|array $search  Value being searched for (needle)
     * @param  string|array $replace Value to replace matches with
     * @param  int|array    $limit   Maximum possible replacements for each search value;
     *                               multiple limits for each search value are supported;
     *                               defaults to no limit
     * @return string|array          String with replaced values;
     *                               if $string is an array, array of strings
     */
    public static function replace($string, $search, $replace, $limit = -1)
    {
        // convert Kirby collections to arrays
        if (is_a($string, 'Kirby\Toolkit\Collection') === true) {
            $string = $string->toArray();
        }

        if (is_a($search, 'Kirby\Toolkit\Collection') === true) {
            $search  = $search->toArray();
        }

        if (is_a($replace, 'Kirby\Toolkit\Collection') === true) {
            $replace = $replace->toArray();
        }

        // without a limit we might as well use the built-in function
        if ($limit === -1) {
            return str_replace($search, $replace, $string);
        }

        // if the limit is zero, the result will be no replacements at all
        if ($limit === 0) {
            return $string;
        }

        // multiple subjects are run separately through this method
        if (is_array($string) === true) {
            $result = [];
            foreach ($string as $s) {
                $result[] = static::replace($s, $search, $replace, $limit);
            }
            return $result;
        }

        // build an array of replacements
        // we don't use an associative array because otherwise you couldn't
        // replace the same string with different replacements
        $replacements = static::replacements($search, $replace, $limit);

        // run the string and the replacement array through the replacer
        return static::replaceReplacements($string, $replacements);
    }

    /**
     * Generates a replacement array out of dynamic input data
     * Used for Str::replace()
     *
     * @param  string|array $search  Value being searched for (needle)
     * @param  string|array $replace Value to replace matches with
     * @param  int|array    $limit   Maximum possible replacements for each search value;
     *                               multiple limits for each search value are supported;
     *                               defaults to no limit
     * @return array                 List of replacement arrays, each with a
     *                               'search', 'replace' and 'limit' attribute
     */
    public static function replacements($search, $replace, $limit): array
    {
        $replacements = [];

        if (is_array($search) === true && is_array($replace) === true) {
            foreach ($search as $i => $s) {
                // replace with an empty string if no replacement string was defined for this index;
                // behavior is identical to the official PHP str_replace()
                $r = $replace[$i] ?? '';

                if (is_array($limit) === true) {
                    // don't apply a limit if no limit was defined for this index
                    $l = $limit[$i] ?? -1;
                } else {
                    $l = $limit;
                }

                $replacements[] = ['search' => $s, 'replace' => $r, 'limit' => $l];
            }
        } elseif (is_array($search) === true && is_string($replace) === true) {
            foreach ($search as $i => $s) {
                if (is_array($limit) === true) {
                    // don't apply a limit if no limit was defined for this index
                    $l = $limit[$i] ?? -1;
                } else {
                    $l = $limit;
                }

                $replacements[] = ['search' => $s, 'replace' => $replace, 'limit' => $l];
            }
        } elseif (is_string($search) === true && is_string($replace) === true && is_int($limit) === true) {
            $replacements[] = compact('search', 'replace', 'limit');
        } else {
            throw new Exception('Invalid combination of $search, $replace and $limit params.');
        }

        return $replacements;
    }

    /**
     * Takes a replacement array and processes the replacements
     * Used for Str::replace()
     *
     * @param  string $string       String being replaced on (haystack)
     * @param  array  $replacements Replacement array from Str::replacements()
     * @return string               String with replaced values
     */
    public static function replaceReplacements(string $string, array $replacements): string
    {
        // replace in the order of the replacements
        // behavior is identical to the official PHP str_replace()
        foreach ($replacements as $replacement) {
            if (is_int($replacement['limit']) === false) {
                throw new Exception('Invalid limit "' . $replacement['limit'] . '".');
            } elseif ($replacement['limit'] === -1) {

                // no limit, we don't need our special replacement routine
                $string = str_replace($replacement['search'], $replacement['replace'], $string);
            } elseif ($replacement['limit'] > 0) {

                // limit given, only replace for $replacement['limit'] times per replacement
                $position = -1;

                for ($i = 0; $i < $replacement['limit']; $i++) {
                    $position = strpos($string, $replacement['search'], $position + 1);

                    if (is_int($position) === true) {
                        $string = substr_replace($string, $replacement['replace'], $position, strlen($replacement['search']));
                        // adapt $pos to the now changed offset
                        $position = $position + strlen($replacement['replace']) - strlen($replacement['search']);
                    } else {
                        // no more match in the string
                        break;
                    }
                }
            }
        }

        return $string;
    }

    /**
     * Safe rtrim alternative
     *
     * @param string $string
     * @param string $trim
     * @return string
     */
    public static function rtrim(string $string, string $trim = ' '): string
    {
        return preg_replace('!(' . preg_quote($trim) . ')+$!', '', $string);
    }

    /**
     * Shortens a string and adds an ellipsis if the string is too long
     *
     * <code>
     *
     * echo Str::short('This is a very, very, very long string', 10);
     * // output: This is a…
     *
     * echo Str::short('This is a very, very, very long string', 10, '####');
     * // output: This i####
     *
     * </code>
     *
     * @param  string  $string   The string to be shortened
     * @param  int     $length   The final number of characters the
     *                           string should have
     * @param  string  $appendix The element, which should be added if the
     *                           string is too long. Ellipsis is the default.
     * @return string            The shortened string
     */
    public static function short(string $string = null, int $length = 0, string $appendix = '…'): ?string
    {
        if ($length === 0) {
            return $string;
        }

        if (static::length($string) <= $length) {
            return $string;
        }

        return static::substr($string, 0, $length) . $appendix;
    }

    /**
     * Convert a string to a safe version to be used in a URL
     *
     * @param  string  $string     The unsafe string
     * @param  string  $separator  To be used instead of space and
     *                             other non-word characters.
     * @param  string  $allowed    List of all allowed characters (regex)
     * @return string              The safe string
     */
    public static function slug(string $string = null, string $separator = null, string $allowed = null): string
    {
        $separator = $separator ?? static::$defaults['slug']['separator'];
        $allowed   = $allowed   ?? static::$defaults['slug']['allowed'];

        $string = trim($string);
        $string = static::lower($string);
        $string = static::ascii($string);

        // replace spaces with simple dashes
        $string = preg_replace('![^' . $allowed . ']!i', $separator, $string);

        if (strlen($separator) > 0) {
            // remove double separators
            $string = preg_replace('![' . preg_quote($separator) . ']{2,}!', $separator, $string);
        }

        // replace slashes with dashes
        $string = str_replace('/', $separator, $string);

        // trim leading and trailing non-word-chars
        $string = preg_replace('!^[^a-z0-9]+!', '', $string);
        $string = preg_replace('![^a-z0-9]+$!', '', $string);

        return $string;
    }

    /**
     * Convert a string to snake case.
     *
     * @param  string  $value
     * @param  string  $delimiter
     * @return string
     */
    public static function snake(string $value = null, string $delimiter = '_'): string
    {
        if (!ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', ucwords($value));
            $value = static::lower(preg_replace('/(.)(?=[A-Z])/u', '$1'.$delimiter, $value));
        }
        return $value;
    }

    /**
     * Better alternative for explode()
     * It takes care of removing empty values
     * and it has a built-in way to skip values
     * which are too short.
     *
     * @param  string  $string The string to split
     * @param  string  $separator The string to split by
     * @param  int     $length The min length of values.
     * @return array   An array of found values
     */
    public static function split($string, string $separator = ',', int $length = 1): array
    {
        if (is_array($string) === true) {
            return $string;
        }

        $parts  = explode($separator, $string);
        $out    = [];

        foreach ($parts as $p) {
            $p = trim($p);
            if (static::length($p) > 0 && static::length($p) >= $length) {
                $out[] = $p;
            }
        }

        return $out;
    }

    /**
     * Checks if a string starts with the passed needle
     *
     * @param  string   $string
     * @param  string   $needle
     * @param  bool     $caseInsensitive
     * @return bool
     */
    public static function startsWith(string $string = null, string $needle, bool $caseInsensitive = false): bool
    {
        if ($needle === '') {
            return true;
        }

        return static::position($string, $needle, $caseInsensitive) === 0;
    }

    /**
     * A UTF-8 safe version of substr()
     *
     * @param  string  $string
     * @param  int     $start
     * @param  int     $length
     * @return string
     */
    public static function substr(string $string = null, int $start = 0, int $length = null): string
    {
        return mb_substr($string, $start, $length, 'UTF-8');
    }

    /**
     * Replaces placeholders in string with value from array
     *
     * <code>
     *
     * echo Str::template('From {{ b }} to {{ a }}', ['a' => 'there', 'b' => 'here']);
     * // output: From here to there
     *
     * </code>
     *
     * @param  string  $string   The string with placeholders
     * @param  array   $data     Associative array with placeholders as
     *                           keys and replacements as values
     * @param  string  $fallback A fallback if a token does not have any matches
     * @return string            The filled-in string
     */
    public static function template(string $string = null, array $data = [], string $fallback = null, string $start = '{{', string $end = '}}'): string
    {
        return preg_replace_callback('!' . $start . '(.*?)' . $end . '!', function ($match) use ($data, $fallback) {
            $query = trim($match[1]);
            if (strpos($query, '.') !== false) {
                return (new Query($match[1], $data))->result() ?? $fallback;
            }
            return $data[$query] ?? $fallback;
        }, $string);
    }

    /**
     * Convert the string to the given type
     *
     * @param string $string
     * @param mixed $type
     * @return mixed
     */
    public static function toType($string = null, $type)
    {
        if (is_string($type) === false) {
            $type = gettype($type);
        }

        switch ($type) {
            case 'array':
                return (array)$string;
            case 'bool':
            case 'boolean':
                return filter_var($string, FILTER_VALIDATE_BOOLEAN);
            case 'double':
            case 'float':
                return floatval($string);
            case 'int':
            case 'integer':
                return intval($string);
        }

        return (string)$string;
    }

    /**
     * Safe trim alternative
     *
     * @param string $string
     * @param string $trim
     * @return string
     */
    public static function trim(string $string, string $trim = ' '): string
    {
        return static::rtrim(static::ltrim($string, $trim), $trim);
    }

    /**
     * A UTF-8 safe version of ucfirst()
     *
     * @param  string $string
     * @return string
     */
    public static function ucfirst(string $string = null): string
    {
        return static::upper(static::substr($string, 0, 1)) . static::lower(static::substr($string, 1));
    }

    /**
     * A UTF-8 safe version of ucwords()
     *
     * @param  string  $string
     * @return string
     */
    public static function ucwords(string $string = null): string
    {
        return mb_convert_case($string, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Removes all html tags and encoded chars from a string
     *
     * <code>
     *
     * echo str::unhtml('some <em>crazy</em> stuff');
     * // output: some uber crazy stuff
     *
     * </code>
     *
     * @param  string  $string
     * @return string  The html string
     */
    public static function unhtml(string $string = null): string
    {
        return Html::decode($string);
    }

    /**
     * Returns the beginning of a string until the given character
     *
     * @param  string   $string
     * @param  string   $needle
     * @param  bool     $caseInsensitive
     * @return string
     */
    public static function until(string $string, string $needle, bool $caseInsensitive = false): string
    {
        $position = static::position($string, $needle, $caseInsensitive);

        if ($position === false) {
            return false;
        } else {
            return static::substr($string, 0, $position + static::length($needle));
        }
    }

    /**
     * A UTF-8 safe version of strotoupper()
     *
     * @param  string  $string
     * @return string
     */
    public static function upper(string $string = null): string
    {
        return mb_strtoupper($string, 'UTF-8');
    }

    /**
     * The widont function makes sure that there are no
     * typographical widows at the end of a paragraph –
     * that's a single word in the last line
     *
     * @param string $string
     * @return string
     */
    public static function widont(string $string = null): string
    {
        return preg_replace_callback('|([^\s])\s+([^\s]+)\s*$|u', function ($matches) {
            if (static::contains($matches[2], '-')) {
                return $matches[1] . ' ' . str_replace('-', '&#8209;', $matches[2]);
            } else {
                return $matches[1] . '&nbsp;' . $matches[2];
            }
        }, $string);
    }
}
