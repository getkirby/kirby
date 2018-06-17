<?php

namespace Kirby\Cms;

/**
 * Normalizes site options in the site blueprint
 * and checks for each option, if the current
 * user is allowed to execute it.
 */
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
