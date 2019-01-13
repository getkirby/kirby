<?php

namespace Kirby\Toolkit;

use Exception;
use Throwable;

/**
 * Simple PHP template engine
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Tpl
{

    /**
     * Renders the template
     *
     * @param string $__file
     * @param array $__data
     * @return string
     */
    public static function load(string $__file = null, array $__data = []): string
    {
        if (file_exists($__file) === false) {
            return '';
        }

        $exception = null;

        ob_start();
        extract($__data);

        try {
            require $__file;
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
