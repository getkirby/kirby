<?php

namespace Kirby\Text;

use Exception;
use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
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

    public function testParse()
    {
        KirbyTag::$types = [
            'test' => [
                'html' => function () {
                    return 'test';
                }
            ]
        ];

        $this->assertSame('test', KirbyTags::parse('(test: foo)'));
        $this->assertSame('test', KirbyTags::parse('(Test: foo)'));
        $this->assertSame('test', KirbyTags::parse('(TEST: foo)'));
        $this->assertSame('test', KirbyTags::parse('(tEsT: foo)'));
    }

    public function testParseWithValue()
    {
        KirbyTag::$types = [
            'test' => [
                'html' => function ($tag) {
                    return $tag->value;
                }
            ]
        ];

        $this->assertSame('foo', KirbyTags::parse('(test: foo)'));
        $this->assertSame('foo', KirbyTags::parse('(Test: foo)'));
        $this->assertSame('foo', KirbyTags::parse('(TEST: foo)'));
    }

    public function testParseWithAttribute()
    {
        KirbyTag::$types = [
            'test' => [
                'attr' => ['a'],
                'html' => function ($tag) {
                    return $tag->value . '|' . $tag->a;
                }
            ]
        ];

        $this->assertSame('foo|bar', KirbyTags::parse('(test: foo a: bar)'));
        $this->assertSame('foo|bar', KirbyTags::parse('(Test: foo A: bar)'));
        $this->assertSame('foo|bar', KirbyTags::parse('(TEST: foo a: bar)'));
    }

    public function testParseWithException()
    {
        KirbyTag::$types = [
            'test' => [
                'html' => function () {
                    throw new Exception('Just for fun');
                }
            ],
            'invalidargument' => [
                'html' => function () {
                    throw new InvalidArgumentException('Just for fun');
                }
            ],
            'undefined' => [
                'html' => function () {
                    throw new InvalidArgumentException('Undefined tag type: undefined');
                }
            ]
        ];

        $this->assertSame('(test: foo)', KirbyTags::parse('(test: foo)'));
        $this->assertSame('(invalidargument: foo)', KirbyTags::parse('(invalidargument: foo)'));
        $this->assertSame('(undefined: foo)', KirbyTags::parse('(undefined: foo)'));
    }

    public function testParseWithExceptionDebug1()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Just for fun');

        KirbyTag::$types = [
            'test' => [
                'html' => function () {
                    throw new Exception('Just for fun');
                }
            ]
        ];

        KirbyTags::parse('(test: foo)', [], ['debug' => true]);
    }

    public function testParseWithExceptionDebug2()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Just for fun');

        KirbyTag::$types = [
            'invalidargument' => [
                'html' => function () {
                    throw new InvalidArgumentException('Just for fun');
                }
            ]
        ];

        KirbyTags::parse('(invalidargument: foo)', [], ['debug' => true]);
    }

    public function testParseWithExceptionDebug3()
    {
        KirbyTag::$types = [
            'undefined' => [
                'html' => function () {
                    throw new InvalidArgumentException('Undefined tag type: undefined');
                }
            ]
        ];

        $this->assertSame('(undefined: foo)', KirbyTags::parse('(undefined: foo)', [], ['debug' => true]));
    }

    public function testParseWithBrackets()
    {
        KirbyTag::$types = [
            'test' => [
                'attr' => ['a'],
                'html' => function ($tag) {
                    $value = $tag->value;

                    if (empty($tag->a) === false) {
                        $value .= ' - ' . $tag->a;
                    }

                    return $value;
                }
            ]
        ];

        $this->assertSame('foo(bar)', KirbyTags::parse('(test: foo(bar))'));
        $this->assertSame('foo(bar) - hello(world)', KirbyTags::parse('(test: foo(bar) a: hello(world))'));
        $this->assertSame('foo(bar) hello', KirbyTags::parse('(test: foo(bar) hello)'));
        $this->assertSame('foo(bar hello(world))', KirbyTags::parse('(test: foo(bar hello(world)))'));
        $this->assertSame('foo - (bar)', KirbyTags::parse('(test: foo a: (bar))'));
        $this->assertSame('(bar)', KirbyTags::parse('(test: (bar))'));
        // will not parse if brackets don't match
        $this->assertSame('(test: foo (bar)', KirbyTags::parse('(test: foo (bar)'));
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

    public function testVideoLocal()
    {
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null',
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'test',
                        'content' => [
                            'text' => '(video: sample.mp4)'
                        ],
                        'files' => [
                            ['filename' => 'sample.mp4']
                        ]
                    ]
                ]
            ]
        ]);

        $page  = $kirby->page('test');
        $image = $page->file('sample.mp4');

        $expected = '<figure class="video"><video controls><source src="' . $image->url() . '" type="video/mp4"></video></figure>';

        $this->assertSame($expected, $page->text()->kt()->value());
    }

    public function testVideoInlineAttrs()
    {
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null',
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'test',
                        'content' => [
                            'text' => '(video: sample.mp4
                                autoplay: true
                                caption: Lorem ipsum
                                controls: false
                                class: video-class
                                height: 350
                                loop: true
                                muted: true
                                poster: sample.jpg
                                preload: auto
                                style: border: none
                                width: 500)'
                        ],
                        'files' => [
                            ['filename' => 'sample.mp4'],
                            ['filename' => 'sample.jpg']
                        ]
                    ]
                ]
            ]
        ]);

        $page  = $kirby->page('test');

        $image = $page->file('sample.jpg');
        $video = $page->file('sample.mp4');

        $expected = '<figure class="video-class" style="border: none"><video autoplay height="350" loop muted poster="' . $image->url() . '" preload="auto" width="500"><source src="' . $video->url() . '" type="video/mp4"></video><figcaption>Lorem ipsum</figcaption></figure>';
        $this->assertSame($expected, $page->text()->kt()->value());
    }

    public function testVideoPredefinedAttrs()
    {
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null',
            ],
            'options' => [
                'kirbytext' => [
                    'video' => [
                        'autoplay' => true,
                        'caption'  => 'Lorem ipsum',
                        'controls' => false,
                        'class'    => 'video-class',
                        'height'   => 350,
                        'loop'     => true,
                        'muted'    => true,
                        'poster'   => 'sample.jpg',
                        'preload'  => 'auto',
                        'style'    => 'border: none',
                        'width'    => 500
                    ]
                ]
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'test',
                        'content' => [
                            'text' => '(video: sample.mp4)'
                        ],
                        'files' => [
                            ['filename' => 'sample.mp4'],
                            ['filename' => 'sample.jpg']
                        ]
                    ]
                ]
            ]
        ]);

        $page  = $kirby->page('test');

        $image = $page->file('sample.jpg');
        $video = $page->file('sample.mp4');

        $expected = '<figure class="video-class" style="border: none"><video autoplay height="350" loop muted poster="' . $image->url() . '" preload="auto" width="500"><source src="' . $video->url() . '" type="video/mp4"></video><figcaption>Lorem ipsum</figcaption></figure>';
        $this->assertSame($expected, $page->text()->kt()->value());
    }

    public function testVideoOptions()
    {
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null',
            ],
            'options' => [
                'kirbytext' => [
                    'video' => [
                        'options'  => [
                            'youtube' => [
                                'controls' => 0
                            ]
                        ]
                    ]
                ]
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'test',
                        'content' => [
                            'text' => '(video: https://www.youtube.com/watch?v=VhP7ZzZysQg)'
                        ]
                    ]
                ]
            ]
        ]);

        $page  = $kirby->page('test');

        $expected = '<figure class="video"><iframe allowfullscreen src="https://youtube.com/embed/VhP7ZzZysQg?controls=0"></iframe></figure>';
        $this->assertSame($expected, $page->text()->kt()->value());
    }

    public function testVideoRemote()
    {
        $kirby = new App([
            'roots' => [
                'index' => '/dev/null',
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'test',
                        'content' => [
                            'text' => '(video: https://getkirby.com/sample.mp4)'
                        ]
                    ]
                ]
            ]
        ]);

        $page  = $kirby->page('test');

        $expected = '<figure class="video"><video controls><source src="https://getkirby.com/sample.mp4" type="video/mp4"></video></figure>';
        $this->assertSame($expected, $page->text()->kt()->value());
    }
}
