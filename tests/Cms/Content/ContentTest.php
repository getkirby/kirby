<?php

namespace Kirby\Cms;

class ContentTest extends TestCase
{
    protected function mockData(): array
    {
        return [
            'title' => 'Test Content',
            'text'  => 'Lorem ipsum'
        ];
    }

    protected function mockObject()
    {
        return new Content($this->mockData());
    }

    public function testData()
    {
        $data   = $this->mockData();
        $object = $this->mockObject();

        $this->assertEquals($data, $object->data());
    }

    public function testKeys()
    {
        $content = $this->mockObject();

        $this->assertEquals(['title', 'text'], $content->keys());
    }

    public function testHas()
    {
        $content = new Content([
            'a' => 'A'
        ]);

        $this->assertTrue($content->has('a'));
        $this->assertFalse($content->has('b'));
    }

    public function testHasWithDifferentCase()
    {
        $content = new Content([
            'testA' => 'A',
            'TESTb' => 'B'
        ]);

        $this->assertTrue($content->has('testA'));
        $this->assertTrue($content->has('testa'));
        $this->assertTrue($content->has('TESTb'));
        $this->assertTrue($content->has('testb'));
    }

    public function testGetExistingField()
    {
        $content = $this->mockObject();
        $field   = $content->get('title');

        $this->assertInstanceOf(Field::class, $field);
        $this->assertEquals('Test Content', $field->value());
    }

    public function testGetWithDifferentCase()
    {
        $content = new Content([
            'testA' => 'A',
            'TESTb' => 'B'
        ]);

        $this->assertEquals('A', $content->get('testA')->value());
        $this->assertEquals('A', $content->get('testa')->value());
        $this->assertEquals('B', $content->get('TESTb')->value());
        $this->assertEquals('B', $content->get('testb')->value());
    }

    public function testGetNonExistingField()
    {
        $content = $this->mockObject();
        $field   = $content->get('nonExistingField');

        $this->assertInstanceOf(Field::class, $field);
        $this->assertEquals(null, $field->value());
    }

    public function testGetAllFields()
    {
        $content = $this->mockObject();
        $fields  = $content->get();

        foreach ($this->mockData() as $key => $value) {
            $this->assertInstanceOf(Field::class, $fields[$key]);
            $this->assertEquals($key, $fields[$key]->key());
            $this->assertEquals($value, $fields[$key]->value());
        }
    }

    public function testNot()
    {
        $content = $this->mockObject();
        $content = $content->not('title');

        $this->assertEquals(null, $content->get('title')->value());
        $this->assertEquals('Lorem ipsum', $content->get('text')->value());
    }

    public function testMultipleNot()
    {
        $content = $this->mockObject();
        $content = $content->not('title')->not('text');

        $this->assertEquals(null, $content->get('title')->value());
        $this->assertEquals(null, $content->get('text')->value());
    }

    public function testParent()
    {
        $page    = new Page(['slug' => 'test']);
        $content = new Content(['title' => 'Test'], $page);

        $this->assertEquals($page, $content->parent());
    }

    public function testSetParent()
    {
        $page    = new Page(['slug' => 'test']);
        $content = new Content(['title' => 'Test']);
        $content->setParent($page);

        $this->assertEquals($page, $content->parent());
    }

    public function testToArray()
    {
        $this->assertEquals($this->mockData(), $this->mockObject()->toArray());
    }

    public function testUpdate()
    {
        $content = $this->mockObject();

        $content = $content->update([
            'category' => 'test'
        ]);
        $this->assertEquals('test', $content->get('category')->value());

        // Field objects should be cleared on update
        $content = $content->update([
            'category' => 'another-test'
        ]);
        $this->assertEquals('another-test', $content->get('category')->value());
    }

    public function testDebuginfo()
    {
        $this->assertEquals($this->mockData(), $this->mockObject()->__debugInfo());
    }
}
