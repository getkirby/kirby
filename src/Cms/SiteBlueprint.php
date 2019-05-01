<?php

namespace Kirby\Cms;

/**
 * Extension of the basic blueprint class
 * to handle the blueprint for the site.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class SiteBlueprint extends Blueprint
{
    public function __construct(array $props)
    {
        parent::__construct($props);

        // normalize all available page options
        $this->props['options'] = $this->normalizeOptions(
            $props['options'] ?? true,
            // defaults
            [
                'changeTitle' => null,
                'update'      => null,
            ],
            // aliases
            [
                'title' => 'changeTitle',
            ]
        );
    }
}
