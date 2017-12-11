<?php

namespace Kirby\Cms;

class ChildrenFinderTest extends TestCase
{

    public function pageProvider()
    {
        return [
            ['projects', 'projects', null],
            ['projects/project-a', 'project-a', 'projects'],
            ['projects/project-a/some-child', 'some-child', 'projects/project-a'],
        ];
    }

    /**
     * @dataProvider pageProvider
     */
    public function testFindById($id, $findBy, $startAt)
    {
        $collection = new Pages([
            $page = new Page([
                'id' => $id
            ])
        ]);

        $finder = new ChildrenFinder($collection, $startAt);

        $this->assertIsPage($finder->findById($findBy), $page);
        $this->assertIsPage($finder->find($findBy), $page);

        if (empty($startAt) === false) {
            $this->assertNull($finder->findById($id));
            $this->assertNull($finder->find($id));
        }
    }

}
