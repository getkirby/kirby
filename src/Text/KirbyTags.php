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
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class KirbyTags
{
	/**
	 * Replaces all KirbyTags in the given text with their HTML
	 *
	 * @param $data Data for the tags, e.g. the `kirby` instance and `parent` model
	 * @param $debug If `true`, exceptions of a tag are thrown instead of being logged
	 */
	public static function parse(
		string|null $text = null,
		array $data = [],
		bool $debug = false
	): string {
		$text ??= '';

		$regex = '!
            (?=[^\]])               # positive lookahead that matches a group after the main expression without including ] in the result
            (?=\([a-z0-9_-]+:)      # positive lookahead that requires starts with ( and lowercase ASCII letters, digits, underscores or hyphens followed with : immediately to the right of the current location
            (\(                     # capturing group 1
                (?:[^()]+|(?1))*+   # repetitions of any chars other than ( and ) or the whole group 1 pattern (recursed)
            \))                     # end of capturing group 1
        !isx';

		return preg_replace_callback($regex, function (array $match) use ($data, $debug): string {
			try {
				return KirbyTag::parse($match[0], $data)->render();
			} catch (Exception $e) {
				// undefined tags stay as they are, even in debug mode
				if (
					$e instanceof InvalidArgumentException === true &&
					Str::startsWith($e->getMessage(), 'Undefined tag type:') === true
				) {
					return $match[0];
				}

				if ($debug === true) {
					throw $e;
				}

				// stay silent in production
				error_log($e);

				return $match[0];
			}
		}, $text) ?? $text;
	}
}
