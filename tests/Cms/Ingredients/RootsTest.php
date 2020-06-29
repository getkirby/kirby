<?php

namespace Kirby\Cms;

class RootsTest extends TestCase
{
    public function defaultRootProvider(): array
    {
        $index = realpath(__DIR__ . '/../../../../');

        return [
            [$index, 'index'],
            [$index . '/kirby', 'kirby'],
            [$index . '/media', 'media'],
            [$index . '/content', 'content'],
            [$site = $index . '/site', 'site'],
            [$site . '/collections', 'collections'],
            [$site . '/controllers', 'controllers'],
            [$site . '/accounts', 'accounts'],
            [$site . '/snippets', 'snippets'],
            [$site . '/templates', 'templates'],
            [$site . '/plugins', 'plugins'],
            [$site . '/blueprints', 'blueprints'],
        ];
    }

    /**
     * @dataProvider defaultRootProvider
     */
    public function testDefaulRoot($root, $method)
    {
        $roots = (new App())->roots();

        $this->assertEquals($root, $roots->$method());
    }

    public function customIndexRootProvider(): array
    {
        $index = '/var/www/getkirby.com';

        return [
            [$index, 'index'],
            [$index . '/media', 'media'],
            [$index . '/content', 'content'],
            [$site = $index . '/site', 'site'],
            [$site . '/collections', 'collections'],
            [$site . '/controllers', 'controllers'],
            [$site . '/accounts', 'accounts'],
            [$site . '/snippets', 'snippets'],
            [$site . '/templates', 'templates'],
            [$site . '/plugins', 'plugins'],
            [$site . '/blueprints', 'blueprints'],
        ];
    }

    /**
     * @dataProvider customIndexRootProvider
     */
    public function testCustomIndexRoot($root, $method)
    {
        $app = new App([
            'roots' => [
                'index' => '/var/www/getkirby.com'
            ]
        ]);

        $roots = $app->roots();

        $this->assertEquals($root, $roots->$method());
    }

    public function customRootProvider(): array
    {
        $base    = '/var/www/getkirby.com';
        $public  = $base . '/public';

        return [
            [$public, 'index'],
            [$public . '/media', 'media'],
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

        $app = new App([
            'roots' => [
                'index'   => $public,
                'media'   => $public . '/media',
                'content' => $base . '/content',
                'site'    => $base . '/site'
            ]
        ]);

        $roots = $app->roots();

        $this->assertEquals($root, $roots->$method());
    }
}
