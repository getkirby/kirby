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
        $fields   = [];

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
        $fields = $this->data['fields'] ?? [];
        $result = [];

        foreach ($fields as $field) {
            $field['name'] = strtolower($field['name'] ?? null);

            if ($field['name'] === null) {
                continue;
            }

            $result[$field['name']] = $field;
        }

        return $result;
    }

    public function toArray()
    {
        $data = $this->data;
        $data['fields'] = $this->fields();

        return $data;
    }

}
