<?php

namespace Kirby\Cms;

/**
 * Extension of the basic blueprint class
 * to handle all blueprints for files.
 */
class FileBlueprint extends Blueprint
{
    protected $accept = [];

    public function accept(): array
    {
        return $this->accept;
    }

    public function options()
    {
        if (is_a($this->options, 'Kirby\Cms\FileBlueprintOptions') === true) {
            return $this->options;
        }

        return $this->options = new FileBlueprintOptions($this->model, $this->options);
    }

    protected function setAccept(array $accept = null)
    {
        // accept anything
        if (empty($accept) === true) {
            return $this;
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

        $this->accept = array_merge($defaults, $accept);
        return $this;
    }
}
