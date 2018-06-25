<?php

namespace Kirby\Toolkit;

/**
 * A set of handy string methods
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Str
{

    /**
     * Ascii translation table
     *
     * @var array
     */
    protected static $ascii = [
        '/À|Á|Â|Ã|Å|Ǻ|Ā|Ă|Ą|Ǎ|Ä|A/' => 'A',
        '/à|á|â|ã|å|ǻ|ā|ă|ą|ǎ|ª|æ|ǽ|ä|a/' => 'a',
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
        '/ì|í|î|ï|ĩ|ī|ĭ|ǐ|į|ı|и/' => 'i',
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
        '/ò|ó|ô|õ|ō|ŏ|ǒ|ő|ơ|ø|ǿ|º|ö|o/' => 'o',
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
        '/ß/'=> 'ss',
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
     * Convert a string to 7-bit ASCII.
     *
     * @param  string  $string
     * @return string
     */
    public static function ascii(string $string): string
    {
        $foreign = static::$ascii;
        $string  = preg_replace(array_keys($foreign), array_values($foreign), $string);
        return preg_replace('/[^\x09\x0A\x0D\x20-\x7E]/', '', $string);
    }

    /**
     * Checks if the string is a valid emoji
     *
     * @param string|null $string
     * @return boolean
     */
    public static function isEmoji(string $string = null): bool
    {
        if ($string === null) {
            return false;
        }

        $chr = function ($i) {
            return iconv('UCS-4LE', 'UTF-8', pack('V', $i));
        };

        if (preg_match('/[' .
            $chr(0x1F600) . '-' . $chr(0x1F64F) . // Emoticons
            $chr(0x1F300) . '-' . $chr(0x1F5FF) . // Misc Symbols and Pictographs
            $chr(0x1F680) . '-' . $chr(0x1F6FF) . // Transport and Map
            $chr(0x2600)  . '-' . $chr(0x26FF)  . // Misc symbols
            $chr(0x2700)  . '-' . $chr(0x27BF)  . // Dingbats
            $chr(0xFE00)  . '-' . $chr(0xFE0F)  . // Variation Selectors
            $chr(0x1F900) . '-' . $chr(0x1F9FF) . // Supplemental Symbols and Pictographs
            $chr(0x1F1E6) . '-' . $chr(0x1F1FF) . // Flags
            ']/u', $string)) {
            return true;
        }

        return false;
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

        // trim trailing and leading dashes
        $string = trim($string, $separator);

        // replace slashes with dashes
        $string = str_replace('/', $separator, $string);

        return $string;
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
        return mb_detect_encoding($string, 'UTF-8, ISO-8859-1, windows-1251');
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

        $string = trim($string, $separator);
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
     * Checks if a str contains another string
     *
     * @param  string  $string
     * @param  string  $needle
     * @param  bool $caseInsensitive
     * @return bool
     */
    public static function contains(string $string, string $needle, bool $caseInsensitive = false): bool
    {
        return call_user_func($caseInsensitive === true ? 'stristr' : 'strstr', $string, $needle) !== false;
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
    public static function position(string $string, string $needle, bool $caseInsensitive = false)
    {
        if ($caseInsensitive === true) {
            $string = static::lower($string);
            $needle = static::lower($needle);
        }

        return mb_strpos($string, $needle, 0, 'UTF-8');
    }

    /**
     * Checks if a string starts with the passed needle
     *
     * @param  string   $string
     * @param  string   $needle
     * @param  bool     $caseInsensitive
     * @return bool
     */
    public static function startsWith(string $string, string $needle, bool $caseInsensitive = false): bool
    {
        if ($needle === '') {
            return true;
        }

        return static::position($string, $needle, $caseInsensitive) === 0;
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
            return static::substr($string, $position + 1);
        }
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
    public static function short(string $string, int $length = 0, string $appendix = '…'): string
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
