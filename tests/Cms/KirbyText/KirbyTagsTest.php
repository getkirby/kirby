<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;
use PHPUnit\Framework\TestCase;

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

    public function testImageWithCaption()
    {
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);

        $expected = '<figure><img alt="" src="/myimage.jpg"><figcaption>This is an <em>awesome</em> image and this a <a href="">link</a></figcaption></figure>';

        $this->assertEquals($expected, $kirby->kirbytext('(image: myimage.jpg caption: This is an *awesome* image and this a <a href="">link</a>)'));
    }

    public function testImageWithFileLink()
    {
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null',
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'a',
                        'content' => [
                            'text' => '(image: image.jpg link: document.pdf)'
                        ],
                        'files' => [
                            [
                                'filename' => 'image.jpg',
                            ],
                            [
                                'filename' => 'document.pdf',
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $page  = $kirby->page('a');
        $image = $page->file('image.jpg');
        $doc   = $page->file('document.pdf');

        $expected = '<figure><a href="' . $doc->url() . '"><img alt="" src="' . $image->url() . '"></a></figure>';

        $this->assertEquals($expected, $page->text()->kt()->value());
    }

    public function testFile()
    {
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null',
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'a',
                        'content' => [
                            'text' => '(file: a.jpg)'
                        ],
                        'files' => [
                            [
                                'filename' => 'a.jpg',
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $page = $kirby->page('a');
        $file = $page->file('a.jpg');

        $expected = '<p><a download href="' . $file->url() . '">a.jpg</a></p>';

        $this->assertEquals($expected, $page->text()->kt()->value());
    }

    public function testFileWithDisabledDownloadOption()
    {
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null',
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'a',
                        'content' => [
                            'text' => '(file: a.jpg download: false)'
                        ],
                        'files' => [
                            [
                                'filename' => 'a.jpg',
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $page = $kirby->page('a');
        $file = $page->file('a.jpg');

        $expected = '<p><a href="' . $file->url() . '">a.jpg</a></p>';

        $this->assertEquals($expected, $page->text()->kt()->value());
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

        $this->assertEquals($expected, $a->caption()->kt()->value());
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

    public function testLinkWithHash()
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

        $this->assertEquals('<a href="https://getkirby.com/en/a">getkirby.com/en/a</a>', $app->kirbytags('(link: a)'));
        $this->assertEquals('<a href="https://getkirby.com/de/a">getkirby.com/de/a</a>', $app->kirbytags('(link: a lang: de)'));
        $this->assertEquals('<a href="https://getkirby.com/en/a#anchor">getkirby.com/en/a</a>', $app->kirbytags('(link: a#anchor lang: en)'));
        $this->assertEquals('<a href="https://getkirby.com/de/a#anchor">getkirby.com/de/a</a>', $app->kirbytags('(link: a#anchor lang: de)'));
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
