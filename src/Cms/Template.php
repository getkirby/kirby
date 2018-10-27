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
    protected $defaultType;

    public function __construct(string $name, string $type = 'html', string $defaultType = 'html')
    {
        $this->name = strtolower($name);
        $this->type = $type;
        $this->defaultType = $defaultType;
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
        return $this->defaultType;
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

            // Look for the default template provided by an extension.
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
            // Look for the template with type extension provided by an extension.
            // This might be null if the template does not exist.
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
