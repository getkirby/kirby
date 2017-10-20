<?php

namespace Kirby\Cms;

use Kirby\Cms\Blueprint\Converter;
use Kirby\Data\Data;

class Blueprint
{

    protected $root;
    protected $name;
    protected $file;

    public function __construct(string $root, string $name)
    {
        $this->root = $root;
        $this->name = $name;
        $this->file = $this->root . '/' . $name . '.yml';

        if (file_exists($this->file) === false) {
            $this->name = 'default';
            $this->file = $this->root . '/default.yml';
        }

    }

    public function root(): string
    {
        return $this->root;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function file(): string
    {
        return $this->file;
    }

    public function isDefault(): bool
    {
        return $this->name === 'default';
    }

    public function toArray(): array
    {

        $data = ['name' => $this->name] + Data::read($this->file);

        // Kirby 2 blueprint
        if (!isset($data['layout'])) {
            $data = Converter::convert($data);
        }

        return $data;
    }

}
