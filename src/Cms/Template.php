<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Toolkit\View;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Tpl;
use Throwable;

class Template
{
    public static $data = [];

    protected $name;
    protected $type;

    public function __construct(string $name, string $type = 'html')
    {
        $this->name = strtolower($name);
        $this->type = $type;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function exists(): bool
    {
        return file_exists($this->file());
    }

    public function extension(): string
    {
        return 'php';
    }

    public function file(): ?string
    {
        $type = $this->type();
        $name = $type !== null && $type !== 'html' ? $this->name() . '.' . $type : $this->name();

        try {
            return F::realpath($this->root() . '/' . $name . '.' . $this->extension(), $this->root());
        } catch (Exception $e) {
            return App::instance()->extension('templates', $name);
        }
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * @param array $data
     * @return string
     */
    public function render(array $data = []): string
    {
        return Tpl::load($this->file(), $data);
    }

    public function root(): string
    {
        return App::instance()->root('templates');
    }

    public function type(): string
    {
        return $this->type;
    }
}
