<?php

namespace Kirby\Cms;

/**
 * Extension of the basic blueprint class
 * to handle all blueprints for files.
 */
class FileBlueprint extends Blueprint
{
    public function __construct(array $props)
    {
        parent::__construct($props);

        // normalize all available page options
        $this->props['options'] = $this->normalizeOptions(
            $this->props['options'] ?? true,
            // defaults
            [
                'changeName' => null,
                'create'     => null,
                'delete'     => null,
                'replace'    => null,
                'update'     => null,
            ]
        );

        // normalize the accept settings
        $this->props['accept'] = $this->normalizeAccept($this->props['accept'] ?? []);
    }

    public function accept(): array
    {
        return $this->props['accept'];
    }

    protected function normalizeAccept($accept = null)
    {
        if (is_string($accept) === true) {
            $accept = [
                'mime' => $accept
            ];
        }

        // accept anything
        if (empty($accept) === true) {
            return [];
        }

        $accept = array_change_key_case($accept);

        $defaults = [
            'mime'        => null,
            'maxheight'   => null,
            'maxsize'     => null,
            'maxwidth'    => null,
            'minheight'   => null,
            'minsize'     => null,
            'minwidth'    => null,
            'orientation' => null
        ];

        return array_merge($defaults, $accept);
    }
}
