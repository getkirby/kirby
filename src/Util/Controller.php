<?php

namespace Kirby\Util;

use Closure;
use Exception;
use ReflectionFunction;

class Controller
{

    protected $bind;
    protected $callback;
    protected $data;

    public function __construct(Closure $callback, array $data = [], $bind = null)
    {
        $this->callback = $callback;
        $this->data     = $data;
        $this->bind     = $bind;
    }

    public function arguments(): array
    {
        if (empty($this->data) === true) {
            return [];
        }

        $info = new ReflectionFunction($this->callback);
        $args = [];

        foreach ($info->getParameters() as $parameter) {
            $name = $parameter->getName();

            if (isset($this->data[$name]) === false) {
                throw new Exception(sprintf('The "%s" parameter is missing', $name));
            }

            $args[] = $this->data[$name];
        }

        return $args;
    }

    public function call()
    {
        if ($this->bind === null) {
            return call_user_func($this->callback, ...$this->arguments());
        }

        return $this->callback->call($this->bind, ...$this->arguments());
    }

}
