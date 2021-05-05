<?php

namespace Kirby\Api;

use Kirby\Cms\App;
use PHPUnit\Framework\TestCase;

class TranslationsRoutesTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);

        $this->app->impersonate('kirby');
    }

    public function testList()
    {
        $app      = $this->app;
        $response = $app->api()->call('translations');
        $files    = glob($app->root('kirby') . '/i18n/translations/*.json');

        $this->assertCount(count($files), $response['data']);
    }

    public function testGet()
    {
        $app = $this->app;

        $response = $app->api()->call('translations/de');

        $this->assertEquals('de', $response['data']['id']);
        $this->assertEquals('Deutsch', $response['data']['name']);
        $this->assertEquals('ltr', $response['data']['direction']);
    }
}
