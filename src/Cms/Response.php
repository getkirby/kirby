<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Http\Response as BaseResponse;

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
class Response extends BaseResponse
{

    /**
     * Adjusted redirect creation which
     * parses locations with the Url::to method
     * first.
     *
     * @param string $location
     * @param int $code
     * @return self
     */
    public static function redirect(?string $location = null, ?int $code = null)
    {
        return parent::redirect(Url::to($location ?? '/'), $code);
    }
}
