<?php

namespace Kirby\Cms;

use Exception;

class Role extends Model
{

    protected $description;
    protected $name;
    protected $title;

    public function __construct(array $props)
    {
        $this->setProperties($props);
    }

    public static function factory(string $name): self
    {
        try {
            $props = Blueprint::load('users/' . $name);
        } catch (Exception $e) {
            if (in_array($name, ['admin', 'nobody'], true) === false) {
                throw new Exception(sprintf('The role "%s" does not exist', $name));
            }

            $props = [
                'name'  => $name,
                'title' => ucfirst($name)
            ];
        }

        return new static($props);
    }

    public function description()
    {
        return $this->description;
    }

    public function id(): string
    {
        return $this->name();
    }

    public function name(): string
    {
        return $this->name;
    }

    protected function setDescription(string $description = null): self
    {
        $this->description = $description;
        return $this;
    }

    protected function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    protected function setTitle($title): self
    {
        $this->title = $title;
        return $this;
    }

    public function title(): string
    {
        return $this->title;
    }

}
