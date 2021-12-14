<?php

namespace Kirby\Text;

use Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Str;

/**
 * Parses and converts custom kirbytags in any
 * given string. KirbyTags are defined via
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
            $debug = $options['debug'] ?? false;

            try {
                return KirbyTag::parse($match[0], $data, $options)->render();
            } catch (InvalidArgumentException $e) {
                // stay silent in production and ignore non-existing tags
                if ($debug !== true || Str::startsWith($e->getMessage(), 'Undefined tag type:') === true) {
                    return $match[0];
                }

                throw $e;
            } catch (Exception $e) {
                if ($debug === true) {
                    throw $e;
                }

                return $match[0];
            }
        }, $text ?? '');
    }
}
