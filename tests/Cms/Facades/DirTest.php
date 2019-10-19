<?php

namespace Kirby\Cms;

use Kirby\Toolkit\A;
use Kirby\Toolkit\F;
use PHPUnit\Framework\TestCase;

class DirTest extends TestCase
{
    protected $fixtures;

    public function setUp(): void
    {
        Dir::remove($this->fixtures = __DIR__ . '/fixtures/DirTest');
        Dir::make($this->fixtures);
    }

    public function tearDown(): void
    {
        Dir::remove($this->fixtures);
    }

    public function create(array $items, ...$args)
    {
        foreach ($items as $item) {
            $root = $this->fixtures . '/' . $item;

            if ($extension = F::extension($item)) {
                F::write($root, '');
            } else {
                Dir::make($root);
            }
        }

        return Dir::inventory($this->fixtures, ...$args);
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

    public function testInventoryWithSkippedFiles()
    {
        $inventory = $this->create([
            'valid.jpg',
            'skipped.html',
            'skipped.htm',
            'skipped.php'
        ]);

        $expected = [
            'valid.jpg'
        ];

        $this->assertEquals($expected, A::pluck($inventory['files'], 'filename'));
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

    public function testChildWithLeadingZero()
    {
        $inventory = $this->create([
            '01_project-c',
            '02_project-b',
            '03_project-a',
        ]);

        $this->assertEquals('project-c', $inventory['children'][0]['slug']);
        $this->assertEquals(1, $inventory['children'][0]['num']);

        $this->assertEquals('project-b', $inventory['children'][1]['slug']);
        $this->assertEquals(2, $inventory['children'][1]['num']);

        $this->assertEquals('project-a', $inventory['children'][2]['slug']);
        $this->assertEquals(3, $inventory['children'][2]['num']);
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

    public function testExtension()
    {
        $inventory = $this->create([
            'cover.jpg',
            'cover.jpg.md',
            'article.md'
        ], 'md');

        $this->assertEquals('cover.jpg', $inventory['files']['cover.jpg']['filename']);
        $this->assertEquals('article', $inventory['template']);
    }

    public function testIgnore()
    {
        $inventory = $this->create([
            'cover.jpg',
            'article.txt'
        ], 'txt', ['cover.jpg']);

        $this->assertCount(0, $inventory['files']);
        $this->assertEquals('article', $inventory['template']);
    }

    public function testMultilang()
    {
        $inventory = $this->create([
            'cover.jpg',
            'cover.jpg.en.txt',
            'article.en.txt',
            'article.de.txt'
        ], 'txt', null, true);

        $this->assertEquals('cover.jpg', $inventory['files']['cover.jpg']['filename']);
        $this->assertEquals('article', $inventory['template']);
    }

    public function testModels()
    {
        Page::$models = [
            'a' => 'A',
            'b' => 'A'
        ];

        $inventory = $this->create([
            'child-with-model-a/a.txt',
            'child-with-model-b/b.txt',
            'child-without-model-c/c.txt'
        ]);

        $this->assertEquals('a', $inventory['children'][0]['model']);
        $this->assertEquals('b', $inventory['children'][1]['model']);
        $this->assertEquals(null, $inventory['children'][2]['model']);

        Page::$models = [];
    }

    public function testMultilangModels()
    {
        new App([
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
                    'name'    => 'Deutsch',
                ]
            ]
        ]);

        Page::$models = [
            'a' => 'A',
            'b' => 'A'
        ];

        $inventory = $this->create([
            'child-with-model-a/a.de.txt',
            'child-with-model-a/a.en.txt',
            'child-with-model-b/b.de.txt',
            'child-with-model-b/b.en.txt',
            'child-without-model-c/c.de.txt',
            'child-without-model-c/c.en.txt'
        ], 'txt', null, true);

        $this->assertEquals('a', $inventory['children'][0]['model']);
        $this->assertEquals('b', $inventory['children'][1]['model']);
        $this->assertEquals(null, $inventory['children'][2]['model']);

        Page::$models = [];
    }
}
