<?php

namespace Kirby\Cms;

use Kirby\Data\Data;

class Blueprint
{

    protected $data;
    protected $name;
    protected $file;

    public function __construct(string $name = 'default')
    {
        $this->name = $name;
        $this->file = $this->file();
        $this->data = $this->data();
    }

    public function file()
    {

        if ($this->file !== null) {
            return $this->file;
        }

        $root = App::instance()->root('blueprints');
        $this->file = $root . '/' . $this->name . '.yml';

        if (file_exists($this->file) === false) {
            $this->name = 'default';
            $this->file = $root . '/default.yml';
        }

        return $this->file;

    }

    public function data()
    {
        if ($this->data !== null) {
            return $this->data;
        }

        $this->data = Data::read($this->file());
        $this->data['name'] = $this->name;

        return $this->data;
    }

    public function layout(): array
    {
        return $this->data()['layout'] ?? [];
    }

    public function sections($type = null): array
    {
        $sections = [];

        foreach ($this->layout() as $column) {
            foreach (($column['sections'] ?? []) as $section) {
                if ($type !== null && $section['type'] !== $type) {
                    continue;
                }
                $sections[] = $section;
            }
        }

        return $sections;
    }

    public function isDefault(): bool
    {
        return pathinfo($this->file(), PATHINFO_FILENAME) === 'default';
    }

    public function fields(): array
    {
        $fields = [];

        foreach ($this->sections('fields') as $section) {
            $fields = array_merge($fields, $section['fields'] ?? []);
        }

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

    public function blueprints(): array
    {
        $templates = [];

        foreach ($this->sections('pages') as $section) {

            $sectionTemplates = $section['template'] ?? 'default';

            if (is_array($sectionTemplates) === true) {
                $templates = array_merge($templates, $sectionTemplates);
            } elseif (is_string($sectionTemplates)) {
                $templates[] = $sectionTemplates;
            }

        }

        return array_unique($templates);

    }

    public function toArray(): array
    {
        return $this->data();
    }

}
