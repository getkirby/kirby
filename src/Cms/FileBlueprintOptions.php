<?php

namespace Kirby\Cms;

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
        return $this->isAllowed('file', 'changeName');
    }

    public function create(): bool
    {
        return $this->isAllowed('file', 'create');
    }

    public function delete(): bool
    {
        return $this->isAllowed('file', 'delete');
    }

    public function replace(): bool
    {
        return $this->isAllowed('file', 'replace');
    }

    public function update(): bool
    {
        return $this->isAllowed('file', 'update');
    }

}
