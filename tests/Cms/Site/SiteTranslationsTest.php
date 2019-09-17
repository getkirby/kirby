<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class SiteTranslationsTest extends TestCase
{
    public function app($language = null)
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
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
            $app->setCurrentLanguage($language);
            $app->setCurrentTranslation($language);
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

        // non-existing language
        $this->assertEquals('/', $site->url('fr'));
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

    public function visitProvider()
    {
        return [
            ['en', 'Site', 'English Test'],
            ['de', 'Seite', 'Deutsch Test']
        ];
    }

    /**
     * @dataProvider visitProvider
     */
    public function testVisit($languageCode, $siteTitle, $pageTitle)
    {
        $app = $this->app()->clone([
            'site' => [
                'children' => [
                    [
                        'slug' => 'test',
                        'translations' => [
                            [
                                'code' => 'en',
                                'content' => [
                                    'title' => 'English Test'
                                ],
                            ],
                            [
                                'code' => 'de',
                                'content' => [
                                    'title' => 'Deutsch Test'
                                ],
                            ]
                        ]
                    ]
                ],
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

        $site = $app->site();
        $page = $site->visit('test', $languageCode);

        $this->assertEquals($languageCode, $app->language()->code());
        $this->assertEquals('test', $page->slug());
        $this->assertEquals($siteTitle, $site->title()->value());
        $this->assertEquals($pageTitle, $page->title()->value());
    }
}
