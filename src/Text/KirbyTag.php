<?php

namespace Kirby\Text;

use Closure;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\BadMethodCallException;

/**
 * Representation and parse of a single KirbyTag.
 */
class KirbyTag
{
    public static $aliases = [];
    public static $types = [];

    public $attrs = [];
    public $data = [];
    public $options = [];
    public $type  = null;
    public $value = null;

    public function __call(string $name, array $arguments = [])
    {
        return $this->data[$name] ?? $this->$name;
    }

    public static function __callStatic(string $type, array $arguments = [])
    {
        return (new static($type, ...$arguments))->render();
    }

    public function __construct(string $type, string $value = null, array $attrs = [], array $data = [], array $options = [])
    {
        if (isset(static::$types[$type]) === false) {
            if (isset(static::$aliases[$type]) === false) {
                throw new InvalidArgumentException('Undefined tag type: ' . $type);
            }

            $type = static::$aliases[$type];
        }

        foreach ($attrs as $attrName => $attrValue) {
            $this->$attrName = $attrValue;
        }

        $this->attrs   = $attrs;
        $this->data    = $data;
        $this->options = $options;
        $this->$type   = $value;
        $this->type    = $type;
        $this->value   = $value;
    }

    public function __get(string $attr)
    {
        return null;
    }

    public function attr(string $name, $default = null)
    {
        return $this->$name ?? $default;
    }

    public static function factory(...$arguments)
    {
        return (new static(...$arguments))->render();
    }

    public static function parse(string $string, array $data = [], array $options = []): string
    {
        // remove the brackets
        $tag  = trim(rtrim(ltrim($string, '('), ')'));
        $type = trim(substr($tag, 0, strpos($tag, ':')));
        $attr = static::$types[$type]['attr'] ?? [];

        array_unshift($attr, $type);

        // extract all attributes
        $search = preg_split('!(' . implode('|', $attr) . '):!i', $tag, false, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $num    = 0;

        $attributes = [];

        foreach ($search as $key) {
            if (!isset($search[$num + 1])) {
                break;
            }
            $key   = trim($search[$num]);
            $value = trim($search[$num + 1]);

            $attributes[$key] = $value;
            $num = $num + 2;
        }

        $value = array_shift($attributes);

        return (new static($type, $value, $attributes, $data, $options))->render();
    }

    public function option(string $key, $default = null)
    {
        return $this->options[$key] ?? $default;
    }

    public function render()
    {
        if (is_a(static::$types[$this->type]['html'], 'Closure') === true) {
            return static::$types[$this->type]['html']($this, $this);
        }

        throw new BadMethodCallException('Invalid tag render function in tag: ' . $this->type);
    }

    public function type(): string
    {
        return $this->type;
    }
}
