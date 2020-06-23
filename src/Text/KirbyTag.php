<?php

namespace Kirby\Text;

use Kirby\Exception\BadMethodCallException;
use Kirby\Exception\InvalidArgumentException;

/**
 * Representation and parse of a single KirbyTag.
 *
 * @package   Kirby Text
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
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
            $attrName = strtolower($attrName);
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
        $attr = strtolower($attr);
        return $this->$attr ?? null;
    }

    public function attr(string $name, $default = null)
    {
        $name = strtolower($name);
        return $this->$name ?? $default;
    }

    public static function factory(...$arguments)
    {
        return (new static(...$arguments))->render();
    }

    /**
     * @param string $string
     * @param array $data
     * @param array $options
     * @return self
     */
    public static function parse(string $string, array $data = [], array $options = [])
    {
        // remove the brackets, extract the first attribute (the tag type)
        $tag  = trim(ltrim($string, '('));

        // use substr instead of rtrim to keep non-tagged brackets
        // (link: file.pdf text: Download (PDF))
        if (substr($tag, -1) === ')') {
            $tag = substr($tag, 0, -1);
        }

        $type = trim(substr($tag, 0, strpos($tag, ':')));
        $type = strtolower($type);
        $attr = static::$types[$type]['attr'] ?? [];

        // the type should be parsed as an attribute, so we add it here
        // to the list of possible attributes
        array_unshift($attr, $type);

        // extract all attributes
        $regex = sprintf('/(%s):/i', implode('|', $attr));
        $search = preg_split($regex, $tag, false, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        // $search is now an array with alternating keys and values
        // convert it to arrays of keys and values
        $chunks = array_chunk($search, 2);
        $keys   = array_column($chunks, 0);
        $values = array_map('trim', array_column($chunks, 1));

        // ensure that there is a value for each key
        // otherwise combining won't work
        if (count($values) < count($keys)) {
            $values[] = '';
        }

        // combine the two arrays to an associative array
        $attributes = array_combine($keys, $values);

        // the first attribute is the type attribute
        // extract and pass its value separately
        $value = array_shift($attributes);

        return new static($type, $value, $attributes, $data, $options);
    }

    public function option(string $key, $default = null)
    {
        return $this->options[$key] ?? $default;
    }

    public function render(): string
    {
        $callback = static::$types[$this->type]['html'] ?? null;

        if (is_a($callback, 'Closure') === true) {
            return (string)$callback($this);
        }

        throw new BadMethodCallException('Invalid tag render function in tag: ' . $this->type);
    }

    public function type(): string
    {
        return $this->type;
    }
}
