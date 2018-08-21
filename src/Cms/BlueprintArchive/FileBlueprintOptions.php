<?php

namespace Kirby\Cms;

/**
 * Normalizes file options in file blueprints
 * and checks for each option, if the current
 * user is allowed to execute it.
 */
class FileBlueprintOptions extends BlueprintOptions
{
    protected $options = [
        'changeName' => null,
        'create'     => null,
        'delete'     => null,
        'replace'    => null,
        'update'     => null,
    ];

    public function __construct(File $model, array $options = null)
    {
        parent::__construct($model, $options);
    }

    public function changeName(): bool
    {
        return $this->isAllowed('files', 'changeName');
    }

    public function create(): bool
    {
        return $this->isAllowed('files', 'create');
    }

    public function delete(): bool
    {
        return $this->isAllowed('files', 'delete');
    }

    public function replace(): bool
    {
        return $this->isAllowed('files', 'replace');
    }

    public function update(): bool
    {
        return $this->isAllowed('files', 'update');
    }
}
