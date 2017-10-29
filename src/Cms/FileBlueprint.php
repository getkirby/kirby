<?php

namespace Kirby\Cms;

class FileBlueprint
{

    protected $file;
    protected $data = [];

    public function __construct(File $file)
    {
        $this->file = $file;
        $this->data = $this->parse();
    }

    protected function parse(): array
    {

        $parent   = new Blueprint($this->file->page()->template());
        $sections = $parent->sections('files');

        foreach ($sections as $section) {
            if (isset($section['group']) && $this->file->group()->value() !== $section['group']) {
                continue;
            }

            return $section;
        }

        return [];

    }

    public function data(): array
    {
        return $this->data;
    }

    public function fields(): array
    {
        return (new Fields($this->file, $this->data['fields'] ?? []))->toArray();
    }

    public function toArray()
    {
        $data = $this->data;
        $data['fields'] = array_values($this->fields());

        return $data;
    }

}
