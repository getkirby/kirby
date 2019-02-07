<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Properties;

class FileVersion
{
    use FileFoundation;
    use Properties;

    protected $modifications;
    protected $original;

    public function __call(string $method, array $arguments = [])
    {
        // public property access
        if (isset($this->$method) === true) {
            return $this->$method;
        }

        // asset method proxy
        if (method_exists($this->asset(), $method)) {
            if ($this->exists() === false) {
                $this->save();
            }

            return $this->asset()->$method(...$arguments);
        }

        if (is_a($this->original(), File::class) === true) {
            // content fields
            return $this->original()->content()->get($method, $arguments);
        }
    }

    public function id(): string
    {
        return dirname($this->original()->id()) . '/' . $this->filename();
    }

    public function kirby(): App
    {
        return $this->original()->kirby();
    }

    public function modifications(): array
    {
        return $this->modifications ?? [];
    }

    public function original()
    {
        return $this->original;
    }

    public function save()
    {
        $this->kirby()->thumb($this->original()->root(), $this->root(), $this->modifications());
        return $this;
    }

    protected function setModifications(array $modifications = null)
    {
        $this->modifications = $modifications;
    }

    protected function setOriginal($original)
    {
        $this->original = $original;
    }

    /**
     * Convert the object to an array
     *
     * @return array
     */
    public function toArray(): array
    {
        $array = array_merge(parent::toArray(), [
            'modifications' => $this->modifications(),
        ]);

        ksort($array);

        return $array;
    }
}
