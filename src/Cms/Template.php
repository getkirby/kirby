<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Toolkit\View;
use Kirby\Toolkit\F;

class Template extends View
{
    protected static $type = 'template';
    protected static $globals = [];
    protected $name;

    public function __construct(string $name, array $data = [], string $contentType = null)
    {
        $this->data = $data;
        $this->name = strtolower($name);

        if ($contentType !== null && $contentType !== 'html') {
            $this->name .= '.' . $contentType;
        }
    }

    public function data(): array
    {
        return array_merge(static::$globals, $this->data);
    }

    public function extension(): string
    {
        return 'php';
    }

    public function file()
    {
        try {
            return F::realpath($this->root() . '/' . $this->name() . '.' . $this->extension(), $this->root());
        } catch (Exception $e) {
            // try to load the template from the registry
            return App::instance()->extension(static::$type . 's', $this->name());
        }
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
        return sprintf('The %s "%s" cannot be found', static::$type, $this->name());
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
        return App::instance()->root(static::$type . 's');
    }
}
