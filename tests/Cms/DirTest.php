<?php

namespace Kirby\Cms;

use Kirby\Toolkit\F;
use PHPUnit\Framework\TestCase;

class DirTest extends TestCase
{

    public function setUp()
    {
        Dir::remove($this->fixtures = __DIR__ . '/fixtures/DirTest');
        Dir::make($this->fixtures);
    }

    public function tearDown()
    {
        Dir::remove($this->fixtures);
    }

    public function create(array $items)
    {

        foreach ($items as $item) {
            $root = $this->fixtures . '/' . $item;

            if ($extension = F::extension($item)) {
                touch($root);
            } else {
                Dir::make($root);
            }
        }

        return Dir::inventory($this->fixtures);
    }

    public function testInventory()
    {

        $inventory = $this->create([
            '1_project-a',
            '2_project-b',
            'cover.jpg',
            'cover.jpg.txt',
            'projects.txt'
        ]);

        $this->assertEquals('project-a', $inventory['children'][0]['slug']);
        $this->assertEquals(1, $inventory['children'][0]['num']);

        $this->assertEquals('project-b', $inventory['children'][1]['slug']);
        $this->assertEquals(2, $inventory['children'][1]['num']);

        $this->assertEquals('cover.jpg', $inventory['files']['cover.jpg']['filename']);
        $this->assertEquals('jpg', $inventory['files']['cover.jpg']['extension']);

        $this->assertEquals('projects', $inventory['template']);

    }

    public function testChildSorting()
    {
        $inventory = $this->create([
            '1_project-c',
            '10_project-b',
            '11_project-a',
        ]);

        $this->assertEquals('project-c', $inventory['children'][0]['slug']);
        $this->assertEquals('project-b', $inventory['children'][1]['slug']);
        $this->assertEquals('project-a', $inventory['children'][2]['slug']);
    }

    public function testFileSorting()
    {
        $inventory = $this->create([
            '1-c.jpg',
            '10-b.jpg',
            '11-a.jpg',
        ]);

        $files = array_values($inventory['files']);

        $this->assertEquals('1-c.jpg', $files[0]['filename']);
        $this->assertEquals('10-b.jpg', $files[1]['filename']);
        $this->assertEquals('11-a.jpg', $files[2]['filename']);
    }

    public function testMissingTemplate()
    {
        $inventory = $this->create([
            'cover.jpg',
            'cover.jpg.txt'
        ]);

        $this->assertEquals('cover.jpg', $inventory['files']['cover.jpg']['filename']);
        $this->assertEquals('default', $inventory['template']);
    }

    public function testTemplateWithDotInFilename()
    {
        $inventory = $this->create([
            'cover.jpg',
            'cover.jpg.txt',
            'article.video.txt'
        ]);

        $this->assertEquals('cover.jpg', $inventory['files']['cover.jpg']['filename']);
        $this->assertEquals('article.video', $inventory['template']);
    }

}
