<?php

namespace Kirby\Cms;

class Query
{

    protected $query;
    protected $data;

    public function __construct(string $query, array $data = [])
    {
        $this->query = $query;
        $this->data  = $data;
    }

    public function result()
    {
        $parts = $this->parts($this->query);
        $data  = $this->data;

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

    protected function parts($token)
    {
        $token = trim($token);
        $token = preg_replace_callback('!\((.*?)\)!', function ($match) {
            return '(' . str_replace('.', '@@@', $match[1]) . ')';
        }, $token);

        $parts = explode('.', $token);

        return $parts;

    }

    protected function info($token)
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

                return (float)$arg;
            }, explode(',', $match[1]));

        }, $token);

        return [
            'method' => $method,
            'args'   => $args
        ];
    }

}
