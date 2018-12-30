<?php

namespace Kirby\Toolkit;

/**
 * The Query class can be used to
 * query arrays and objects, including their
 * methods with a very simple string-based syntax.
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 */
class Query
{

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
     * @param array  $data
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

        $parts = $this->parts($this->query);
        $data  = $this->data;
        $value = null;

        while (count($parts)) {
            $part   = array_shift($parts);
            $info   = $this->info($part);
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
     * @param  string $token
     * @return array
     */
    protected function parts(string $token): array
    {
        $token = trim($token);
        $token = preg_replace_callback('!\((.*?)\)!', function ($match) {
            return '(' . str_replace('.', '@@@', $match[1]) . ')';
        }, $token);

        $parts = explode('.', $token);

        return $parts;
    }

    /**
     * Analyzes each part of the query string and
     * extracts methods and method arguments.
     *
     * @param  string $token
     * @return array
     */
    protected function info(string $token): array
    {
        $args   = [];
        $method = preg_replace_callback('!\((.*?)\)!', function ($match) use (&$args) {
            $args = array_map(function ($arg) {
                $arg = trim($arg);
                $arg = str_replace('@@@', '.', $arg);

                if (substr($arg, 0, 1) === '"') {
                    return trim($arg, '"');
                }

                if (substr($arg, 0, 1) === '\'') {
                    return trim($arg, '\'');
                }

                switch ($arg) {
                    case 'null':
                        return null;
                    case 'false':
                        return false;
                    case 'true':
                        return true;
                }

                if (is_numeric($arg) === true) {
                    return (float)$arg;
                }

                return $arg;
            }, str_getcsv($match[1], ','));
        }, $token);

        return [
            'method' => $method,
            'args'   => $args
        ];
    }
}
