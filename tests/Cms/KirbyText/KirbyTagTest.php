<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class KirbyTagTest extends TestCase
{
    public function testWithParent()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null',
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'a',
                        'files' => [
                            [
                                'filename' => 'a.jpg'
                            ],
                            [
                                'filename' => 'b.jpg'
                            ],
                            [
                                'filename' => 'c.jpg'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $page = $app->page('a');
        $image = $page->image('b.jpg');
        $expected = '<figure><img alt="" src="/media/pages/a/' . $image->mediaHash() . '/b.jpg"></figure>';

        $this->assertSame($expected, $app->kirbytag('image', 'b.jpg', [], [
            'parent' => $page,
        ]));
    }
}
