<?php

namespace Kirby\Cms;

class Role extends Model
{

    protected $name;
    protected $title;

    public function __construct(array $props)
    {
        $this->setProperties($props);
    }

    public static function factory(string $name): self
    {
        $props = Blueprint::load('users/' . $name);
        return new static($props);
    }

    public function name(): string
    {
        return $this->name;
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
