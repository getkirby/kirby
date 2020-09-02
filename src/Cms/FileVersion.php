<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Properties;

/**
 * FileVersion
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class FileVersion
{
    use FileFoundation {
        toArray as parentToArray;
    }
    use Properties;

    protected $modifications;
    protected $original;

    /**
     * Proxy for public properties, asset methods
     * and content field getters
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
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

        if (is_a($this->original(), 'Kirby\Cms\File') === true) {
            // content fields
            return $this->original()->content()->get($method, $arguments);
        }
    }

    /**
     * Returns the unique ID
     *
     * @return string
     */
    public function id(): string
    {
        return dirname($this->original()->id()) . '/' . $this->filename();
    }

    /**
     * Returns the parent Kirby App instance
     *
     * @return \Kirby\Cms\App
     */
    public function kirby()
    {
        return $this->original()->kirby();
    }

    /**
     * Returns an array with all applied modifications
     *
     * @return array
     */
    public function modifications(): array
    {
        return $this->modifications ?? [];
    }

    /**
     * Returns the instance of the original File object
     *
     * @return mixed
     */
    public function original()
    {
        return $this->original;
    }

    /**
     * Applies the stored modifications and
     * saves the file on disk
     *
     * @return self
     */
    public function save()
    {
        $this->kirby()->thumb($this->original()->root(), $this->root(), $this->modifications());
        return $this;
    }

    /**
     * Setter for modifications
     *
     * @param array|null $modifications
     */
    protected function setModifications(array $modifications = null)
    {
        $this->modifications = $modifications;
    }

    /**
     * Setter for the original File object
     *
     * @param $original
     */
    protected function setOriginal($original)
    {
        $this->original = $original;
    }

    /**
     * Converts the object to an array
     *
     * @return array
     */
    public function toArray(): array
    {
        $array = array_merge($this->parentToArray(), [
            'modifications' => $this->modifications(),
        ]);

        ksort($array);

        return $array;
    }
}
