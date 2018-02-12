<?php

namespace Kirby\Form;

use Exception;
use Kirby\Cms\Query;
use Kirby\Cms\Tempura;
use Kirby\Collection\Collection;
use Kirby\Util\Properties;

class OptionsQuery
{

    use Properties;

    protected $aliases = [];
    protected $data;
    protected $options;
    protected $query;
    protected $text;
    protected $value;

    public function __construct(array $props)
    {
        $this->setProperties($props);
    }

    public function aliases(): array
    {
        return $this->aliases;
    }

    public function data(): array
    {
        return $this->data;
    }

    protected function field(string $object, string $field, array $data)
    {
        $value = $this->$field();

        if (is_array($value) === true) {
            if (isset($value[$object]) === false) {
                throw new Exception('Missing "' . $field . '" definition');
            }

            $value = $value[$object];
        }

        return (new Tempura($value, $data))->render();
    }

    public function options(): array
    {
        if (is_array($this->options) === true) {
            return $this->options;
        }

        $data    = $this->data();
        $query   = new Query($this->query(), $this->data());
        $result  = $query->result();
        $options = [];

        if (is_a($result, Collection::class) === false) {
            throw new Exception('Invalid query result data');
        }

        foreach ($result as $item) {

            $alias = $this->resolve($item);
            $data  = array_merge($data, [$alias => $item]);

            $options[] = [
                'text'  => $this->field($alias, 'text', $data),
                'value' => $this->field($alias, 'value', $data)
            ];
        }

        return $this->options = $options;
    }

    public function query(): string
    {
        return $this->query;
    }

    public function resolve($object)
    {
        // fast access
        if ($alias = ($this->aliases[get_class($object)] ?? null)) {
            return $alias;
        }

        // slow but precise resolving
        foreach ($this->aliases as $className => $alias) {
            if (is_a($object, $className) === true) {
                return $alias;
            }
        }

        throw new Exception('The object class could not be resolved');
    }

    protected function setAliases(array $aliases = null)
    {
        $this->aliases = $aliases;
        return $this;
    }

    protected function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    protected function setQuery(string $query)
    {
        $this->query = $query;
        return $this;
    }

    protected function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    protected function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    public function text()
    {
        return $this->text;
    }

    public function toArray(): array
    {
        return $this->options();
    }

    public function value()
    {
        return $this->value;
    }

}
