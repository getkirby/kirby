<?php

namespace Kirby\Cms;

/**
 * @coversDefaultClass Kirby\Cms\Content
 */
class ContentTest extends TestCase
{
    protected $content;
    protected $parent;

    public function setUp(): void
    {
        $this->parent  = new Page(['slug' => 'test']);
        $this->content = new Content([
            'a' => 'A',
            'B' => 'B',
            'MiXeD' => 'mixed',
            'mIXeD' => 'MIXED'
        ], $this->parent);
    }

    /**
     * @covers ::__call
     */
    public function testCall()
    {
        $this->assertSame('a', $this->content->a()->key());
        $this->assertSame('A', $this->content->a()->value());
        $this->assertSame('mixed', $this->content->mixed()->key());
        $this->assertSame('MIXED', $this->content->mixed()->value());
        $this->assertSame('mixed', $this->content->mIXEd()->key());
        $this->assertSame('MIXED', $this->content->mIXEd()->value());
    }

    /**
     * @covers ::__construct
     * @covers ::__debugInfo
     * @covers ::data
     * @covers ::toArray
     */
    public function testData()
    {
        $expected = [
            'a' => 'A',
            'b' => 'B',
            'mixed' => 'MIXED'
        ];

        $this->assertSame($expected, $this->content->__debugInfo());
        $this->assertSame($expected, $this->content->data());
        $this->assertSame($expected, $this->content->toArray());
    }

    /**
     * @covers ::fields
     */
    public function testFields()
    {
        $fields = $this->content->fields();

        $this->assertCount(3, $fields);
        $this->assertInstanceOf(Field::class, $fields['mixed']);
        $this->assertSame('mixed', $fields['mixed']->key());
        $this->assertSame('MIXED', $fields['mixed']->value());
    }

    /**
     * @covers ::get
     */
    public function testGet()
    {
        $field = $this->content->get('mixed');
        $this->assertInstanceOf(Field::class, $field);
        $this->assertSame('mixed', $field->key());
        $this->assertSame($this->parent, $field->parent());
        $this->assertSame('MIXED', $field->value());

        // different case
        $this->assertSame($field, $this->content->get('MiXeD'));

        // non-existing field
        $field = $this->content->get('invalid');
        $this->assertInstanceOf(Field::class, $field);
        $this->assertSame('invalid', $field->key());
        $this->assertSame($this->parent, $field->parent());
        $this->assertSame(null, $field->value());

        // all fields
        $fields = $this->content->get();
        $this->assertSame(['mixed', 'invalid', 'a', 'b'], array_keys($fields));
        $this->assertInstanceOf(Field::class, $fields['mixed']);
        $this->assertSame('mixed', $fields['mixed']->key());
        $this->assertSame('MIXED', $fields['mixed']->value());
    }

    /**
     * @covers ::has
     */
    public function testHas()
    {
        $this->assertTrue($this->content->has('a'));
        $this->assertTrue($this->content->has('A'));
        $this->assertTrue($this->content->has('b'));
        $this->assertTrue($this->content->has('B'));
        $this->assertTrue($this->content->has('mixed'));
        $this->assertTrue($this->content->has('MIXED'));
        $this->assertFalse($this->content->has('c'));
        $this->assertFalse($this->content->has('C'));
    }

    /**
     * @covers ::keys
     */
    public function testKeys()
    {
        $this->assertSame(['a', 'b', 'mixed'], $this->content->keys());
    }
    
    /**
     * @covers ::not
     */
    public function testNot()
    {
        $content1 = $this->content->not('a');
        $this->assertNotSame($this->content, $content1);
        $this->assertSame(null, $content1->get('a')->value());
        $this->assertSame('B', $content1->get('b')->value());

        $content2 = $this->content->not('A');
        $this->assertNotSame($this->content, $content2);
        $this->assertSame(null, $content2->get('a')->value());
        $this->assertSame('B', $content2->get('b')->value());

        $content3 = $this->content->not('MIxeD');
        $this->assertNotSame($this->content, $content3);
        $this->assertSame(null, $content3->get('mixed')->value());
        $this->assertSame('B', $content3->get('b')->value());

        // multiple nots
        $content4 = $this->content->not('a')->not('MIxed');
        $this->assertNotSame($this->content, $content4);
        $this->assertSame(null, $content4->get('a')->value());
        $this->assertSame(null, $content4->get('mixed')->value());
        $this->assertSame('B', $content4->get('b')->value());

        // multiple nots in one go
        $content5 = $this->content->not('a', 'MIxed');
        $this->assertNotSame($this->content, $content5);
        $this->assertSame(null, $content5->get('a')->value());
        $this->assertSame(null, $content5->get('mixed')->value());
        $this->assertSame('B', $content5->get('b')->value());
    }

    /**
     * @covers ::parent
     */
    public function testParent()
    {
        $this->assertSame($this->parent, $this->content->parent());
    }

    /**
     * @covers ::setParent
     */
    public function testSetParent()
    {
        $page = new Page(['slug' => 'another-test']);
        $this->content->setParent($page);

        $this->assertSame($page, $this->content->parent());
    }

    /**
     * @covers ::update
     */
    public function testUpdate()
    {
        $this->content->update([
            'a' => 'aaa'
        ]);
        $this->assertSame('aaa', $this->content->get('a')->value());

        $this->content->update([
            'miXED' => 'mixed!'
        ]);
        $this->assertSame('mixed!', $this->content->get('mixed')->value());

        // Field objects should be cleared on update
        $this->content->update([
            'a' => 'aaaaaa'
        ]);
        $this->assertSame('aaaaaa', $this->content->get('a')->value());

        $this->content->update($expected = [
            'TEST' => 'TEST'
        ], true);
        $this->assertSame(['test' => 'TEST'], $this->content->data());

        $this->content->update(null, true);
        $this->assertSame([], $this->content->data());
    }
}
