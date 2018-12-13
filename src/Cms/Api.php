<?php

namespace Kirby\Cms;

use Kirby\Api\Api as BaseApi;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Toolkit\Str;

class Api extends BaseApi
{
    protected $kirby;

    public function call(string $path = null, string $method = 'GET', array $requestData = [])
    {
        $this->setRequestMethod($method);
        $this->setRequestData($requestData);

        if ($languageCode = $this->requestHeaders('x-language')) {
            $this->kirby->setCurrentLanguage($languageCode);
        }

        if ($user = $this->kirby->user()) {
            $this->kirby->setCurrentTranslation($user->language());
        }

        return parent::call($path, $method, $requestData);
    }

    public function fieldApi($model, string $name, string $path = null)
    {
        $form       = Form::for($model);
        $fieldNames = Str::split($name, '+');
        $index      = 0;
        $count      = count($fieldNames);
        $field      = null;

        foreach ($fieldNames as $fieldName) {
            $index++;

            if ($field = $form->fields()->get($fieldName)) {
                if ($count !== $index) {
                    $form = $field->form();
                }
            } else {
                throw new NotFoundException('The field "' . $fieldName . '" could not be found');
            }
        }

        if ($field === null) {
            throw new NotFoundException('The field "' . $fieldNames . '" could not be found');
        }

        $fieldApi = $this->clone([
            'routes' => $field->api(),
            'data'   => array_merge($this->data(), ['field' => $field])
        ]);

        return $fieldApi->call($path, $this->requestMethod(), $this->requestData());
    }

    public function file(string $path = null, string $filename)
    {
        $filename = urldecode($filename);

        if ($file = $this->parent($path)->file($filename)) {
            return $file;
        }

        throw new NotFoundException([
            'key'  => 'file.notFound',
            'data' => [
                'filename' => $filename
            ]
        ]);
    }

    public function parent(string $path)
    {
        $modelType  = $path === 'site' ? 'site' : dirname($path);
        $modelTypes = ['site' => 'site', 'users' => 'user', 'pages' => 'page'];
        $modelName  = $modelTypes[$modelType] ?? null;

        if ($modelName === null) {
            throw new InvalidArgumentException('Invalid file model type');
        }

        if ($modelName === 'site') {
            $modelId = null;
        } else {
            $modelId = basename($path);

            if ($modelName === 'page') {
                $modelId = str_replace('+', '/', $modelId);
            }
        }

        if ($model = $this->kirby()->$modelName($modelId)) {
            return $model;
        }

        throw new NotFoundException([
            'key' => $modelName . '.undefined'
        ]);
    }

    public function kirby()
    {
        return $this->kirby;
    }

    public function language()
    {
        return $this->requestHeaders('x-language');
    }

    public function page(string $id)
    {
        $id   = str_replace('+', '/', $id);
        $page = $this->kirby->page($id);

        if ($page && $page->isReadable()) {
            return $page;
        }

        throw new NotFoundException([
            'key'  => 'page.notFound',
            'data' => [
                'slug' => $id
            ]
        ]);
    }

    public function session(array $options = [])
    {
        return $this->kirby->session(array_merge([
            'detect' => true
        ], $options));
    }

    protected function setKirby(App $kirby)
    {
        $this->kirby = $kirby;
        return $this;
    }

    public function site()
    {
        return $this->kirby->site();
    }

    public function user(string $id = null)
    {
        // get the authenticated user
        if ($id === null) {
            return $this->kirby->auth()->user();
        }

        // get a specific user by id
        if ($user = $this->kirby->users()->find($id)) {
            return $user;
        }

        throw new NotFoundException([
            'key'  => 'user.notFound',
            'data' => [
                'name' => $id
            ]
        ]);
    }

    public function users()
    {
        return $this->kirby->users();
    }
}
