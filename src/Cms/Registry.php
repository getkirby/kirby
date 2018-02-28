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
     * Add an entry to the registry
     *
     * @param string $type
     * @param string $key
     * @param mixed $value
     * @return self
     */
    protected function add(string $type, string $key, $value)
    {
        if (array_key_exists($type, $this->entries) === false) {
            throw new Exception(sprintf('Invalid registry entry type "%s"', $type));
        }

        $this->entries[$type][$key] = $value;
        return $this;
    }

    /**
     * @param string $type
     * @param string $key
     * @param mixed $value
     * @return self
     */
    protected function append(string $type, string $key, $value): self
    {
        if (array_key_exists($type, $this->entries) === false) {
            throw new Exception(sprintf('Invalid registry entry type "%s"', $type));
        }

        if (isset($this->entries[$type][$key]) === false) {
            $this->entries[$type][$key] = [];
        }

        $this->entries[$type][$key][] = $value;

        return $this;
    }

    /**
     * Global getter for any registry entry type
     *
     * @return mixed
     */
    public function get(string $type = null, string $key = null)
    {
        if ($type === null) {
            return $this->entries;
        }

        if (array_key_exists($type, $this->entries) === false) {
            throw new Exception(sprintf('Invalid registry entry type "%s"', $type));
        }

        if ($key === null) {
            return $this->entries[$type];
        }

        return $this->entries[$type][$key] ?? null;
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
        return $this->add('blueprint', $name, $file);
    }

    public function setCollection(string $name, Closure $function)
    {
        return $this->add('collection', $name, $function);
    }

    public function setController($name, Closure $function)
    {
        return $this->add('controller', $name, $function);
    }

    public function setField(string $name, string $directory)
    {
        return $this->add('field', $name, $directory);
    }

    public function setFieldMethod($name, Closure $function)
    {
        return $this->add('fieldMethod', $name, $function);
    }

    public function setFileMethod($name, Closure $function)
    {
        return $this->add('fileMethod', $name, $function);
    }

    public function setFilesMethod($name, Closure $function)
    {
        return $this->add('filesMethod', $name, $function);
    }

    public function setHook($name, Closure $function)
    {
        return $this->append('hook', $name, $function);
    }

    public function setPageMethod($name, Closure $function)
    {
        return $this->add('pageMethod', $name, $function);
    }

    public function setPageModel($name, string $className)
    {
        return $this->add('pageModel', $name, $className);
    }

    public function setPagesMethod($name, Closure $function)
    {
        return $this->add('pagesMethod', $name, $function);
    }

    public function setOption(string $name, $value)
    {
        return $this->add('option', $name, $value);
    }

    public function setRoute($name, $route = null)
    {
        if ($route === null) {
            $route = $name;
            $name  = $route['pattern'] ?? null;
        }

        return $this->add('route', $name, $route);
    }

    public function setSiteMethod($name, Closure $function)
    {
        return $this->add('siteMethod', $name, $function);
    }

    public function setSnippet(string $name, string $file)
    {
        return $this->add('snippet', $name, $file);
    }

    public function setTag(string $name, array $tag)
    {
        return $this->add('tag', $name, $tag);
    }

    public function setTemplate($name, string $file)
    {
        return $this->add('template', $name, $file);
    }

    public function setValidator(string $name, Closure $function)
    {
        return $this->add('validator', $name, $function);
    }

    public function setWidget(string $name, string $directory)
    {
        return $this->add('widget', $name, $directory);
    }

}
