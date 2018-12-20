<?php

namespace Kirby\Cms;

class FileSiblingsTest extends TestCase
{
    protected function collection()
    {
        return [
            ['filename' => 'cover.jpg',     'template' => 'cover'],
            ['filename' => 'gallery-1.jpg', 'template' => 'gallery'],
            ['filename' => 'gallery-2.jpg', 'template' => 'gallery'],
            ['filename' => 'gallery-3.jpg', 'template' => 'gallery']
        ];
    }

    protected function files()
    {
        return (new Page([
            'slug'  => 'test',
            'files' => $this->collection(),
        ]))->files();
    }

    public function testHasNext()
    {
        $collection = $this->files();

        $this->assertTrue($collection->first()->hasNext());
        $this->assertFalse($collection->last()->hasNext());
    }

    public function testHasPrev()
    {
        $collection = $this->files();

        $this->assertTrue($collection->last()->hasPrev());
        $this->assertFalse($collection->first()->hasPrev());
    }

    public function testIndexOf()
    {
        $collection = $this->files();

        $this->assertEquals(0, $collection->first()->indexOf());
        $this->assertEquals(1, $collection->nth(1)->indexOf());
        $this->assertEquals(3, $collection->last()->indexOf());
    }

    public function testIsFirst()
    {
        $collection = $this->files();

        $this->assertTrue($collection->first()->isFirst());
        $this->assertFalse($collection->last()->isFirst());
    }

    public function testIsLast()
    {
        $collection = $this->files();

        $this->assertTrue($collection->last()->isLast());
        $this->assertFalse($collection->first()->isLast());
    }

    public function testIsNth()
    {
        $collection = $this->files();

        $this->assertTrue($collection->first()->isNth(0));
        $this->assertTrue($collection->nth(1)->isNth(1));
        $this->assertTrue($collection->last()->isNth($collection->count() - 1));
    }

    public function testNext()
    {
        $collection = $this->files();

        $this->assertEquals($collection->first()->next(), $collection->nth(1));
    }

    public function testNextAll()
    {
        $collection = $this->files();
        $first      = $collection->first();

        $this->assertCount(3, $first->nextAll());

        $this->assertEquals($first->nextAll()->first(), $collection->nth(1));
        $this->assertEquals($first->nextAll()->last(), $collection->nth(3));
    }

    public function testPrev()
    {
        $collection = $this->files();

        $this->assertEquals($collection->last()->prev(), $collection->nth(2));
    }

    public function testPrevAll()
    {
        $collection = $this->files();
        $last       = $collection->last();

        $this->assertCount(3, $last->prevAll());

        $this->assertEquals($last->prevAll()->first(), $collection->nth(0));
        $this->assertEquals($last->prevAll()->last(), $collection->nth(2));
    }

    public function testSiblings()
    {
        $files    = $this->files();
        $file     = $files->nth(1);
        $siblings = $files->not($file);

        $this->assertEquals($files, $file->siblings());
        $this->assertEquals($siblings, $file->siblings(false));
    }

    public function testTemplateSiblings()
    {
        $page = new Page([
            'slug'  => 'test',
            'files' => [
                [
                    'filename' => 'a.jpg',
                    'template' => 'gallery'
                ],
                [
                    'filename' => 'b.jpg',
                    'template' => 'cover'
                ],
                [
                    'filename' => 'c.jpg',
                    'template' => 'gallery'
                ],
                [
                    'filename' => 'd.jpg',
                    'template' => 'gallery'
                ]
            ]
        ]);

        $files    = $page->files();
        $siblings = $files->first()->templateSiblings();

        $this->assertTrue($siblings->has('test/a.jpg'));
        $this->assertTrue($siblings->has('test/c.jpg'));
        $this->assertTrue($siblings->has('test/d.jpg'));

        $this->assertFalse($siblings->has('test/b.jpg'));

        $siblings = $files->first()->templateSiblings(false);

        $this->assertTrue($siblings->has('test/c.jpg'));
        $this->assertTrue($siblings->has('test/d.jpg'));

        $this->assertFalse($siblings->has('test/a.jpg'));
        $this->assertFalse($siblings->has('test/b.jpg'));
    }
}
