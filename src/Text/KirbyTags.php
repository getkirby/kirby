<?php

namespace Kirby\Text;

use Exception;

/**
 * Parses and converts custom kirbytags in any
 * given string. KiryTags are defined via
 * `KirbyTag::$types`. The default tags for the
 * Cms are located in `kirby/config/tags.php`
 *
 * @package   Kirby Text
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class KirbyTags
{
    protected static $tagClass = 'Kirby\Text\KirbyTag';

    public static function parse(string $text = null, array $data = [], array $options = []): string
    {
        $regex = '!
            (?=[^\]])               # positive lookahead that matches a group after the main expression without including ] in the result
            (?=\([a-z0-9_-]+:)      # positive lookahead that requires starts with ( and lowercase ASCII letters, digits, underscores or hyphens followed with : immediately to the right of the current location
            (\(                     # capturing group 1
                (?:[^()]+|(?1))*+   # repetitions of any chars other than ( and ) or the whole group 1 pattern (recursed)
            \))                     # end of capturing group 1 
        !isx';

        return preg_replace_callback($regex, function ($match) use ($data, $options) {
            try {
                return static::$tagClass::parse($match[0], $data, $options)->render();
            } catch (Exception $e) {
                return $match[0];
            }
        }, $text);
    }
}
