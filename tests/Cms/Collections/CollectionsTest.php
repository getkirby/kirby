<?php

namespace Kirby\Cms;

class CollectionsTest extends TestCase
{
    protected function _app()
    {
        return new App([
            'roots' => [
                'collections' => __DIR__ . '/fixtures/collections'
            ]
        ]);
    }

    public function testGetAndCall()
    {
        $app        = $this->_app();
        $collection = new Collection();

        // get
        $result = $app->collections()->get('test');
        $this->assertEquals($collection, $result);

        // __call
        $result = $app->collections()->test();
        $this->assertEquals($collection, $result);
    }

    public function testGetWithData()
    {
        $app    = $this->_app();
        $result = $app->collections()->get('string', [
            'a' => 'a',
            'b' => 'b'
        ]);

        $this->assertEquals('ab', $result);
    }

    public function testGetWithRearrangedData()
    {
        $app    = $this->_app();
        $result = $app->collections()->get('rearranged', [
            'a' => 'a',
            'b' => 'b'
        ]);

        $this->assertEquals('ab', $result);
    }

    public function testGetWithDifferentData()
    {
        $app = $this->_app();

        $result = $app->collections()->get('string', [
            'a' => 'a',
            'b' => 'b'
        ]);
        $this->assertEquals('ab', $result);

        $result = $app->collections()->get('string', [
            'a' => 'c',
            'b' => 'd'
        ]);
        $this->assertEquals('cd', $result);
    }

    public function testGetCloned()
    {
        $app         = $this->_app();
        $collections = $app->collections();

        $a = $collections->get('test');
        $this->assertEquals(0, $a->count());

        $a->add('kirby');
        $this->assertEquals(1, $a->count());

        $b = $collections->get('test');
        $this->assertEquals(0, $b->count());
    }

    public function testHas()
    {
        $app= $this->_app();
        $this->assertTrue($app->collections()->has('test'));
        $this->assertFalse($app->collections()->has('does-not-exist'));
        $this->assertTrue($app->collections()->has('test'));
    }

    public function testLoad()
    {
        $app = $this->_app();
        $result = $app->collections()->load('test');
        $this->assertInstanceOf(Collection::class, $result());

        $result = $app->collections()->load('nested/test');
        $this->assertEquals('a', $result());
    }

    public function testLoadNested()
    {
        $app = $this->_app();
        $result = $app->collections()->load('nested/test');
        $this->assertEquals('a', $result());
    }
}
