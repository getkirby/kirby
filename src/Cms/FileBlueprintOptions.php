<?php

namespace Kirby\Cms;

class FileBlueprintOptions extends BlueprintOptions
{

    protected $options = [
        'delete'   => true,
        'read'     => true,
        'replace'  => true,
        'update'   => true,
    ];

    public function __construct(File $model, array $options = null)
    {
        parent::__construct($model, $options);
    }

    public function delete(): bool
    {
        return $this->options['delete'];
    }

    public function read(): bool
    {
        return $this->options['read'];
    }

    public function replace(): bool
    {
        return $this->options['replace'];
    }

    public function update(): bool
    {
        return $this->options['update'];
    }

}
