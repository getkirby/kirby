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
     * @param string|null $path
     * @param string $method
     * @param array $requestData
     * @return mixed
     */
    public function call(string $path = null, string $method = 'GET', array $requestData = [])
    {
        $this->setRequestMethod($method);
        $this->setRequestData($requestData);

        $this->kirby->setCurrentLanguage($this->language());

        $allowImpersonation = $this->kirby()->option('api.allowImpersonation', false);
        if ($user = $this->kirby->user(null, $allowImpersonation)) {
            $this->kirby->setCurrentTranslation($user->language());
        }

        return parent::call($path, $method, $requestData);
    }

    /**
     * @param mixed $model
     * @param string $name
     * @param string|null $path
     * @return mixed
     * @throws \Kirby\Exception\NotFoundException if the field type cannot be found or the field cannot be loaded
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

        // it can get this error only if $name is an empty string as $name = ''
        if ($field === null) {
            throw new NotFoundException('No field could be loaded');
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
     * @param string|null $path Path to file's parent model
     * @param string $filename Filename
     * @return \Kirby\Cms\File|null
     * @throws \Kirby\Exception\NotFoundException if the file cannot be found
     */
    public function file(string $path = null, string $filename)
    {
        $filename = urldecode($filename);
        $file     = $this->parent($path)->file($filename);

        if ($file && $file->isReadable() === true) {
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
     * @return \Kirby\Cms\Model|null
     * @throws \Kirby\Exception\InvalidArgumentException if the model type is invalid
     * @throws \Kirby\Exception\NotFoundException if the model cannot be found
     */
    public function parent(string $path)
    {
        $modelType  = in_array($path, ['site', 'account']) ? $path : trim(dirname($path), '/');
        $modelTypes = [
            'site'    => 'site',
            'users'   => 'user',
            'pages'   => 'page',
            'account' => 'account'
        ];
        $modelName = $modelTypes[$modelType] ?? null;

        if (Str::endsWith($modelType, '/files') === true) {
            $modelName = 'file';
        }

        $kirby = $this->kirby();

        switch ($modelName) {
            case 'site':
                $model = $kirby->site();
                break;
            case 'account':
                $model = $kirby->user(null, $kirby->option('api.allowImpersonation', false));
                break;
            case 'page':
                $id    = str_replace(['+', ' '], '/', basename($path));
                $model = $kirby->page($id);
                break;
            case 'file':
                $model = $this->file(...explode('/files/', $path));
                break;
            case 'user':
                $model = $kirby->user(basename($path));
                break;
            default:
                throw new InvalidArgumentException('Invalid model type: ' . $modelType);
        }

        if ($model) {
            return $model;
        }

        throw new NotFoundException([
            'key' => $modelName . '.undefined'
        ]);
    }

    /**
     * Returns the Kirby instance
     *
     * @return \Kirby\Cms\App
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
     * @return \Kirby\Cms\Page|null
     * @throws \Kirby\Exception\NotFoundException if the page cannot be found
     */
    public function page(string $id)
    {
        $id   = str_replace('+', '/', $id);
        $page = $this->kirby->page($id);

        if ($page && $page->isReadable() === true) {
            return $page;
        }

        throw new NotFoundException([
            'key'  => 'page.notFound',
            'data' => [
                'slug' => $id
            ]
        ]);
    }

    /**
     * Returns the subpages for the given
     * parent. The subpages can be filtered
     * by status (draft, listed, unlisted, published, all)
     *
     * @param string|null $parentId
     * @param string|null $status
     * @return \Kirby\Cms\Pages
     */
    public function pages(string $parentId = null, string $status = null)
    {
        $parent = $parentId === null ? $this->site() : $this->page($parentId);

        switch ($status) {
            case 'all':
                return $parent->childrenAndDrafts();
            case 'draft':
            case 'drafts':
                return $parent->drafts();
            case 'listed':
                return $parent->children()->listed();
            case 'unlisted':
                return $parent->children()->unlisted();
            case 'published':
            default:
                return $parent->children();
        }
    }

    /**
     * Search for direct subpages of the
     * given parent
     *
     * @param string|null $parent
     * @return \Kirby\Cms\Pages
     */
    public function searchPages(string $parent = null)
    {
        $pages = $this->pages($parent, $this->requestQuery('status'));

        if ($this->requestMethod() === 'GET') {
            return $pages->search($this->requestQuery('q'));
        }

        return $pages->query($this->requestBody());
    }

    /**
     * Returns the current Session instance
     *
     * @param array $options Additional options, see the session component
     * @return \Kirby\Session\Session
     */
    public function session(array $options = [])
    {
        return $this->kirby->session(array_merge([
            'detect' => true
        ], $options));
    }

    /**
     * Setter for the parent Kirby instance
     *
     * @param \Kirby\Cms\App $kirby
     * @return self
     */
    protected function setKirby(App $kirby)
    {
        $this->kirby = $kirby;
        return $this;
    }

    /**
     * Returns the site object
     *
     * @return \Kirby\Cms\Site
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
     * @param string|null $id User's id
     * @return \Kirby\Cms\User|null
     * @throws \Kirby\Exception\NotFoundException if the user for the given id cannot be found
     */
    public function user(string $id = null)
    {
        // get the authenticated user
        if ($id === null) {
            return $this->kirby->auth()->user(null, $this->kirby()->option('api.allowImpersonation', false));
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
     * @return \Kirby\Cms\Users
     */
    public function users()
    {
        return $this->kirby->users();
    }
}
