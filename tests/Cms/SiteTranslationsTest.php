<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Toolkit\F;
use PHPUnit\Framework\TestCase;

class SiteTranslationsTest extends TestCase
{

    public function app($language = null)
    {
        $app = new App([
            'languages' => [
                [
                    'code'    => 'en',
                    'name'    => 'English',
                    'default' => true
                ],
                [
                    'code'    => 'de',
                    'name'    => 'Deutsch'
                ]
            ],
            'site' => [
                'translations' => [
                    [
                        'code' => 'en',
                        'content' => [
                            'title' => 'Site',
                            'untranslated' => 'Untranslated'
                        ]
                    ],
                    [
                        'code' => 'de',
                        'content' => [
                            'title' => 'Seite',
                        ]
                    ],
                ]
            ]
        ]);

        if ($language !== null) {
            $app->localize( $app->languages()->find($language) );
        }

        return $app;
    }

    public function site()
    {
        return $this->app()->site();
    }

    public function testUrl()
    {
        $site = $this->site();

        $this->assertEquals('/en', $site->url());
        $this->assertEquals('/de', $site->url('de'));
    }

    public function testContentInEnglish()
    {
        $site = $this->site();
        $this->assertEquals('Site', $site->title()->value());
        $this->assertEquals('Untranslated', $site->untranslated()->value());
    }

    public function testContentInDeutsch()
    {
        $site = $this->app('de')->site();
        $this->assertEquals('Seite', $site->title()->value());
        $this->assertEquals('Untranslated', $site->untranslated()->value());
    }

    public function testTranslations()
    {
        $site = $this->site();
        $this->assertCount(2, $site->translations());
        $this->assertEquals(['en', 'de'], $site->translations()->keys());
    }

}
