<?php

namespace Kirby\Cms;

use Exception;
use Throwable;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Tpl;
use Kirby\Toolkit\View;

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

    public function defaultType(): string
    {
        return 'html';
    }

    public function store(): string
    {
        return 'templates';
    }

    public function file(): ?string
    {
        if ($this->hasDefaultType()) {
            try {
                // Try the default template in the default template directory.
                return F::realpath("{$this->root()}/{$this->name()}.{$this->extension()}", $this->root());
            } catch (Exception $e) {
                //
            }

            $path = App::instance()->extension($this->store(), $this->name());

            if (!is_null($path)) {
                return $path;
            }
        }

        $name = "{$this->name()}.{$this->type()}";

        try {
            // Try the template with type extension in the default template directory.
            return F::realpath("{$this->root()}/{$name}.{$this->extension()}", $this->root());
        } catch (Exception $e) {
            // Finally look for the template with type extension provided by an extension.
            return App::instance()->extension($this->store(), $name);
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
        return App::instance()->root($this->store());
    }

    public function type(): string
    {
        return $this->type;
    }

    public function hasDefaultType(): bool
    {
        $type = $this->type();

        return $type === null || $type === $this->defaultType();
    }
}
