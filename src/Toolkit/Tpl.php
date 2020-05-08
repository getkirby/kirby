<?php

namespace Kirby\Toolkit;

use Throwable;

/**
 * Simple PHP template engine
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Tpl
{
    /**
     * Renders the template
     *
     * @param string $file
     * @param array $data
     * @return string
     */
    public static function load(string $file = null, array $data = []): string
    {
        if (is_file($file) === false) {
            return '';
        }

        ob_start();

        $exception = null;
        try {
            F::load($file, null, $data);
        } catch (Throwable $e) {
            $exception = $e;
        }

        $content = ob_get_contents();
        ob_end_clean();

        if ($exception === null) {
            return $content;
        }

        throw $exception;
    }
}
