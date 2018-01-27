<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Toolkit\View;
use Kirby\Util\F;

class Template extends View
{

    protected static $globals = [];
    protected $name;

    public function __construct(string $name, array $data = [], string $contentType = null)
    {

        $this->data = $data;
        $this->name = strtolower($name);

        if ($contentType !== null && $contentType !== 'html') {
            $this->name .= '.' . $contentType;
        }

        try {
            $this->file = F::realpath($this->root() . '/' . $this->name . '.php', $this->root());
        } catch (Exception $e) {
            $this->file = false;
        }

    }

    public function data(): array
    {
        return array_merge(static::$globals, $this->data);
    }

    public static function globals(array $globals = null): array
    {
        if ($globals === null) {
            return static::$globals;
        }

        return static::$globals = $globals;
    }

    protected function missingViewMessage(): string
    {
        return sprintf('The template "%s" cannot be found', $this->name());
    }

    public function name(): string
    {
        return $this->name;
    }

    public function render(): string
    {
        return parent::render();
    }

    public function root(): string
    {
        return App::instance()->root('templates');
    }

}
