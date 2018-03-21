<?php

namespace Kirby\Cms;

class FileBlueprintOptions extends BlueprintOptions
{

    protected $options = [
        'changeName' => true,
        'create'     => true,
        'delete'     => true,
        'replace'    => true,
        'update'     => true,
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
