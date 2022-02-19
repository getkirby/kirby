<?php

namespace Kirby\Filesystem;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

use PHPUnit\Framework\TestCase as TestCase;

/**
 * @coversDefaultClass \Kirby\Filesystem\Dir
 */
class DirTest extends TestCase
{
    public const FIXTURES = __DIR__ . '/fixtures/dir';

    protected $fixtures = __DIR__ . '/fixtures/dir';
    protected $tmp = __DIR__ . '/tmp';
    protected $moved = __DIR__ . '/moved';

    public function tearDown(): void
    {
        Dir::remove($this->tmp);
        Dir::remove($this->moved);
    }

    protected function create(array $items, ...$args)
    {
        foreach ($items as $item) {
            $root = $this->tmp . '/' . $item;

            if ($extension = F::extension($item)) {
                F::write($root, '');
            } else {
                Dir::make($root);
            }
        }

        return Dir::inventory($this->tmp, ...$args);
    }

    /**
     * @covers ::copy
     */
    public function testCopy()
    {
        $src    = $this->fixtures . '/copy';
        $target = $this->tmp . '/copy';

        $result = Dir::copy($src, $target);

        $this->assertTrue($result);

        $this->assertTrue(file_exists($target . '/a.txt'));
        $this->assertTrue(file_exists($target . '/subfolder/b.txt'));
    }

    /**
     * @covers ::copy
     */
    public function testCopyNonRecursive()
    {
        $src    = $this->fixtures . '/copy';
        $target = $this->tmp . '/copy';

        $result = Dir::copy($src, $target, false);

        $this->assertTrue($result);

        $this->assertTrue(file_exists($target . '/a.txt'));
        $this->assertFalse(file_exists($target . '/subfolder/b.txt'));
    }

    /**
     * @covers ::copy
     */
    public function testCopyIgnore()
    {
        $src    = $this->fixtures . '/copy';
        $target = $this->tmp . '/copy';

        $result = Dir::copy($src, $target, true, [$src . '/subfolder/b.txt']);

        $this->assertTrue($result);

        $this->assertTrue(file_exists($target . '/a.txt'));
        $this->assertTrue(is_dir($target . '/subfolder'));
        $this->assertFalse(file_exists($target . '/subfolder/b.txt'));
    }

    /**
     * @covers ::copy
     */
    public function testCopyMissingSource()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('The directory "/does-not-exist" does not exist');

        $src    = '/does-not-exist';
        $target = $this->tmp . '/copy';

