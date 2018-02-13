<?php

namespace Kirby\Cms;

class FileBlueprintOptions extends BlueprintOptions
{

    protected $options = [
        'delete'   => true,
        'edit'     => true,
        'read'     => true,
        'replace'  => true,
    ];

    public function __construct(File $model, array $options = null)
    {
        parent::__construct($model, $options);
    }

    public function delete(): bool
    {
        return $this->options['delete'];
    }

    public function edit(): bool
    {
        return $this->options['edit'];
    }

    public function read(): bool
    {
        return $this->options['read'];
    }

    public function replace(): bool
    {
        return $this->options['replace'];
    }

}
