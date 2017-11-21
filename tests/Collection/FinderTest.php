<?php

namespace Kirby\Collection;

use PHPUnit\Framework\TestCase;

class FinderTest extends TestCase
{

    public function testCollection()
    {
        $collection = new Collection([
            'one' => 'eins',
            'two' => 'zwei',
        ]);
        $finder = new Finder($collection);

        $this->assertEquals($collection, $finder->collection());
    }

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
        $finder = new Finder($collection);

        $this->assertEquals([
            'name' => 'Bastian',
            'email' => 'bastian@getkirby.com'
        ], $finder->findBy('email', 'bastian@getkirby.com'));
        $this->assertEquals(null, $finder->findBy('email', 'sonja@getkirby.com'));
    }

    public function testFindKey()
    {
        $collection = new Collection([
            'one' => 'eins',
            'two' => 'zwei',
        ]);
        $finder = new Finder($collection);

        $this->assertEquals('zwei', $finder->find('two'));
    }

    public function testFindKeys()
    {
        $collection = new Collection([
            'one'   => 'eins',
            'two'   => 'zwei',
            'three' => 'drei'
        ]);
        $result = new Collection([
            'one' => 'eins',
            'two' => 'zwei',
        ]);
        $finder = new Finder($collection);

        $this->assertEquals($result, $finder->find('one', 'two'));
    }

    public function testFindByKey()
    {
        $collection = new Collection([
            'one' => 'eins',
            'two' => 'zwei',
        ]);
        $finder = new Finder($collection);

        $this->assertEquals('zwei', $finder->findByKey('two'));
    }

}
