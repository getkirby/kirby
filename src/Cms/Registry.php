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
     * Creates a new Registry instance
     * and also imports every entry that
     * is being passed
     *
     * @param array $import
     */
    public function __construct(array $import = null)
    {
        $this->import($import);
    }

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
     * Imports an entire set of registry entries
     *
     * [
     *   'controller' => [
     *     'foo' => function () {}
     *   ],
     *   'collection' => [
     *     'bar' => function () {}
     *   ]
     * ]
     *
     * @param array $array
     * @return self
     */
    public function import(array $import = null)
    {
        if (is_array($import) === false) {
            return $this;
        }

        foreach ($import as $type => $entries) {
            foreach ($entries as $name => $entry) {
                if (is_string($name)) {
                    $this->set($type, $name, $entry);
                } else {
                    $this->set($type, $entry);
                }
            }
        }

        return $this;
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

    public function setHook($name, $function)
    {
        if (is_a($function, Closure::class) === true) {
            return $this->append('hook', $name, $function);
        }

        if (is_array($function) === true) {
            foreach ($function as $func) {
                $this->setHook($name, $func);
            }
            return $this;
        }

        throw new Exception('Invalid hook format');
    }

    public function setPageModel($name, string $className)
    {
        return $this->add('pageModel', $name, $className);
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

}
