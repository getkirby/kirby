<?php

namespace Kirby\Cms;

use Exception;

class ObjectMock extends Object
{

    public function __construct(array $props = [])
    {
        parent::__construct($props, [
            'stringProp' => [
                'type' => 'string'
            ],
            'integerProp' => [
                'type' => 'integer'
            ],
            'doubleProp' => [
                'type' => 'double'
            ],
            'booleanProp' => [
                'type' => 'boolean'
            ],
            'arrayProp' => [
                'type' => 'array'
            ],
            'objectProp' => [
                'type' => ObjectMock::class
            ],
            'numberProp' => [
                'type' => 'number'
            ],
            'propWithSimpleDefaultValue' => [
                'default' => 'hello'
            ],
            'propWithLazyDefaultValue' => [
                'default' => function () {
                    return 'hello';
                }
            ],
            'propWithInvalidDefaultValue' => [
                'type' => 'string',
                'default' => function () {
                    return false;
                }
            ]
        ]);
    }

}

class ObjectToArrayMock extends Object
{
    public function __construct(array $props = [])
    {
        parent::__construct($props, [
            'a' => [
                'type' => 'string',
                'default' => 'defaultA'
            ],
            'b' => [
                'type' => 'string',
                'default' => 'defaultB'
            ]
        ]);
    }
}


class ObjectTest extends TestCase
{

    public function validPropsProvider(): array
    {
        return [
            ['stringProp', 'string'],
            ['integerProp', 1],
            ['doubleProp', 1.2],
            ['booleanProp', true],
            ['arrayProp', []],
            ['objectProp', new ObjectMock([])],
            ['numberProp', '0'],
            ['numberProp', '1'],
            ['numberProp', '1.2'],
            ['numberProp', 0],
            ['numberProp', 1],
            ['numberProp', 1.2],
        ];
    }

    /**
     * @dataProvider validPropsProvider
     */
    public function testValidProps($prop, $value)
    {
        $object = new ObjectMock([$prop => $value]);
        $this->assertEquals($value, $object->$prop());
    }

    public function invalidPropsProvider(): array
    {
        return [
            ['stringProp', false],
            ['integerProp', '1'],
            ['doubleProp', '1.2'],
            ['booleanProp', 'true'],
            ['arrayProp', 'array'],
            ['objectProp', []],
            ['numberProp', 'a'],
        ];
    }

    /**
     * @dataProvider invalidPropsProvider
     */
    public function testInValidProps($prop, $value)
    {
        $this->expectException(Exception::class);
        $object = new ObjectMock([$prop => $value]);
    }

    public function testPropWithSimpleDefaultValue()
    {
        $object = new ObjectMock();
        $this->assertEquals('hello', $object->propWithSimpleDefaultValue());
    }

    public function testPropWithLazyDefaultValue()
    {
        $object = new ObjectMock();
        $this->assertEquals('hello', $object->propWithLazyDefaultValue());
    }

    public function testPropWithInvalidDefaultValue()
    {
        $this->expectException(Exception::class);
        $object = new ObjectMock();
        $object->propWithInvalidDefaultValue();
    }

    public function testSetValidPropValue()
    {
        $object = new ObjectMock();
        $object->set('stringProp', 'hello');
        $this->assertEquals('hello', $object->stringProp());
    }

    public function testSetInvalidPropValue()
    {
        $this->expectException(Exception::class);
        $object = new ObjectMock();
        $object->set('stringProp', false);
    }

    public function testSetMultipleProps()
    {
        $object = new ObjectMock();
        $object->set([
            'stringProp' => 'hello',
            'integerProp' => 2
        ]);

        $this->assertEquals('hello', $object->stringProp());
        $this->assertEquals(2, $object->integerProp());
    }

    public function testIs()
    {
        $a = new ObjectMock(['id' => 'a']);
        $b = new ObjectMock(['id' => 'b']);

        $this->assertTrue($a->is($a));
        $this->assertFalse($a->is($b));
    }

    public function testToArrayWithEmptyObject()
    {
        $object = new Object();
        $this->assertEquals([], $object->toArray());
    }

    public function testToArrayWithDefaults()
    {
        $object = new ObjectToArrayMock();
        $this->assertEquals([
            'a' => 'defaultA',
            'b' => 'defaultB',
        ], $object->toArray());
    }

    public function testToArrayWithValues()
    {
        $data = [
            'a' => 'valueA',
            'b' => 'valueB',
        ];

        $object = new ObjectToArrayMock($data);
        $this->assertEquals($data, $object->toArray());
    }

}
