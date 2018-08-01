<?php

namespace Kirby\Api;

use Closure;
use Exception;
use Throwable;

use Kirby\Exception\NotFoundException;
use Kirby\Http\Router;
use Kirby\Http\Response;
use Kirby\Toolkit\Properties;

/**
 * The API class is a generic container
 * for API routes, models and collections and is used
 * to run our REST API. You can find our API setup
 * in kirby/config/api.php
 */
class Api
{
    use Properties;

    protected $authentication;
    protected $collections = [];
    protected $data = [];
    protected $models = [];
    protected $routes = [];
    protected $requestData = [];
    protected $requestMethod;

    public function __call($method, $args)
    {
        return $this->data($method, ...$args);
    }

    public function __construct(array $props)
    {
        $this->setProperties($props);
    }

    public function authenticate()
    {
        if ($auth = $this->authentication()) {
            return $auth->call($this);
        }

        return true;
    }

    public function authentication()
    {
        return $this->authentication;
    }

    public function call(string $path = null, string $method = 'GET', array $requestData = [])
    {
        $path = rtrim($path, '/');

        $this->setRequestMethod($method);
        $this->setRequestData($requestData);

        $router = new Router($this->routes());
        $result = $router->find($path, $method);
        $auth   = $result->attributes()['auth'] ?? true;

        if ($auth !== false) {
            $this->authenticate();
        }

        $output = $result->action()->call($this, ...$result->arguments());

        if (is_object($output) === true) {
            return $this->resolve($output)->toResponse();
        }

        return $output;
    }

    public function collection(string $name, $collection = null)
    {
        if (isset($this->collections[$name]) === false) {
            throw new NotFoundException(sprintf('The collection "%s" does not exist', $name));
        }

        return new Collection($this, $collection, $this->collections[$name]);
    }

    public function collections(): array
    {
        return $this->collections;
    }

    public function data($key = null, ...$args)
    {
        if ($key === null) {
            return $this->data;
        }

        if ($this->hasData($key) === false) {
            throw new NotFoundException(sprintf('Api data for "%s" does not exist', $key));
        }

        // lazy-load data wrapped in Closures
        if (is_a($this->data[$key], 'Closure') === true) {
            return $this->data[$key]->call($this, ...$args);
        }

        return $this->data[$key];
    }

    public function hasData($key): bool
    {
        return isset($this->data[$key]) === true;
    }

    public function model(string $name, $object = null)
    {
        if (isset($this->models[$name]) === false) {
            throw new NotFoundException(sprintf('The model "%s" does not exist', $name));
        }

        return new Model($this, $object, $this->models[$name]);
    }

    public function models(): array
    {
        return $this->models;
    }

    public function requestData($type = null, $key = null, $default = null)
    {
        if ($type === null) {
            return $this->requestData;
        }

        if ($key === null) {
            return $this->requestData[$type] ?? [];
        }

        $data = array_change_key_case($this->requestData($type));
        $key  = strtolower($key);

        return $data[$key] ?? $default;
    }

    public function requestBody(string $key = null, $default = null)
    {
        return $this->requestData('body', $key, $default);
    }

    public function requestFiles(string $key = null, $default = null)
    {
        return $this->requestData('files', $key, $default);
    }

    public function requestHeaders(string $key = null, $default = null)
    {
        return $this->requestData('headers', $key, $default);
    }

    public function requestMethod(): string
    {
        return $this->requestMethod;
    }

    public function requestQuery(string $key = null, $default = null)
    {
        return $this->requestData('query', $key, $default);
    }

    public function resolve($object)
    {
        if (is_a($object, 'Kirby\Api\Model') === true || is_a($object, 'Kirby\Api\Collection') === true) {
            return $object;
        }

        $className = strtolower(get_class($object));
        $lastDash  = strrpos($className, '\\');

        if ($lastDash !== false) {
            $className = substr($className, $lastDash + 1);
        }

        if (isset($this->models[$className]) === true) {
            return $this->model($className, $object);
        }

        if (isset($this->collections[$className]) === true) {
            return $this->collection($className, $object);
        }

        // now models deeply by checking for the actual type
        foreach ($this->models as $className => $model) {
            if (is_a($object, $model['type']) === true) {
                return $this->model($className, $object);
            }
        }

        throw new NotFoundException(sprintf('The object "%s" cannot be resolved', $className));
    }

