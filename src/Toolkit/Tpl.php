<?php

namespace Kirby\Toolkit;

use Kirby\Filesystem\F;
use Kirby\Template\Stack;
use Throwable;

/**
 * Simple PHP template engine
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Tpl
{
	/**
	 * Renders the template
	 *
	 * @throws Throwable
	 */
	public static function load(
		string|null $file = null,
		array $data = []
	): string {
		if ($file === null || is_file($file) === false) {
			return '';
		}

		Stack::open();
		ob_start();

		try {
			F::load($file, null, $data);
		} catch (Throwable $e) {
			$exception = $e;
		}

		$content = ob_get_contents();
		ob_end_clean();
		Stack::close();

		if (Stack::isOpen() === false) {
			$content = Stack::replace($content);
		}

		if (isset($exception) === true) {
			throw $exception;
		}

		return $content;
	}
}
