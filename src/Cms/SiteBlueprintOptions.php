<?php

namespace Kirby\Cms;

use Exception;

class SiteBlueprintOptions extends BlueprintOptions
{

    protected $options = [
        'read'   => true,
        'update' => true,
    ];

    public function __construct(Site $model, array $options = null)
    {
        parent::__construct($model, $options);
    }

    public function read(): bool
    {
        return $this->options['read'];
    }

    public function update(): bool
    {
        return $this->options['update'];
    }

}
