<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class LanguageRoutesTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
        App::destroy();

        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'languages' => [
                [
                    'code'    => 'en',
                    'name'    => 'English',
                    'default' => true,
                    'locale'  => 'en_US.UTF-8',
                    'url'     => '/',
                ],
                [
                    'code'    => 'de',
                    'name'    => 'Deutsch',
                    'locale'  => 'de_AT.UTF-8',
                    'url'     => '/de',
                ],
            ]
        ]);
    }

    public function testFallback()
    {
        $app = $this->app->clone([
            'site' => [
                'children' => [
                    [
                        'slug'     => 'error',
                        'template' => 'error'
                    ]
                ]
            ]
        ]);

        $app->call('notes');
        $this->assertSame($app->language()->code(), 'en');

        $app->call('de/notes');
        $this->assertSame($app->language()->code(), 'de');
    }
}