    public function routes(): array
    {
        return $this->routes;
    }

    protected function setAuthentication(Closure $authentication = null)
    {
        $this->authentication = $authentication;
        return $this;
    }

    protected function setCollections(array $collections = null)
    {
        if ($collections !== null) {
            $this->collections = array_change_key_case($collections);
        }
        return $this;
    }

    protected function setData(array $data = null)
    {
        $this->data = $data ?? [];
        return $this;
    }

    protected function setModels(array $models = null)
    {
        if ($models !== null) {
            $this->models = array_change_key_case($models);
        }

        return $this;
    }

    protected function setRequestData(array $requestData = null)
    {
        $defaults = [
            'query' => [],
            'body'  => [],
            'files' => []
        ];

        $this->requestData = array_merge($defaults, (array)$requestData);
        return $this;
    }

    protected function setRequestMethod(string $requestMethod = null)
    {
        $this->requestMethod = $requestMethod;
        return $this;
    }

    protected function setRoutes(array $routes = null)
    {
        $this->routes = $routes ?? [];
        return $this;
    }

    public function render(string $path, $method = 'GET', array $requestData = [])
    {
        try {
            $result = $this->call($path, $method, $requestData);
        } catch (Throwable $e) {
            error_log($e);

            if (is_a($e, 'Kirby\Exception\Exception') === true) {
                $result = ['status' => 'error'] + $e->toArray();
            } else {
                $result = [
                    'status'    => 'error',
                    'exception' => get_class($e),
                    'message'   => $e->getMessage(),
                    'file'      => ltrim($e->getFile(), $_SERVER['DOCUMENT_ROOT'] ?? null),
                    'line'      => $e->getLine(),
                    'code'      => 500
                ];
            }
        }

        if ($result === null) {
            $result = [
                'status'  => 'error',
                'message' => 'not found',
                'code'    => 404,
            ];
        }

        if ($result === true) {
            $result = [
                'status' => 'ok',
            ];
        }

        if ($result === false) {
            $result = [
                'status'  => 'error',
                'message' => 'bad request',
                'code'    => 400,
            ];
        }

        // pretty print json data
        $pretty = (bool)($requestData['query']['pretty'] ?? false) === true;

        if (($result['status'] ?? 'ok') === 'error') {
            return Response::json($result, $result['code'] ?? 400, $pretty);
        }

        return Response::json($result, 200, $pretty);
    }

    public function upload(Closure $callback, $single = false): array
    {
        $trials  = 0;
        $uploads = [];
        $errors  = [];
        $files   = $this->requestFiles();

        if (empty($files) === true) {
            throw new Exception('No uploaded files');
        }

        foreach ($files as $upload) {
            if (isset($upload['tmp_name']) === false && is_array($upload)) {
                continue;
            }

            $trials++;

            try {
                if ($upload['error'] !== 0) {
                    throw new Exception('Upload error');
                }

                $filename = basename($upload['name']);
                $source   = dirname($upload['tmp_name']) . '/' . uniqid() . '.' . $filename;

                // move the file to a location including the extension,
                // for better mime detection
                if (move_uploaded_file($upload['tmp_name'], $source) === false) {
                    throw new Exception('The uploaded file could not be moved');
                }

                $data = $callback($source, $filename);

                if (is_object($data) === true) {
                    $data = $this->resolve($data)->toArray();
                }

                $uploads[$upload['name']] = $data;
            } catch (Exception $e) {
                $errors[$upload['name']] = $e->getMessage();
            }

            if ($single === true) {
                break;
            }
        }

        // return a single upload response
        if ($trials === 1) {
            if (empty($errors) === false) {
                return [
                    'status'  => 'error',
                    'message' => current($errors)
                ];
            }

            return [
                'status' => 'ok',
                'data'   => current($uploads)
            ];
        }

        if (empty($errors) === false) {
            return [
                'status' => 'error',
                'errors' => $errors
            ];
        }

        return [
            'status' => 'ok',
            'data'   => $uploads
        ];
    }
}
