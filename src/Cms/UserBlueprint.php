<?php

namespace Kirby\Cms;

use Kirby\Data\Data;

class UserBlueprint extends Blueprint
{

    public function file()
    {

        if ($this->file !== null) {
            return $this->file;
        }

        $root = App::instance()->root('blueprints') . '/users';
        $this->file = $root . '/' . basename($this->name) . '.yml';

        if (file_exists($this->file) === false) {
            $this->name = 'visitor';
            $this->file = $root . '/' . $this->name . '.yml';
        }

        return $this->file;

    }

    public function fields(): array
    {
        $fields = parent::fields();

        if (isset($fields['name']) === false) {
            $fields['name'] = [
                'name' => 'name',
                'type' => 'text'
            ];
        }

        if (isset($fields['language']) === false) {
            $fields['language'] = [
                'name' => 'language',
                'type' => 'text'
            ];
        }

        return $fields;
    }

}
