<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;

class KirbyTagsTest extends TestCase
{
    public function dataProvider()
    {
        $tests = [];

        foreach (Dir::read($root = __DIR__ . '/fixtures/kirbytext') as $dir) {
            $kirbytext = F::read($root . '/' . $dir . '/test.txt');
            $expected  = F::read($root . '/' . $dir . '/expected.html');

            $tests[] = [trim($kirbytext), trim($expected)];
        }

        return $tests;
    }

    /**
     * @dataProvider dataProvider
     */
    public function testWithMarkdown($kirbytext, $expected)
    {
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'options' => [
                'markdown' => [
                    'extra' => false
                ]
            ]
        ]);

        $this->assertEquals($expected, $kirby->kirbytext($kirbytext));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testWithMarkdownExtra($kirbytext, $expected)
    {
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'options' => [
                'markdown' => [
                    'extra' => true
                ]
            ]
        ]);

        $this->assertEquals($expected, $kirby->kirbytext($kirbytext));
    }

    public function testImageWithoutFigure()
    {
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'options' => [
                'kirbytext' => [
                    'image' => [
                        'figure' => false
                    ]
                ]
            ]
        ]);

        $expected = '<img alt="" src="https://test.com/something.jpg">';

        $this->assertEquals($expected, $kirby->kirbytext('(image: https://test.com/something.jpg)'));
    }

    public function testFileWithinFile()
    {
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null',
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'a',
                        'files' => [
                            [
                                'filename' => 'a.jpg',
                                'content' => [
                                    'caption' => '(file: b.jpg)'
                                ]
                            ],
                            [
                                'filename' => 'b.jpg'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $a = $kirby->file('a/a.jpg');
        $b = $kirby->file('a/b.jpg');
        $expected = '<p><a download href="' . $b->url() . '">b.jpg</a></p>';

        $this->assertEquals($expected, (string)$a->caption()->kt());
    }

    public function testLinkWithLangAttribute()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'urls' => [
                'index' => 'https://getkirby.com'
            ],
            'languages' => [
                'en' => [
                    'code' => 'en'
                ],
                'de' => [
                    'code' => 'de'
                ]
            ],
            'site' => [
                'children' => [
                    ['slug' => 'a']
                ]
            ]
        ]);

        $this->assertEquals('<a href="https://getkirby.com/en/a">getkirby.com/en/a</a>', $app->kirbytags('(link: a lang: en)'));
        $this->assertEquals('<a href="https://getkirby.com/de/a">getkirby.com/de/a</a>', $app->kirbytags('(link: a lang: de)'));
    }

    public function testHooks()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'hooks' => [
                'kirbytags:before' => function ($text, $data, $options) {
                    return 'before';
                },
            ]
        ]);

        $this->assertEquals('before', $app->kirbytags('test'));

        $app = $app->clone([
            'hooks' => [
                'kirbytags:after' => function ($text, $data, $options) {
                    return 'after';
                },
            ]
        ]);

        $this->assertEquals('after', $app->kirbytags('test'));
    }
}
