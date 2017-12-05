<?php

namespace Kirby\Cms;

class RootsTest extends TestCase
{

    public function defaultRootProvider(): array
    {

        $index = realpath(__DIR__ . '/../../../');

        return [
            [$index, 'index'],
            [$index, '/'],
            [$index . '/kirby', 'kirby'],
            [$index . '/media', 'media'],
            [$index . '/content', 'content'],
            [$site = $index . '/site', 'site'],
            [$site . '/controllers', 'controllers'],
            [$site . '/accounts', 'accounts'],
            [$site . '/snippets', 'snippets'],
            [$site . '/templates', 'templates'],
            [$site . '/plugins', 'plugins'],
            [$site . '/blueprints', 'blueprints'],
            [$index . '/panel', 'panel'],
        ];
    }

    /**
     * @dataProvider defaultRootProvider
     */
    public function testDefaulRoot($root, $method)
    {
        $roots = new Roots();

        $this->assertEquals($root, $roots->$method());
        $this->assertEquals($root, $roots->get($method));
    }

    public function customIndexRootProvider(): array
    {

        $index = '/var/www/getkirby.com';

        return [
            [$index, 'index'],
            [$index, '/'],
            [$index . '/kirby', 'kirby'],
            [$index . '/media', 'media'],
            [$index . '/content', 'content'],
            [$site = $index . '/site', 'site'],
            [$site . '/controllers', 'controllers'],
            [$site . '/accounts', 'accounts'],
            [$site . '/snippets', 'snippets'],
            [$site . '/templates', 'templates'],
            [$site . '/plugins', 'plugins'],
            [$site . '/blueprints', 'blueprints'],
            [$index . '/panel', 'panel'],
        ];
    }

    /**
     * @dataProvider customIndexRootProvider
     */
    public function testCustomIndexRoot($root, $method)
    {
        $roots = new Roots([
            'index' => '/var/www/getkirby.com'
        ]);

        $this->assertEquals($root, $roots->$method());
        $this->assertEquals($root, $roots->get($method));
    }

    public function customRootProvider(): array
    {

        $base    = '/var/www/getkirby.com';
        $public  = $base . '/public';

        return [
            [$public, 'index'],
            [$public, '/'],
            [$public . '/media', 'media'],
            [$public . '/panel', 'panel'],
            [$base . '/kirby', 'kirby'],
            [$base . '/content', 'content'],
            [$base . '/site', 'site'],
        ];
    }

    /**
     * @dataProvider customRootProvider
     */
    public function testCustomRoot($root, $method)
    {

        // public directory setup
        $base   = '/var/www/getkirby.com';
        $public = $base . '/public';

        $roots = new Roots([
            'index'   => $public,
            'media'   => $public . '/media',
            'panel'   => $public . '/panel',
            'kirby'   => $base . '/kirby',
            'content' => $base . '/content',
            'site'    => $base . '/site'
        ]);

        $this->assertEquals($root, $roots->$method());
        $this->assertEquals($root, $roots->get($method));
    }

}
