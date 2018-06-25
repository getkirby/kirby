<?php

namespace Kirby\Toolkit;

class DirTest extends TestCase
{

    const FIXTURES = __DIR__ . '/fixtures/dir';


    public function testCopy()
    {
        $src    = static::FIXTURES . '/copy';
        $target = static::FIXTURES . '/copy-target';

        $result = Dir::copy($src, $target);

        $this->assertTrue($result);

        $this->assertTrue(file_exists($target . '/a.txt'));
        $this->assertTrue(file_exists($target . '/subfolder/b.txt'));

        // clean up
        Dir::remove($target);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The target directory
     */
    public function testCopyExists()
    {
        $src    = static::FIXTURES . '/copy';
        $target = static::FIXTURES . '/copy';

        Dir::copy($src, $target);
    }


}
