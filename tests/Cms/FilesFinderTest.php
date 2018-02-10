<?php

namespace Kirby\Cms;

class FilesFinderTest extends TestCase
{

    public function fileProvider()
    {
        return [
            ['some-file.jpg', null],
            ['projects/some-file.jpg', 'projects'],
            ['projects/project-a/some-file.jpg', 'projects/project-a'],
            ['projects/project-a/some-child/some-file.jpg', 'projects/project-a/some-child'],
        ];
    }

    /**
     * @dataProvider fileProvider
     */
    public function testFindById($filename, $startAt)
    {
        $collection = new Files([
            $file = new File([
                'filename' => $filename,
                'url'      => $filename,
            ])
        ]);

        $finder = new FilesFinder($collection, $startAt);

        $this->assertIsFile($finder->findById('some-file.jpg'), $file);
        $this->assertIsFile($finder->find('some-file.jpg'), $file);

        if (empty($startAt) === false) {
            $this->assertNull($finder->findById($filename));
            $this->assertNull($finder->find($filename));
        }
    }

}
