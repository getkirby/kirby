<?php

namespace Kirby\Cms;

/**
 * Extension of the basic blueprint class
 * to handle all blueprints for files.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
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
                'read'       => null,
                'replace'    => null,
                'update'     => null,
            ]
        );

        // normalize the accept settings
        $this->props['accept'] = $this->normalizeAccept($this->props['accept'] ?? []);

        // default values for security props
        if (isset($this->props['createMedia']) !== true) {
            $this->props['createMedia'] = true;
        }

        if (isset($this->props['protect']) !== true) {
            $this->props['protect'] = false;
        }

        // file protection always implies protection against publishing
        if ($this->props['protect'] === true) {
            $this->props['createMedia'] = false;
        }
    }

    /**
     * @return array
     */
    public function accept(): array
    {
        return $this->props['accept'];
    }

    /**
     * @return bool
     */
    public function createMedia(): bool
    {
        return $this->props['createMedia'];
    }

    /**
     * @param mixed $accept
     * @return array
     */
    protected function normalizeAccept($accept = null): array
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

    /**
     * @return bool
     */
    public function protect(): bool
    {
        return $this->props['protect'];
    }
}
