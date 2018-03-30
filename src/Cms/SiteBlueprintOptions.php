<?php

namespace Kirby\Cms;

class SiteBlueprintOptions extends BlueprintOptions
{

    protected $options = [
        'update' => null,
    ];

    public function __construct(Site $model, array $options = null)
    {
        parent::__construct($model, $options);
    }

    public function update(): bool
    {
        return $this->isAllowed('site', 'update');
    }

}
