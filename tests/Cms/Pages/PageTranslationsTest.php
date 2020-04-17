<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Dir;
use PHPUnit\Framework\TestCase;

class PageTranslationsTest extends TestCase
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
                'children' => [
                    [
                        'children' => [
                            [
                                'children' => [
                                    [
                                        'slug' => 'child',
                                        'translations' => [
                                            [
                                                'code' => 'en',
                                                'content' => [
                                                    'title' => 'Child',
                                                ]
                                            ],
                                            [
                                                'code' => 'de',
                                                'slug' => 'kind',
                                                'content' => [
                                                    'title' => 'Kind',
                                                ]
                                            ],
                                        ]
                                    ]
                                ],
                                'slug' => 'mother',
                                'translations' => [
                                    [
                                        'code' => 'en',
                                        'content' => [
                                            'title' => 'Mother',
                                        ]
                                    ],
                                    [
                                        'code' => 'de',
                                        'slug' => 'mutter',
                                        'content' => [
                                            'title' => 'Mutter',
                                        ]
                                    ],
                                ],
                            ]
                        ],
                        'slug'  => 'grandma',
                        'translations' => [
                            [
                                'code' => 'en',
                                'content' => [
                                    'title' => 'Grandma',
                                    'untranslated' => 'Untranslated'
                                ]
                            ],
                            [
                                'code' => 'de',
                                'slug' => 'oma',
                                'content' => [
                                    'title' => 'Oma',
                                ]
                            ],
                        ],
                    ],
                    [
                        'slug' => 'home'
                    ]
                ],
            ],
        ]);

        if ($language !== null) {
            $app->setCurrentLanguage($language);
            $app->setCurrentTranslation($language);
        }

        return $app;
    }

    public function testUrl()
    {
        $app = $this->app();

        $page = $app->page('home');
        $this->assertEquals('/en', $page->url());
        $this->assertEquals('/de', $page->url('de'));

        $page = $app->page('grandma');
        $this->assertEquals('/en/grandma', $page->url());
        $this->assertEquals('/de/oma', $page->url('de'));

        $page = $app->page('grandma/mother');
        $this->assertEquals('/en/grandma/mother', $page->url());
        $this->assertEquals('/de/oma/mutter', $page->url('de'));

        $page = $app->page('grandma/mother/child');
        $this->assertEquals('/en/grandma/mother/child', $page->url());
        $this->assertEquals('/de/oma/mutter/kind', $page->url('de'));
    }

    public function testContentInEnglish()
    {
        $page = $this->app()->page('grandma');
        $this->assertEquals('Grandma', $page->title()->value());
        $this->assertEquals('Untranslated', $page->untranslated()->value());
    }

    public function testContentInDeutsch()
    {
        $page = $this->app('de')->page('grandma');
        $this->assertEquals('Oma', $page->title()->value());

        $this->assertEquals('Untranslated', $page->untranslated()->value());
    }

    public function testContent()
    {
        $page = $this->app('en')->page('grandma');

        // without language code
        $content = $page->content();
        $this->assertEquals('Grandma', $content->title()->value());
        $this->assertEquals('Untranslated', $content->untranslated()->value());

        // with default language code
        $content = $page->content('en');
        $this->assertEquals('Grandma', $content->title()->value());
        $this->assertEquals('Untranslated', $content->untranslated()->value());

        // with different language code
        $content = $page->content('de');
        $this->assertEquals('Oma', $content->title()->value());
        $this->assertEquals('Untranslated', $content->untranslated()->value());

        // switch back to default
        $content = $page->content('en');
        $this->assertEquals('Grandma', $content->title()->value());
        $this->assertEquals('Untranslated', $content->untranslated()->value());
    }

    public function testSlug()
    {
        $app = $this->app();

        $this->assertEquals('grandma', $app->page('grandma')->slug());
        $this->assertEquals('grandma', $app->page('grandma')->slug('en'));
        $this->assertEquals('oma', $app->page('grandma')->slug('de'));

        $this->assertEquals('mother', $app->page('grandma/mother')->slug());
        $this->assertEquals('mother', $app->page('grandma/mother')->slug('en'));
        $this->assertEquals('mutter', $app->page('grandma/mother')->slug('de'));

        $this->assertEquals('child', $app->page('grandma/mother/child')->slug());
        $this->assertEquals('child', $app->page('grandma/mother/child')->slug('en'));
        $this->assertEquals('kind', $app->page('grandma/mother/child')->slug('de'));
    }

    public function testFindInEnglish()
    {
        $app = $this->app();
        $this->assertEquals('grandma', $app->page('grandma')->id());
        $this->assertEquals('grandma/mother', $app->page('grandma/mother')->id());
        $this->assertEquals('grandma/mother/child', $app->page('grandma/mother/child')->id());
    }

    public function testFindInDeutsch()
    {
        $app = $this->app('de');
        $this->assertEquals('grandma', $app->page('oma')->id());
        $this->assertEquals('grandma/mother', $app->page('oma/mutter')->id());
        $this->assertEquals('grandma/mother/child', $app->page('oma/mutter/kind')->id());
    }

    public function testTranslations()
    {
        $page = $this->app()->page('grandma');
        $this->assertCount(2, $page->translations());
        $this->assertEquals(['en', 'de'], $page->translations()->keys());
    }

    public function testUntranslatableFields()
    {
        $app = new App([
            'roots' => [
                'index' => $fixtures = __DIR__ . '/fixtures/PageTranslationsTest'
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
            'options' => [
                'languages' => true
            ]
        ]);

        $page = new Page([
            'slug' => 'test',
            'blueprint' => [
                'fields' => [
                    'a' => [
                        'type' => 'text'
                    ],
                    'b' => [
                        'type' => 'text',
                        'translate' => false
                    ],
                    'CAPITALIZED' => [
                        'type' => 'text',
                        'translate' => false
                    ],
                    'dDdDdD' => [
                        'type' => 'text',
                    ]
                ]
            ]
        ]);

        $app->impersonate('kirby');

        $en = $page->update([
            'a' => 'A',
            'b' => 'B',
            'capitalized' => 'C',
            'dDdDdD' => 'D'
        ]);

        $expected = [
            'a' => 'A',
            'b' => 'B',
            'capitalized' => 'C',
            'dddddd' => 'D'
        ];

        $this->assertSame($expected, $en->content('en')->data());

        $de = $page->update([
            'a' => 'A',
            'b' => 'B',
            'capitalized' => 'C',
            'dDdDdD' => 'D'
        ], 'de');

        $expected = [
            'a' => 'A',
            'b' => null,
            'capitalized' => null,
            'dddddd' => 'D'
        ];

        $this->assertSame($expected, $de->content('de')->data());

        Dir::remove($fixtures);
    }
}
