<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Http\Request;
use Kirby\Http\Router;
use Kirby\Http\Router\Route;

class Api extends Object
{

    protected function schema()
    {
        return [
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
        ];
    }

    public function user(): User
    {

        $headers = array_change_key_case($this->props->request->headers(), CASE_LOWER);
        $token   = str_replace('Bearer ', '', $headers['authorization'] ?? null);

        if ($token === null) {
            throw new Exception('Invalid authorization token');
        }

        $user = $this->app()->users()->findBy('token', $token);

        if ($user === null) {
            throw new Exception('The user cannot be found');
        }

        return $user;

    }

    public function request()
    {
        return $this->props->request;
    }

    public function query()
    {
        return $this->request()->query()->toArray();
    }

    public function input($key = null)
    {
        if ($key === null) {
            return $this->request()->data();
        }

        return $this->request()->data()[$key] ?? null;
    }

    public function router()
    {

        $router = new Router;

        foreach ($this->props->routes as $route) {
            $router->register(new Route($route['pattern'], $route['method'] ?? 'GET', $route['action'], $route));
        }

        return $router;

    }

    public function call($path, $method = 'GET')
    {

        $result = $this->router()->find($path, $method);

        // authentication
        if (($result->attributes()['auth'] ?? false) === true) {
            // $this->user();
        }

        try {
            return $result->action()->call($this, ...$result->arguments());
        } catch (Exception $e) {
            return [
                'status'  => 'error',
                'message' => $e->getMessage()
            ];
        }

    }

    public function output(string $type, $object, ...$arguments): array
    {

        if (isset($this->props->types[$type]) === false) {
            throw new Exception(sprintf('Invalid output type "%s"', $type));
        }

        if ($object === null) {
            throw new Exception(sprintf('Missing "%s" object', $type));
        }

        return $this->props->types[$type]->call($this, $object, ...$arguments);

    }

    public function result(): array
    {

        try {
            $result = $this->call($this->props->path, $this->props->request->method());
        } catch (Exception $e) {
            return [
                'status'  => 'error',
                'message' => $e->getMessage()
            ];
        }

        if (is_array($result)) {
            return $result;
        }

        if ($result === true) {
            return [
                'status' => 'ok'
            ];
        }

        if ($result === null) {
            return [
                'status'  => 'error',
                'message' => 'not found'
            ];
        }

        if ($result === false) {
            return [
                'status'  => 'error',
                'message' => 'Unexpected error'
            ];
        }

    }

    public function __call(string $method, array $arguments = [])
    {
         if (isset($this->props->data[$method])) {
             return $this->props->data[$method];
         }
    }

}
