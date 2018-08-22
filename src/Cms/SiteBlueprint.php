<?php

namespace Kirby\Cms;

/**
 * Extension of the basic blueprint class
 * to handle the blueprint for the site.
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
            ]
        );
    }
}
