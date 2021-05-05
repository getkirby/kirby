<?php

namespace Kirby\Panel;

/**
 * Provides information about the site model for the Panel
 * @since 3.6.0
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Site extends Model
{
    /**
     * Returns the full path without leading slash
     *
     * @return string
     */
    public function path(): string
    {
        return 'site';
    }
}
