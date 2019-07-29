<?php

namespace Kirby\Toolkit;

/**
 * The Query class can be used to
 * query arrays and objects, including their
 * methods with a very simple string-based syntax.
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Query
{
    const PARTS      = '!([a-zA-Z_]*(\(.*?\))?)\.|' . self::SKIP . '!';
    const METHOD     = '!\((.*)\)!';
    const PARAMETERS = '!,|' . self::SKIP . '!';

    const NO_PNTH = '\([^\(]+\)(*SKIP)(*FAIL)';
    const NO_SQBR = '\[[^]]+\](*SKIP)(*FAIL)';
    const NO_DLQU = '\"[^"]+\"(*SKIP)(*FAIL)';
    const NO_SLQU = '\'[^\']+\'(*SKIP)(*FAIL)';
    const SKIP    = self::NO_PNTH . '|' . self::NO_SQBR . '|' .
                    self::NO_DLQU . '|' . self::NO_SLQU;

    /**
     * The query string
     *
     * @var string
     */
    protected $query;

    /**
     * Queryable data
     *
     * @var array
     */
    protected $data;

    /**
     * Creates a new Query object
     *
     * @param string $query
     * @param array|object $data
     */
    public function __construct(string $query = null, $data = [])
    {
        $this->query = $query;
        $this->data  = $data;
    }

    /**
     * Returns the query result if anything
     * can be found. Otherwise returns null.
     *
     * @return mixed
     */
    public function result()
    {
        if (empty($this->query) === true) {
            return $this->data;
        }

        return $this->resolve($this->query);
    }

    /**
     * Resolves the query if anything
     * can be found. Otherwise returns null.
     *
     * @param string $query
     * @return mixed
     */
    protected function resolve(string $query)
    {
        // direct key access in arrays
        if (is_array($this->data) === true && array_key_exists($query, $this->data) === true) {
            return $this->data[$query];
        }

        $parts = $this->parts($query);
        $data  = $this->data;
        $value = null;

        while (count($parts)) {
            $part   = array_shift($parts);
            $info   = $this->part($part);
            $method = $info['method'];
            $value  = null;

            if (is_array($data)) {
                $value = $data[$method] ?? null;
            } elseif (is_object($data)) {
                if (method_exists($data, $method) || method_exists($data, '__call')) {
                    $value = $data->$method(...$info['args']);
                }
            } elseif (is_scalar($data)) {
                return $data;
            } else {
                return null;
            }

            if (is_array($value) || is_object($value)) {
                $data = $value;
            }
        }

        return $value;
    }

    /**
     * Breaks the query string down into its components
     *
     * @param  string $query
     * @return array
     */
    protected function parts(string $query): array
    {
        $query = trim($query);

        // match all parts but the last
        preg_match_all(self::PARTS, $query, $match);

        // remove all matched parts from the query to retrieve last part
        foreach ($match[0] as $part) {
            $query = Str::after($query, $part);
        }

        array_push($match[1], $query);
        return $match[1];
    }

    /**
     * Analyzes each part of the query string and
     * extracts methods and method arguments.
     *
     * @param  string $part
     * @return array
     */
    protected function part(string $part): array
    {
        $args   = [];
        $method = preg_replace_callback(self::METHOD, function ($match) use (&$args) {
            $args = preg_split(self::PARAMETERS, $match[1]);
            $args = array_map('self::parameter', $args);
        }, $part);

        return [
            'method' => $method,
            'args'   => $args
        ];
    }

    /**
     * Converts a parameter of query to
     * proper type.
     *
     * @param  mixed $arg
     * @return mixed
     */
    protected function parameter($arg)
    {
        $arg = trim($arg);

        // string with double quotes
        if (substr($arg, 0, 1) === '"') {
            return trim($arg, '"');
        }

        // string with single quotes
        if (substr($arg, 0, 1) === '\'') {
            return trim($arg, '\'');
        }

        // boolean or null
        switch ($arg) {
            case 'null':
                return null;
            case 'false':
                return false;
            case 'true':
                return true;
        }

        // numeric
        if (is_numeric($arg) === true) {
            return (float)$arg;
        }

        // array: split and recursive sanitizing
        if (substr($arg, 0, 1) === '[' && substr($arg, -1) === ']') {
            $arg = substr($arg, 1, -1);
            $arg = preg_split(self::PARAMETERS, $arg);
            return array_map('self::parameter', $arg);
        }

        // resolve parameter for objects and methods itself
        return $this->resolve($arg);
    }
}
