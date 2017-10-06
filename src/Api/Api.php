<?php

namespace Kirby\Api;

use Kirby\Object\Attributes;
use Kirby\Http\Request;
use Kirby\Http\Router;
use Kirby\Http\Router\Route;
use Kirby\Toolkit\DI\Dependencies;
use Exception;

class Api
{

    protected $attributes;

    public function __construct(array $attributes)
    {

        $this->attributes = Attributes::create($attributes, [
            'request' => [
                'type'     => Request::class,
                'required' => true
            ],
            'path' => [
                'type' => 'string'
            ],
            'routes' => [
                'type'     => 'array',
                'required' => true
            ],
            'data' => [
                'type'     => 'array',
                'required' => true
            ],
            'types' => [
                'type' => 'array',
            ]
        ]);

    }

    public function router()
    {

        $router = new Router;

        foreach ($this->attributes['routes'] as $route) {
            $router->register(new Route($route['pattern'], $route['method'] ?? 'GET', $route['action']));
        }

        return $router;

    }

    public function call($path, $method = 'GET')
    {
        $result = $this->router()->find($path, $method);

        return $result->action()->call($this, ...$result->arguments());
    }

    public function output(string $type, $object, $arguments = []): array
    {

        if (isset($this->attributes['types'][$type]) === false) {
            throw new Exception(sprintf('Invalid output type "%s"', $type));
        }

        if ($object === null) {
            throw new Exception(sprintf('Missing "%s" object', $type));
        }

        return $this->attributes['types'][$type]->call($this, ...[$object, $arguments]);

    }

    public function result(): array
    {
        return $this->call($this->attributes['path'], $this->attributes['request']->method());
    }

    public function __call($method, $arguments)
    {
         if (isset($this->attributes['data'][$method])) {
             return $this->attributes['data'][$method];
         }
    }

}