        Dir::copy($src, $target);
    }

    /**
     * @covers ::copy
     */
    public function testCopyExistingTarget()
    {
        $src    = $this->fixtures . '/copy';
        $target = $this->fixtures . '/copy';

        $this->expectException('Exception');
        $this->expectExceptionMessage('The target directory "' . $target . '" exists');

        Dir::copy($src, $target);
    }

    /**
     * @covers ::copy
     */
    public function testCopyInvalidTarget()
    {
        $src    = $this->fixtures . '/copy';
        $target = '';

        $this->expectException('Exception');
        $this->expectExceptionMessage('The target directory "' . $target . '" could not be created');

        Dir::copy($src, $target);
    }

    /**
     * @covers ::exists
     */
    public function testExists()
    {
        $this->assertFalse(Dir::exists($this->tmp));
        Dir::make($this->tmp);
        $this->assertTrue(Dir::exists($this->tmp));
    }

    /**
     * @covers ::index
     */
    public function testIndex()
    {
        Dir::make($dir = $this->tmp);
        Dir::make($sub = $this->tmp . '/sub');

        F::write($a = $this->tmp . '/a.txt', 'test');
        F::write($b = $this->tmp . '/b.txt', 'test');

        $expected = [
            'a.txt',
            'b.txt',
            'sub',
        ];

        $this->assertSame($expected, Dir::index($dir));
    }

    /**
     * @covers ::index
     */
    public function testIndexRecursive()
    {
        Dir::make($dir = $this->tmp);
        Dir::make($sub = $this->tmp . '/sub');
        Dir::make($subsub = $this->tmp . '/sub/sub');

        F::write($a = $this->tmp . '/a.txt', 'test');
        F::write($b = $this->tmp . '/sub/b.txt', 'test');
        F::write($c = $this->tmp . '/sub/sub/c.txt', 'test');

        $expected = [
            'a.txt',
            'sub',
            'sub/b.txt',
            'sub/sub',
            'sub/sub/c.txt'
        ];

        $this->assertSame($expected, Dir::index($dir, true));
    }

    /**
     * @covers ::isWritable
     */
    public function testIsWritable()
    {
        Dir::make($this->tmp);

        $this->assertSame(is_writable($this->tmp), Dir::isWritable($this->tmp));
    }

    /**
     * @covers ::inventory
     */
    public function testInventory()
    {
        $inventory = $this->create([
            '1_project-a',
            '2_project-b',
            'cover.jpg',
            'cover.jpg.txt',
            'projects.txt'
        ]);

        $this->assertSame('project-a', $inventory['children'][0]['slug']);
        $this->assertSame(1, $inventory['children'][0]['num']);

        $this->assertSame('project-b', $inventory['children'][1]['slug']);
        $this->assertSame(2, $inventory['children'][1]['num']);

        $this->assertSame('cover.jpg', $inventory['files']['cover.jpg']['filename']);
        $this->assertSame('jpg', $inventory['files']['cover.jpg']['extension']);

        $this->assertSame('projects', $inventory['template']);
    }

    /**
     * @covers ::inventory
     */
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

        $this->assertSame($expected, A::pluck($inventory['files'], 'filename'));
    }

    /**
     * @covers ::inventory
     */
    public function testInventoryChildSorting()
    {
        $inventory = $this->create([
            '1_project-c',
            '10_project-b',
            '11_project-a',
        ]);

        $this->assertSame('project-c', $inventory['children'][0]['slug']);
        $this->assertSame('project-b', $inventory['children'][1]['slug']);
        $this->assertSame('project-a', $inventory['children'][2]['slug']);
    }

    /**
     * @covers ::inventory
     */
    public function testInventoryChildWithLeadingZero()
    {
        $inventory = $this->create([
            '01_project-c',
            '02_project-b',
            '03_project-a',
        ]);

        $this->assertSame('project-c', $inventory['children'][0]['slug']);
        $this->assertSame(1, $inventory['children'][0]['num']);

        $this->assertSame('project-b', $inventory['children'][1]['slug']);
        $this->assertSame(2, $inventory['children'][1]['num']);

        $this->assertSame('project-a', $inventory['children'][2]['slug']);
        $this->assertSame(3, $inventory['children'][2]['num']);
    }

    /**
     * @covers ::inventory
     */
    public function testInventoryFileSorting()
    {
        $inventory = $this->create([
            '1-c.jpg',
            '10-b.jpg',
            '11-a.jpg',
        ]);

        $files = array_values($inventory['files']);

        $this->assertSame('1-c.jpg', $files[0]['filename']);
        $this->assertSame('10-b.jpg', $files[1]['filename']);
        $this->assertSame('11-a.jpg', $files[2]['filename']);
    }

    /**
     * @covers ::inventory
     */
    public function testInventoryMissingTemplate()
    {
        $inventory = $this->create([
            'cover.jpg',
            'cover.jpg.txt'
        ]);

        $this->assertSame('cover.jpg', $inventory['files']['cover.jpg']['filename']);
        $this->assertSame('default', $inventory['template']);
    }

    /**
     * @covers ::inventory
     */
    public function testInventoryTemplateWithDotInFilename()
    {
        $inventory = $this->create([
            'cover.jpg',
            'cover.jpg.txt',
            'article.video.txt'
        ]);

        $this->assertSame('cover.jpg', $inventory['files']['cover.jpg']['filename']);
        $this->assertSame('article.video', $inventory['template']);
    }

    /**
     * @covers ::inventory
     */
    public function testInventoryExtension()
    {
        $inventory = $this->create([
            'cover.jpg',
            'cover.jpg.md',
            'article.md'
        ], 'md');

        $this->assertSame('cover.jpg', $inventory['files']['cover.jpg']['filename']);
        $this->assertSame('article', $inventory['template']);
    }

    /**
     * @covers ::inventory
     */
    public function testInventoryIgnore()
    {
        $inventory = $this->create([
            'cover.jpg',
            'article.txt'
        ], 'txt', ['cover.jpg']);

        $this->assertCount(0, $inventory['files']);
        $this->assertSame('article', $inventory['template']);
    }

    /**
     * @covers ::inventory
     */
    public function testInventoryMultilang()
    {
        $inventory = $this->create([
            'cover.jpg',
            'cover.jpg.en.txt',
            'article.en.txt',
            'article.de.txt'
        ], 'txt', null, true);

        $this->assertSame('cover.jpg', $inventory['files']['cover.jpg']['filename']);
        $this->assertSame('article', $inventory['template']);
    }

    /**
     * @covers ::inventory
     * @covers ::inventoryModels
     */
    public function testInventoryModels()
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

        $this->assertSame('a', $inventory['children'][0]['model']);
        $this->assertSame('b', $inventory['children'][1]['model']);
        $this->assertSame(null, $inventory['children'][2]['model']);

        Page::$models = [];
    }

    /**
     * @covers ::inventory
     * @covers ::inventoryModels
     */
    public function testInventoryMultilangModels()
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

        $this->assertSame('a', $inventory['children'][0]['model']);
        $this->assertSame('b', $inventory['children'][1]['model']);
        $this->assertSame(null, $inventory['children'][2]['model']);

        Page::$models = [];
    }

    /**
     * @covers ::make
     */
    public function testMake()
    {
        $this->assertTrue(Dir::make($this->tmp));
        $this->assertFalse(Dir::make(''));
    }

    /**
     * @covers ::make
     */
    public function testMakeFileExists()
    {
        $test = $this->tmp . '/test';

        $this->expectException('Exception');
        $this->expectExceptionMessage('A file with the name "' . $test . '" already exists');

        F::write($test, '');
        Dir::make($test);
    }

    /**
     * @covers ::modified
     */
    public function testModified()
    {
        Dir::make($this->tmp);

        $this->assertTrue(is_int(Dir::modified($this->tmp)));
    }

    /**
     * @covers ::move
     */
    public function testMove()
    {
        Dir::make($this->tmp);

        $this->assertTrue(Dir::move($this->tmp, $this->moved));
    }

    /**
     * @covers ::move
     */
    public function testMoveNonExisting()
    {
        $this->assertFalse(Dir::move('/does-not-exist', $this->moved));
    }

    /**
     * @covers ::link
     */
    public function testLink()
    {
        $source = $this->tmp . '/source';
        $link   = $this->tmp . '/link';

        Dir::make($source);

        $this->assertTrue(Dir::link($source, $link));
        $this->assertTrue(is_link($link));
    }

    /**
     * @covers ::link
     */
    public function testLinkExistingLink()
    {
        $source = $this->tmp . '/source';
        $link   = $this->tmp . '/link';

        Dir::make($source);
        Dir::link($source, $link);

        $this->assertTrue(Dir::link($source, $link));
    }

    /**
     * @covers ::link
     */
    public function testLinkWithoutSource()
    {
        $source = $this->tmp . '/source';
        $link   = $this->tmp . '/link';

        $this->expectExceptionMessage('Expection');
        $this->expectExceptionMessage('The directory "' . $source . '" does not exist and cannot be linked');

        Dir::link($source, $link);
    }

    /**
     * @covers ::read
     */
    public function testRead()
    {
        Dir::make($this->tmp);

        touch($this->tmp . '/a.jpg');
        touch($this->tmp . '/b.jpg');
        touch($this->tmp . '/c.jpg');

        // relative
        $files    = Dir::read($this->tmp);
        $expected = [
            'a.jpg',
            'b.jpg',
            'c.jpg'
        ];

        $this->assertSame($expected, $files);

        // absolute
        $files    = Dir::read($this->tmp, null, true);
        $expected = [
            $this->tmp . '/a.jpg',
            $this->tmp . '/b.jpg',
            $this->tmp . '/c.jpg'
        ];

        $this->assertSame($expected, $files);

        // ignore
        $files    = Dir::read($this->tmp, ['a.jpg']);
        $expected = [
            'b.jpg',
            'c.jpg'
        ];

        $this->assertSame($expected, $files);
    }

    /**
     * @covers ::remove
     */
    public function testRemove()
    {
        Dir::make($this->tmp);

        $this->assertTrue(is_dir($this->tmp));
        $this->assertTrue(Dir::remove($this->tmp));
        $this->assertFalse(is_dir($this->tmp));
    }

    /**
     * @covers ::isReadable
     */
    public function testIsReadable()
    {
        Dir::make($this->tmp);

        $this->assertSame(is_readable($this->tmp), Dir::isReadable($this->tmp));
    }

    /**
     * @covers ::dirs
     * @covers ::files
     */
    public function testReadDirsAndFiles()
    {
        Dir::make($root = $this->fixtures . '/dirs');
        Dir::make($root . '/a');
        Dir::make($root . '/b');
        Dir::make($root . '/c');

        touch($root . '/a.txt');
        touch($root . '/b.jpg');
        touch($root . '/c.doc');

        $any = Dir::read($root);
        $expected = ['a', 'a.txt', 'b', 'b.jpg', 'c', 'c.doc'];

        $this->assertSame($any, $expected);

        // relative dirs
        $dirs = Dir::dirs($root);
        $expected = ['a', 'b', 'c'];

        $this->assertSame($expected, $dirs);

        // absolute dirs
        $dirs = Dir::dirs($root, null, true);
        $expected = [
            $root . '/a',
            $root . '/b',
            $root . '/c'
        ];

        $this->assertSame($expected, $dirs);

        // relative files
        $files = Dir::files($root);
        $expected = ['a.txt', 'b.jpg', 'c.doc'];

        $this->assertSame($expected, $files);

        // absolute files
        $files = Dir::files($root, null, true);
        $expected = [
            $root . '/a.txt',
            $root . '/b.jpg',
            $root . '/c.doc'
        ];

        $this->assertSame($expected, $files);

        Dir::remove($root);
    }

    /**
     * @covers ::size
     * @covers ::niceSize
     */
    public function testSize()
    {
        Dir::make($this->tmp);

        F::write($this->tmp . '/testfile-1.txt', Str::random(5));
        F::write($this->tmp . '/testfile-2.txt', Str::random(5));
        F::write($this->tmp . '/testfile-3.txt', Str::random(5));

        $this->assertSame(15, Dir::size($this->tmp));
        $this->assertSame(15, Dir::size($this->tmp, false));
        $this->assertSame('15 B', Dir::niceSize($this->tmp));

        Dir::remove($this->tmp);
    }

    /**
     * @covers ::size
     */
    public function testSizeWithNestedFolders()
    {
        Dir::make($this->tmp);
        Dir::make($this->tmp . '/sub');
        Dir::make($this->tmp . '/sub/sub');

        F::write($this->tmp . '/testfile-1.txt', Str::random(5));
        F::write($this->tmp . '/sub/testfile-2.txt', Str::random(5));
        F::write($this->tmp . '/sub/sub/testfile-3.txt', Str::random(5));

        $this->assertSame(15, Dir::size($this->tmp));
        $this->assertSame(5, Dir::size($this->tmp, false));
        $this->assertSame('15 B', Dir::niceSize($this->tmp));

        Dir::remove($this->tmp);
    }

    /**
     * @covers ::size
     */
    public function testSizeOfNonExistingDir()
    {
        $this->assertFalse(Dir::size('/does-not-exist'));
    }

    /**
     * @covers ::wasModifiedAfter
     */
    public function testWasModifiedAfter()
    {
        $time = time();

        Dir::make($this->tmp);
        Dir::make($this->tmp . '/sub');
        F::write($this->tmp . '/sub/test.txt', 'foo');

        // the modification time of the folder is already later
        // than the given time
        $this->assertTrue(Dir::wasModifiedAfter($this->tmp, $time - 10));

        // ensure that the modified times are consistent
        // to make the test more reliable
        touch($this->tmp, $time);
        touch($this->tmp . '/sub', $time);
        touch($this->tmp . '/sub/test.txt', $time);

        $this->assertFalse(Dir::wasModifiedAfter($this->tmp, $time));

        touch($this->tmp . '/sub/test.txt', $time + 1);

        $this->assertTrue(Dir::wasModifiedAfter($this->tmp, $time));

        touch($this->tmp . '/sub', $time + 1);
        touch($this->tmp . '/sub/test.txt', $time);

        $this->assertTrue(Dir::wasModifiedAfter($this->tmp, $time));

        // sanity check
        touch($this->tmp . '/sub', $time);

        $this->assertFalse(Dir::wasModifiedAfter($this->tmp, $time));
    }
}
