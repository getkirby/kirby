<?php

namespace Kirby\Http\Request\Auth;

use Kirby\Http\Request\Auth;

/**
 * Bearer token authentication data
 *
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class BearerAuth extends Auth
{
    /**
     * Returns the authentication token
     *
     * @return string
     */
    public function token(): string
    {
        return $this->data;
    }

    /**
     * Returns the auth type
     *
     * @return string
     */
    public function type(): string
    {
        return 'bearer';
    }
}
