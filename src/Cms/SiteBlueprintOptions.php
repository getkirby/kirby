<?php

namespace Kirby\Cms;

use Exception;

class SiteBlueprintOptions extends BlueprintOptions
{

    protected $options = [
        'edit' => true,
        'read' => true,
    ];

    public function __construct(Site $model, array $options = null)
    {
        parent::__construct($model, $options);
    }

    public function edit(): bool
    {
        return $this->options['edit'];
    }

    public function read(): bool
    {
        return $this->options['read'];
    }

}
