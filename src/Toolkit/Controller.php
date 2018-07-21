<?php

namespace Kirby\Toolkit;

use Closure;
use Exception;
use ReflectionFunction;

/**
 * A smart extension of Closures with
 * magic dependency injection based on the
 * defined variable names.
 */
class Controller
{
    protected $function;

    public function __construct(Closure $function)
    {
        $this->function = $function;
    }

    public function arguments(array $data = []): array
    {
        if (empty($data) === true) {
            return [];
        }

        $info = new ReflectionFunction($this->function);
        $args = [];

        foreach ($info->getParameters() as $parameter) {
            $name = $parameter->getName();

            if (isset($data[$name]) === false) {
                throw new Exception(sprintf('The "%s" parameter is missing', $name));
            }

            $args[] = $data[$name];
        }

        return $args;
    }

    public function call($bind = null, $data = [])
    {
        if ($bind === null) {
            return call_user_func($this->function, ...$this->arguments($data));
        }

        return $this->function->call($bind, ...$this->arguments($data));
    }

    public static function load(string $file)
    {
        if (file_exists($file) === false) {
            return null;
        }

        $function = require $file;

        if (is_a($function, 'Closure') === false) {
            return null;
        }

        return new static($function);
    }
}
