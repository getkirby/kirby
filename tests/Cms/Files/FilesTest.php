<?php

namespace Kirby\Cms;

class FilesTest extends TestCase
{
    public function testAddFile()
    {
        $parent = new Page(['slug' => 'test']);

        $files = Files::factory([
            ['filename' => 'a.jpg']
        ], $parent);

        $file = new File([
            'filename' => 'b.jpg'
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
}
