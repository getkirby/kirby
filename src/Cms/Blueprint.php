<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Data\Data;

class Blueprint extends BlueprintObject
{
    protected $sections = null;
    protected $tabs = null;

    public function schema(): array
    {
        return [
            'title' => [
                'type'     => 'string',
                'required' => true
            ],
            'name' => [
                'type'     => 'string',
                'required' => true
            ],
            'options' => [
                'type'     => 'array',
                'required' => false
            ],
            'tabs' => [
                'type'     => 'array',
                'required' => true
            ]
        ];
    }

    public function isDefault(): bool
    {
        return $this->name() === 'default';
    }

    public function tabs(): array
    {
        if (is_array($this->tabs) === true) {
            return $this->tabs;
        }

        $this->tabs = [];

        foreach ($this->prop('tabs') as $tab) {
            $tab = new BlueprintTab($tab);
            $this->tabs[$tab->name()] = $tab;
        }

        return $this->tabs;
    }

    public function tab(string $name)
    {
        return $this->tabs()[$name] ?? null;
    }

    public function sections(): array
    {
        if (is_array($this->sections) === true) {
            return $this->sections;
        }

        $this->sections = [];

        foreach ($this->tabs() as $tab) {
            foreach ($tab->sections() as $section) {
                $this->sections[$section->name()] = $section;
            }
        }

        return $this->sections;
    }

    public function section(string $name)
    {
        return $this->sections()[$name] ?? null;
    }

    public function toArray(): array
    {
        $tabs = array_map(function ($tab) {
            return $tab->toArray();
        }, $this->tabs());

        return array_merge(parent::toArray(), [
            'tabs' => $tabs
        ]);
    }

    public function toLayout(): array
    {
        $layout = [];

        foreach ($this->tabs() as $tab) {
            $layout[] = $tab->toLayout();
        }

        return $layout;
    }

    public static function load(string $file): self
    {
        if (is_file($file) === false) {
            throw new Exception('The blueprint cannot be found');
        }

        $data         = Data::read($file);
        $data['name'] = pathinfo($file, PATHINFO_FILENAME);

        return new static($data);
    }

}
