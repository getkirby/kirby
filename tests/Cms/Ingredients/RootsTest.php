<?php

namespace Kirby\Cms;

class RootsTest extends TestCase
{
    protected function rootProvider(string $index): array
    {
        $kirby = realpath(__DIR__ . '/../../..');

        return [
            [$kirby, 'kirby'],
            [$kirby . '/i18n', 'i18n'],
            [$kirby . '/i18n/translations', 'i18n:translations'],
            [$kirby . '/i18n/rules', 'i18n:rules'],
            [$index, 'index'],
            [$index . '/assets', 'assets'],
            [$index . '/content', 'content'],
            [$index . '/media', 'media'],
            [$kirby . '/panel', 'panel'],
            [$site = $index . '/site', 'site'],
            [$site . '/accounts', 'accounts'],
            [$site . '/blueprints', 'blueprints'],
            [$site . '/cache', 'cache'],
            [$site . '/collections', 'collections'],
            [$site . '/config', 'config'],
            [$site . '/config/.license', 'license'],
            [$site . '/controllers', 'controllers'],
            [$site . '/languages', 'languages'],
            [$site . '/logs', 'logs'],
            [$site . '/models', 'models'],
            [$site . '/plugins', 'plugins'],
            [$site . '/sessions', 'sessions'],
            [$site . '/snippets', 'snippets'],
            [$site . '/templates', 'templates'],
            [$site . '/blueprints/users', 'roles'],
        ];
    }

    public function defaultRootProvider(): array
    {
        return $this->rootProvider(realpath(__DIR__ . '/../../../../'));
    }

    /**
     * @dataProvider defaultRootProvider
     */
    public function testDefaultRoot($root, $method)
    {
        $roots = (new App())->roots();

        $this->assertSame($root, $roots->$method());
    }

    public function customIndexRootProvider(): array
    {
        return $this->rootProvider('/var/www/getkirby.com');
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

        $this->assertSame($root, $roots->$method());
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
            [$base . '/site/config', 'config'],
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

        $this->assertSame($root, $roots->$method());
    }
}
