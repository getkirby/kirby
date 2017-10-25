<?php

namespace Kirby\Cms;

class Site extends Object
{

    use HasChildren;
    use HasContent;
    use HasFiles;

    protected static $storePrefix = 'site';

    public function __construct(array $props = [])
    {

        parent::__construct($props, [
            'children' => [
                'type'    => Pages::class,
                'default' => function (): Pages {
                    return $this->store()->commit('site.children', $this);
                }
            ],
            'content' => [
                'type'    => Content::class,
                'default' => function () {
                    return $this->store()->commit('site.content', $this);
                }
            ],
            'page' => [
                'type'    => Page::class,
                'default' => function () {
                    return $this->homePage();
                }
            ],
            'errorPage' => [
                'type'    => Page::class,
                'default' => function () {
                    return $this->find('error');
                }
            ],
            'files' => [
                'type'    => Files::class,
                'default' => function () {
                    return $this->store()->commit('site.files', $this);
                }
            ],
            'homePage' => [
                'type'    => Page::class,
                'default' => function () {
                    return $this->find('home');
                }
            ],
            'root' => [
                'type' => 'string',
            ],
            'url' => [
                'type'    => 'string',
                'default' => '/'
            ],
        ]);

    }

    public function page(string $path = null)
    {
        if ($path === null) {
            return $this->prop('page');
        }

        return $this->find($path);
    }

    public function pages(): Pages
    {
        return $this->children();
    }

    public function visit($path)
    {
        if ($page = $this->find($path)) {
            return $this->set('page', $page);
        }

        return $this;
    }

    public function update(array $content = []): self
    {
        return $this->store()->commit('site.update', $this, $content);
    }

}
