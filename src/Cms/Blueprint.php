<?php

namespace Kirby\Cms;

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

        if (file_exists($this->file) === false && $name !== 'site') {
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

    protected function defaultSiteBlueprint(): array
    {
        return [
            'name'   => 'site',
            'title'  => 'Site',
            'layout' => [
                [
                    'width'    => '1/1',
                    'sections' => [
                        [
                            'headline' => 'Pages',
                            'type'     => 'pages',
                            'parent'   => '/'
                        ]
                    ]
                ]
            ]
        ];
    }

    public function toArray(): array
    {

        // fallback for missing site blueprints
        if ($this->name === 'site' && file_exists($this->file) === false) {
            return $this->defaultSiteBlueprint();
        }

        $data = ['name' => $this->name] + Data::read($this->file);

        // Kirby 2 blueprint
        if (!isset($data['layout'])) {
            $data = BlueprintConverter::convert($data);
        }

        return $data;
    }

}
