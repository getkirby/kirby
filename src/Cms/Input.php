<?php

namespace Kirby\Cms;

class Input
{

    protected $model;
    protected $schema;
    protected $input;

    public function __construct(Object $model, array $input = [])
    {

        $this->model  = $model;
        $this->input  = $input;
        $this->schema = App::instance()->schema();

        if (is_a($model->content(), Content::class) === false) {
            throw new Exception('Invalid content');
        }

    }

    public function model()
    {
        return $this->model;
    }

    public function schema()
    {
        return $this->schema;
    }

    public function content(): array
    {
        return array_change_key_case($this->model->content()->toArray(), CASE_LOWER);
    }

    public function blueprint()
    {
        switch (get_class($this->model)) {
            case Site::class:
                return new SiteBlueprint();
            case Page::class:
                return new PageBlueprint($this->model->template());
            case File::class:
                return new FileBlueprint($this->model);
        }
    }

    public function fields(): array
    {
        $fields = $this->blueprint()->fields();

        if (isset($fields['title']) === false) {
            $fields['title'] = [
                'name' => 'title',
                'type' => 'text'
            ];
        }

        return $fields;
    }

    public function converter(string $type)
    {
        $schema = $this->schema[$type] ?? [];
        return $schema['input'] ?? null;
    }

    public function toArray(): array
    {

        $fields  = $this->fields();
        $content = [];

        foreach ($this->input as $name => $value) {

            $name  = strtolower($name);
            $field = $fields[$name] ?? null;

            if ($field === null) {
                continue;
            }

            if ($converter = $this->converter($field['type'] ?? 'text')) {
                $value = $converter($this->model, $name, $value, $field);
            }

            $content[$name] = $value;

        }

        return $content;

    }

}
