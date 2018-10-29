<?php

namespace Kirby\Cms;

use Kirby\Api\Api as BaseApi;
use Kirby\Exception\NotFoundException;

class Api extends BaseApi
{
    protected $kirby;

    public function call(string $path = null, string $method = 'GET', array $requestData = [])
    {
        if ($languageCode = $this->requestHeaders('x-language')) {
            $this->kirby->setCurrentLanguage($languageCode);
        }

        if ($user = $this->kirby->user()) {
            $this->kirby->setCurrentTranslation($user->language());
        }

        return parent::call($path, $method, $requestData);
    }

    public function file(string $id = null, string $filename)
    {
        $filename = urldecode($filename);
        $parent   = $id === null ? $this->site(): $this->page($id);

        if ($file = $parent->file($filename)) {
            return $file;
        }

        throw new NotFoundException([
            'key'  => 'file.notFound',
            'data' => [
                'filename' => $filename
            ]
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
            return $this->kirby->user();
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
