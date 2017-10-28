<?php

namespace Kirby\Cms;

class Output
{

    protected $model;
    protected $schema;

    public function __construct(Object $model)
    {

        $this->model  = $model;
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
        return $this->blueprint()->fields();
    }

    public function converter(string $type)
    {
        $schema = $this->schema[$type] ?? [];
        return $schema['output'] ?? null;
    }

    public function toArray(): array
    {

        $fields  = $this->fields();
        $content = $this->content();

        foreach ($fields as $field) {

            $name  = strtolower($field['name']);
            $value = $content[$name] ?? null;

            if ($converter = $this->converter($field['type'] ?? 'text')) {
                $value = $converter($this->model, $name, $value, $field);
            }

            $content[$name] = $value;

        }

        return $content;

    }

}
