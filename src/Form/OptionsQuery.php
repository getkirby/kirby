<?php

namespace Kirby\Form;

use Kirby\Cms\Field;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Toolkit\Collection;
use Kirby\Toolkit\Obj;
use Kirby\Toolkit\Properties;
use Kirby\Toolkit\Query;
use Kirby\Toolkit\Str;

/**
 * Option Queries are run against any set
 * of data. In case of Kirby, you can query
 * pages, files, users or structures to create
 * options out of them.
 *
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class OptionsQuery
{
    use Properties;

    /**
     * @var array
     */
    protected $aliases = [];

    /**
     * @var array
     */
    protected $data;

    /**
     * @var array|string|null
     */
    protected $options;

    /**
     * @var string
     */
    protected $query;

    /**
     * @var mixed
     */
    protected $text;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * OptionsQuery constructor
     *
     * @param array $props
     */
    public function __construct(array $props)
    {
        $this->setProperties($props);
    }

    /**
     * @return array
     */
    public function aliases(): array
    {
        return $this->aliases;
    }

    /**
     * @return array
     */
    public function data(): array
    {
        return $this->data;
    }

    /**
     * @param string $object
     * @param string $field
     * @param array $data
     * @return string
     * @throws \Kirby\Exception\NotFoundException
     */
    protected function template(string $object, string $field, array $data)
    {
        $value = $this->$field();

        if (is_array($value) === true) {
            if (isset($value[$object]) === false) {
                throw new NotFoundException('Missing "' . $field . '" definition');
            }

            $value = $value[$object];
        }

        return Str::safeTemplate($value, $data);
    }

    /**
     * @return array
     */
    public function options(): array
    {
        if (is_array($this->options) === true) {
            return $this->options;
        }

        $data    = $this->data();
        $query   = new Query($this->query(), $data);
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

    /**
     * @return string
     */
    public function query(): string
    {
        return $this->query;
    }

    /**
     * @param $object
     * @return mixed|string|null
     */
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

    /**
     * @param $result
     * @throws \Kirby\Exception\InvalidArgumentException
     */
    protected function resultToCollection($result)
    {
        if (is_array($result)) {
            foreach ($result as $key => $item) {
                if (is_scalar($item) === true) {
                    $result[$key] = new Obj([
                        'key'   => new Field(null, 'key', $key),
                        'value' => new Field(null, 'value', $item),
                    ]);
                }
            }

            $result = new Collection($result);
        }

        if (is_a($result, 'Kirby\Toolkit\Collection') === false) {
            throw new InvalidArgumentException('Invalid query result data');
        }

        return $result;
    }

    /**
     * @param array|null $aliases
     * @return $this
     */
    protected function setAliases(?array $aliases = null)
    {
        $this->aliases = $aliases;
        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    protected function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param array|string|null $options
     * @return $this
     */
    protected function setOptions($options = null)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @param string $query
     * @return $this
     */
    protected function setQuery(string $query)
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @param mixed $text
     * @return $this
     */
    protected function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    protected function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function text()
    {
        return $this->text;
    }

    public function toArray(): array
    {
        return $this->options();
    }

    /**
     * @return mixed
     */
    public function value()
    {
        return $this->value;
    }
}
