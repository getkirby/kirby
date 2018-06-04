<?php

namespace Kirby\Form;

use Kirby\Cms\ContentField;
use Kirby\Cms\Query;
use Kirby\Cms\Tempura;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Toolkit\Collection;
use Kirby\Toolkit\Obj;
use Kirby\Toolkit\Properties;
use Kirby\Toolkit\Str;

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

    protected function template(string $object, string $field, array $data)
    {
        $value = $this->$field();

        if (is_array($value) === true) {
            if (isset($value[$object]) === false) {
                throw new NotFoundException('Missing "' . $field . '" definition');
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
        $result  = $this->resultToCollection($result);
        $options = [];

        foreach ($result as $item) {
            $alias = $this->resolve($item);
            $data  = array_merge($data, [$alias => $item]);

            $options[] = [
                'text'  => $this->template($alias, 'text', $data),
                'value' => $this->template($alias, 'value', $data)
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

        return 'item';
    }

    protected function resultToCollection($result)
    {
        if (is_array($result)) {
            foreach ($result as $key => $item) {
                if (is_scalar($item) === true) {
                    $result[$key] = new Obj([
                        'key'   => new ContentField('key', $key),
                        'value' => new ContentField('value', $item),
                    ]);
                }
            }

            $result = new Collection($result);
        }

        if (is_a($result, Collection::class) === false) {
            throw new InvalidArgumentException('Invalid query result data');
        }

        return $result;
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
