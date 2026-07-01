<?php

namespace Kirby\Text;

use Exception;
use Kirby\Cms\Helpers;
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
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class KirbyTags
{
	public static function parse(
		string|null $text = null,
		array $data = [],
		array $options = [],
		bool $debug = false
	): string {
		// make sure $text is a string
		$text ??= '';

		// @deprecated 5.5.0 the `$options` argument only ever carried the
		// `debug` flag; derive it from there for backward compatibility
		if ($options !== []) {
			Helpers::deprecated('The `$options` argument of `KirbyTags::parse()` has been deprecated. Use the `$debug` argument instead.', 'kirbytags-parse-options');
			$debug = $options['debug'] ?? $debug;
		}

		$regex = '!
            (?=[^\]])               # positive lookahead that matches a group after the main expression without including ] in the result
            (?=\([a-z0-9_-]+:)      # positive lookahead that requires starts with ( and lowercase ASCII letters, digits, underscores or hyphens followed with : immediately to the right of the current location
            (\(                     # capturing group 1
                (?:[^()]+|(?1))*+   # repetitions of any chars other than ( and ) or the whole group 1 pattern (recursed)
            \))                     # end of capturing group 1
        !isx';

		return preg_replace_callback($regex, function ($match) use ($data, $debug) {
			try {
				return KirbyTag::parse($match[0], $data)->render();
			} catch (InvalidArgumentException $e) {
				if (Str::startsWith($e->getMessage(), 'Undefined tag type:') === true) {
					return $match[0];
				}

				// stay silent in production and ignore non-existing tags
				if ($debug !== true) {
					error_log($e);

					return $match[0];
				}

				throw $e;
			} catch (Exception $e) {
				if ($debug === true) {
					throw $e;
				}

				error_log($e);

				return $match[0];
			}
		}, $text);
	}
}
