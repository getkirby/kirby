<?php

namespace Kirby\Cms;

use Closure;
use Exception;

class Registry
{

    protected $entries = [
        'blueprint'   => [],
        'collection'  => [],
        'controller'  => [],
        'field'       => [],
        'fieldMethod' => [],
        'fileMethod'  => [],
        'filesMethod' => [],
        'hook'        => [],
        'pageMethod'  => [],
        'pageModel'   => [],
        'pagesMethod' => [],
        'option'      => [],
        'route'       => [],
        'siteMethod'  => [],
        'snippet'     => [],
        'tag'         => [],
        'template'    => [],
        'validator'   => [],
        'widget'      => []
    ];

    /**
     * Internal getter and setter for registry entries
     *
     * @param string $type
     * @param string|array $key
     * @param mixed $value
     * @return mixed
     */
    protected function entry(string $type, $key, $value = null)
    {
        if ($value === null) {
            return $this->entries[$type][$key] ?? null;
        }

        if (array_key_exists($type, $this->entries) === false) {
            throw new Exception(sprintf('Invalid registry entry type "%s"', $type));
        }

        if (is_array($key) === true) {
            foreach ($key as $k) {
                $this->entries[$type][$k] = $value;
            }
            return $this->entries[$type];
        }

        return $this->entries[$type][$key] = $value;
    }

    public function entries($type = null): array
    {
        if ($type === null) {
            return $this->entries;
        }

        return $this->entries[$type] ?? [];
    }

    /**
     * Global getter for any registry entry type
     *
     * @return mixed
     */
    public function get(string $type, string $key)
    {
        return $this->entry($type, $key);
    }

    /**
     * Global setter for any registry entry type
     *
     * @param string $type
     * @param mixed ...$arguments
     * @return mixed
     */
    public function set(string $type, ...$arguments)
    {
        if (method_exists($this, 'set' . $type) === false) {
            throw new Exception(sprintf('Invalid registry entry type "%s"', $type));
        }

        return $this->{'set' . $type}(...$arguments);
    }

    public function setBlueprint($name, string $file)
    {
        return $this->entry('blueprint', $name, $file);
    }

    public function setCollection(string $name, Closure $function)
    {
        return $this->entry('collection', $name, $function);
    }

    public function setController($name, Closure $function)
    {
        return $this->entry('controller', $name, $function);
    }

    public function setField(string $name, string $directory)
    {
        return $this->entry('field', $name, $directory);
    }

    public function setFieldMethod($name, Closure $function)
    {
        return $this->entry('fieldMethod', $name, $function);
    }

    public function setFileMethod($name, Closure $function)
    {
        return $this->entry('fileMethod', $name, $function);
    }

    public function setFilesMethod($name, Closure $function)
    {
        return $this->entry('filesMethod', $name, $function);
    }

    public function setHook($name, Closure $function)
    {
        return $this->entry('hook', $name, $function);
    }

    public function setPageMethod($name, Closure $function)
    {
        return $this->entry('pageMethod', $name, $function);
    }

    public function setPageModel($name, string $className)
    {
        return $this->entry('pageModel', $name, $className);
    }

    public function setPagesMethod($name, Closure $function)
    {
        return $this->entry('pagesMethod', $name, $function);
    }

    public function setOption(string $name, $value)
    {
        return $this->entry('option', $name, $value);
    }

    public function setRoute($name, $route = null)
    {
        if ($route === null) {
            $route = $name;
            $name  = $route['pattern'] ?? null;
        }

        return $this->entry('route', $name, $route);
    }

    public function setSiteMethod($name, Closure $function)
    {
        return $this->entry('siteMethod', $name, $function);
    }

    public function setSnippet(string $name, string $file)
    {
        return $this->entry('snippet', $name, $file);
    }

    public function setTag(string $name, array $tag)
    {
        return $this->entry('tag', $name, $tag);
    }

    public function setTemplate($name, string $file)
    {
        return $this->entry('template', $name, $file);
    }

    public function setValidator(string $name, Closure $function)
    {
        return $this->entry('validator', $name, $function);
    }

    public function setWidget(string $name, string $directory)
    {
        return $this->entry('widget', $name, $directory);
    }

}
