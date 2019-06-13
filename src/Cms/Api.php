<?php

namespace Kirby\Cms;

use Kirby\Api\Api as BaseApi;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Toolkit\Str;

/**
 * Api
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Api extends BaseApi
{
    /**
     * @var App
     */
    protected $kirby;

    /**
     * Execute an API call for the given path,
     * request method and optional request data
     *
     * @param string $path
     * @param string $method
     * @param array $requestData
     * @return mixed
     */
    public function call(string $path = null, string $method = 'GET', array $requestData = [])
    {
        $this->setRequestMethod($method);
        $this->setRequestData($requestData);

        if ($languageCode = $this->language()) {
            $this->kirby->setCurrentLanguage($languageCode);
        }

        if ($user = $this->kirby->user()) {
            $this->kirby->setCurrentTranslation($user->language());
        }

        return parent::call($path, $method, $requestData);
    }

    /**
     * @param mixed $model
     * @param string $name
     * @param string $path
     * @return mixed
     */
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

    /**
     * Returns the file object for the given
     * parent path and filename
     *
     * @param string $path Path to file's parent model
     * @param string $filename Filename
     * @return Kirby\Cms\File|null
     */
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

    /**
     * Returns the model's object for the given path
     *
     * @param string $path Path to parent model
     * @return Kirby\Cms\Model|null
     */
    public function parent(string $path)
    {
        $modelType  = $path === 'site' ? 'site' : trim(dirname($path), '/');
        $modelTypes = ['site' => 'site', 'users' => 'user', 'pages' => 'page'];
        $modelName  = $modelTypes[$modelType] ?? null;

        if (Str::endsWith($modelType, '/files') === true) {
            $modelName = 'file';
        }

        if ($modelName === null) {
            throw new InvalidArgumentException('Invalid file model type');
        }

        if ($modelName === 'site') {
            $modelId = null;
        } else {
            $modelId = basename($path);

            if ($modelName === 'page') {
                $modelId = str_replace(['+', ' '], '/', $modelId);
            }

            if ($modelName === 'file') {
                if ($model = $this->file(...explode('/files/', $path))) {
                    return $model;
                }
            }
        }

        if ($model = $this->kirby()->$modelName($modelId)) {
            return $model;
        }

        throw new NotFoundException([
            'key' => $modelName . '.undefined'
        ]);
    }

    /**
     * Returns the Kirby instance
     *
     * @return Kirby\Cms\App
     */
    public function kirby()
    {
        return $this->kirby;
    }

    /**
     * Returns the language request header
     *
     * @return string|null
     */
    public function language(): ?string
    {
        return get('language') ?? $this->requestHeaders('x-language');
    }

    /**
    * Returns the page object for the given id
    *
    * @param string $id Page's id
    * @return Kirby\Cms\Page|null
    */
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

    /**
     * @param Kirby\Cms\App $kirby
     */
    protected function setKirby(App $kirby)
    {
        $this->kirby = $kirby;
        return $this;
    }

    /**
     * Returns the site object
     *
     * @return Kirby\Cms\Site
     */
    public function site()
    {
        return $this->kirby->site();
    }

    /**
     * Returns the user object for the given id or
     * returns the current authenticated user if no
     * id is passed
     *
     * @param string $id User's id
     * @return Kirby\Cms\User|null
     */
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

    /**
     * Returns the users collection
     *
     * @return Kirby\Cms\Users
     */
    public function users()
    {
        return $this->kirby->users();
    }
}
