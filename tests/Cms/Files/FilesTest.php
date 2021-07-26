<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;

/**
 * @coversDefaultClass \Kirby\Cms\Files
 */
class FilesTest extends TestCase
{
    public function testAddFile()
    {
        $parent = new Page(['slug' => 'test']);

        $files = Files::factory([
            ['filename' => 'a.jpg']
        ], $parent);

        $file = new File([
            'filename' => 'b.jpg',
            'parent'   => $parent
        ]);

        $result = $files->add($file);

        $this->assertCount(2, $result);
        $this->assertEquals('a.jpg', $result->nth(0)->filename());
        $this->assertEquals('b.jpg', $result->nth(1)->filename());
    }

    public function testAddCollection()
    {
        $parent = new Page(['slug' => 'test']);

        $a = Files::factory([
            ['filename' => 'a.jpg']
        ], $parent);

        $b = Files::factory([
            ['filename' => 'b.jpg'],
            ['filename' => 'c.jpg']
        ], $parent);

        $c = $a->add($b);

        $this->assertCount(3, $c);
        $this->assertEquals('a.jpg', $c->nth(0)->filename());
        $this->assertEquals('b.jpg', $c->nth(1)->filename());
        $this->assertEquals('c.jpg', $c->nth(2)->filename());
    }

    public function testAddById()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'a',
                        'files' => [
                            ['filename' => 'a.jpg'],
                            ['filename' => 'b.jpg'],
                        ]
                    ],
                    [
                        'slug' => 'b',
                        'files' => [
                            ['filename' => 'a.jpg'],
                        ]
                    ]
                ]
            ]
        ]);

        $files = $app->page('a')->files()->add('b/a.jpg');

        $this->assertCount(3, $files);
        $this->assertEquals('a/a.jpg', $files->nth(0)->id());
        $this->assertEquals('a/b.jpg', $files->nth(1)->id());
        $this->assertEquals('b/a.jpg', $files->nth(2)->id());
    }

    public function testAddNull()
    {
        $files = new Files();
        $this->assertCount(0, $files);

        $files->add(null);

        $this->assertCount(0, $files);
    }

    public function testAddFalse()
    {
        $files = new Files();
        $this->assertCount(0, $files);

        $files->add(false);

        $this->assertCount(0, $files);
    }

    public function testAddInvalidObject()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('You must pass a Files or File object or an ID of an existing file to the Files collection');

        $site  = new Site();
        $files = new Files();
        $files->add($site);
    }

    /**
     * @covers ::niceSize
     * @covers ::size
     */
    public function testSize()
    {
        $tmp = __DIR__ . '/tmp';
        Dir::make($tmp);

        $app = new App([
            'roots' => [
                'index' => $tmp
            ],
            'site' => [
                'children' => [
                    ['slug' => 'test']
                ]
            ]
        ]);

        F::write($a = $tmp . '/content/test/a.txt', 'foo');
        F::write($b = $tmp . '/content/test/b.txt', 'bar');

        $files = Files::factory([
            ['filename' => 'a.txt', 'root' => $a],
            ['filename' => 'b.txt', 'root' => $b]
        ], $app->page('test'));


        $this->assertSame(6, $files->size());
        $this->assertSame('6 B', $files->niceSize());

        Dir::remove($tmp);
    }

    /**
     * @covers ::sorted
     */
    public function testSortedByFilename()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'site' => [
                'files' => [
                    ['filename' => 'b.jpg'],
                    ['filename' => 'a.jpg']
                ]
            ]
        ]);

        $files = $app->site()->files()->sorted();

        $this->assertSame('a.jpg', $files->first()->filename());
        $this->assertSame('b.jpg', $files->last()->filename());
    }

    /**
     * @covers ::sorted
     */
    public function testSortedBySort()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'site' => [
                'files' => [
                    ['filename' => 'a.jpg', 'content' => ['sort' => 2]],
                    ['filename' => 'b.jpg', 'content' => ['sort' => 1]]
                ]
            ]
        ]);

        $files = $app->site()->files()->sorted();

        $this->assertSame('b.jpg', $files->first()->filename());
        $this->assertSame('a.jpg', $files->last()->filename());
    }
}
