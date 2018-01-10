<?php

namespace Kirby\Util;

use Closure;
use Exception;
use Kirby\Util\Props;

class Component
{

    public static $loader;

    protected static $components = [];
    protected static $mixins = [];

    protected $_computed;
    protected $_data;
    protected $_definition;
    protected $_methods;
    protected $_options;
    protected $_props;
    protected $_propsData;

    public function __call(string $key, $arguments)
    {
        if (isset($this->_methods[$key]) === true) {
            return $this->trigger($key, ...$arguments);
        }

        return $this->get($key);
    }

    public function __construct($definition, array $propsData = [])
    {
        $definition = static::definition($definition);

        $this->_propsData  = $propsData;
        $this->_methods    = $definition['methods'] ?? [];
        $this->_computed   = $definition['computed'] ?? [];
        $this->_definition = $definition;

        // setup options
        $this->_options = new Props([], $definition, $this);

        // remove unwanted stuff from the options
        $this->_options = $this->_options->not([
            'beforeCreate',
            'computed',
            'created',
            'data',
            'methods',
            'props',
        ]);

        $this->_data  = new Props([], [], $this);
        $this->_props = new Props($definition['props'] ?? [], $propsData, $this, true);

        if (is_a($definition['beforeCreate'] ?? null, Closure::class)) {
            $definition['beforeCreate']->call($this);
        }

        // validate all properties
        $this->_props->validate();

        // set data based on the definition
        if (is_a($this->_definition['data'] ?? null, Closure::class)) {
            $data = $this->_definition['data']->call($this);
        } else {
            $data = [];
        }

        $data = array_merge($this->_props->toArray(), $data);
        $data = $this->computedSet($data);

        $this->_data->set($data);

        if (is_a($this->_definition['created'] ?? null, Closure::class)) {
            $this->_definition['created']->call($this);
        }
    }

    public function __debuginfo(): array
    {
        return $this->toArray();
    }

    public function __get(string $key)
    {
        return $this->get($key);
    }

    public function __set(string $key, $value)
    {
        return $this->set($key, $value);
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    protected static function abstract(): array
    {
        return [];
    }

    protected function computedSet($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $key[$k] = $this->computedSet($k, $v);
            }
            return $key;
        }

        $computer = $this->_computed[$key] ?? null;

        if (is_array($computer) === true && isset($computer['set']) === true) {
            return $computer['set']->call($this, $value);
        }

        return $value;
    }

    public static function create($definition, array $props = [])
    {
        return new static($definition, $props);
    }

    public static function define($name, array $definition = null): array
    {
        if (is_array($name) === true) {
            foreach ($name as $n => $d) {
                static::define($n, $d);
            }
            return static::$components;
        }

        return static::$components[$name] = $definition;
    }

    public static function definition($name)
    {
        if (is_array($name) === true) {
            $definition = $name;
        } else {
            $definition = static::$components[$name] ?? static::load('component', $name);
        }

        if (is_array($definition) === false) {
            throw new Exception('Invalid component definition');
        }

        // lazy-load mixins
        if (isset($definition['mixins'])) {
            foreach ((array)$definition['mixins'] as $mixinName) {
                if ($mixin = static::mixin($mixinName)) {
                    $definition = array_replace_recursive($mixin, $definition);
                } else {
                    throw new Exception(sprintf('Invalid mixin: "%s"', $mixinName));
                }
            }
        }

        // lazy-load extensions
        if (isset($definition['extends'])) {
            $definition = static::extend($definition['extends'], $definition);
        }

        // add the abstract
        $definition = array_replace_recursive(static::abstract(), $definition);

        unset($definition['extends']);
        unset($definition['mixins']);

        return static::$components[$name] = $definition;
    }

    public static function extend(string $extend, array $definition = []): array
    {
        if ($component = static::definition($extend)) {
            return array_replace_recursive($component, $definition);
        }

        throw new Exception(sprintf('The "%s" component is not defined and cannot be extended', $extend));
    }

    public function get(string $key)
    {
        if (isset($this->_computed[$key])) {
            if (is_array($this->_computed[$key])) {
                if (isset($this->_computed[$key]['get']) === true) {
                    $this->_computed[$key]['get']->call($this);
                }
            } else {
                return $this->_computed[$key]->call($this);
            }
        }

        if ($this->_data->has($key) === true) {
            return $this->_data->$key;
        }

        if ($this->_props->has($key) === true) {
            return $this->_props->$key;
        }

        return $this->_options->$key;
    }

    public function hasMethod($method): bool
    {
        return isset($this->_methods[$method]) === true;
    }

    public function hasProp($prop): bool
    {
        return $this->_props->has($prop);
    }

    public function hasOption($option): bool
    {
        return $this->_options->has($option);
    }

    public static function load($type, $name)
    {
        if (is_a(static::$loader, Closure::class) === false) {
            throw new Exception('Undefined component loader');
        }

        return call_user_func(static::$loader, $type, $name);
    }

    public static function mixin(string $name, array $mixin = null)
    {
        if ($mixin !== null) {
            return static::$mixins[$name] = $mixin;
        }

        $definition = static::$mixins[$name] ?? static::load('mixin', $name);

        // lazy-load mixins
        if (isset($definition['extends']) === true) {
            if ($mixin = static::mixin($definition['extends'])) {
                $definition = array_replace_recursive($mixin, $definition);
            } else {
                throw new Exception(sprintf('Invalid mixin: "%s"', $definition['extends']));
            }
        }

        unset($definition['extends']);

        return static::$mixins[$name] = $definition;
    }

    public function set(string $key, $value)
    {
        $value = $this->computedSet($key, $value);

        $this->_props->validate($key, $value);
        $this->_data->set($key, $value);

        // return the component
        return $this;
    }

    public function toArray(): array
    {
        $keys = array_unique(array_merge(
            $this->_options->keys(),
            $this->_props->keys(),
            $this->_data->keys(),
            array_keys($this->_computed)
        ));

        $array = [];

        foreach ($keys as $key) {
            $array[$key] = $this->get($key);
        }

        ksort($array);

        if ($this->hasMethod('toArray') === true) {
            $array = $this->trigger('toArray', $array);
        }

        return $array;
    }

    public function toString(): string
    {
        $string = '';

        if ($this->hasMethod('toString') === true) {
            $string = $this->trigger('toString', $string);
        }

        return $string;
    }

    public function trigger($method, ...$arguments)
    {
        if ($this->hasMethod($method) === false) {
            throw new Exception('The component method does not exist');
        }

        return $this->_methods[$method]->call($this, ...$arguments);
    }

}
