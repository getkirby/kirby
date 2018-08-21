<?php

namespace Kirby\Cms;

/**
 * Extension of the basic blueprint class
 * to handle all blueprints for users.
 */
class UserBlueprint extends Blueprint
{

    public function __construct(array $props)
    {
        parent::__construct($props);

        // normalize all available page options
        $this->props['options'] = $this->normalizeOptions($props['options'] ?? true,
            // defaults
            [
                'create'         => true,
                'changeEmail'    => true,
                'changeLanguage' => true,
                'changeName'     => true,
                'changePassword' => true,
                'changeRole'     => true,
                'delete'         => true,
                'update'         => true,
            ]
        );

    }

}
