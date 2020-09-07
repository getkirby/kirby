<?php

namespace Kirby\Cms;

/**
 * Custom response object with an optimized
 * redirect method to build correct Urls
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Response extends \Kirby\Http\Response
{
    /**
     * Adjusted redirect creation which
     * parses locations with the Url::to method
     * first.
     *
     * @param string|null $location
     * @param int|null $code
     * @return self
     */
    public static function redirect(?string $location = null, ?int $code = null)
    {
        return parent::redirect(Url::to($location ?? '/'), $code);
    }
}
