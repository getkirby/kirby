<?php

namespace Kirby\Toolkit;

class CollectionFinderTest extends TestCase
{
    public function testFindBy()
    {
        $collection = new Collection([
            [
                'name' => 'Bastian',
                'email' => 'bastian@getkirby.com'
            ],
            [
                'name' => 'Nico',
                'email' => 'nico@getkirby.com'
            ]
        ]);

        $this->assertEquals([
            'name' => 'Bastian',
            'email' => 'bastian@getkirby.com'
        ], $collection->findBy('email', 'bastian@getkirby.com'));
    }

    public function testFindKey()
    {
        $collection = new Collection([
            'one' => 'eins',
            'two' => 'zwei'
        ]);

        $this->assertEquals('zwei', $collection->find('two'));
    }
}
