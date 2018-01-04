<?php

namespace Kirby\Util;

use PHPUnit\Framework\TestCase;

class PropsTest extends TestCase
{

    public function getValueProvider()
    {
        return [
            ['simple', 'simple value'],
            ['simpleDefault', 'simple default value']
        ];
    }

    /**
     * @dataProvider getValueProvider
     */
    public function testGet($prop, $expected)
    {
        $schema = [
            'simple' => [
                'type' => 'string'
            ],
            'simpleDefault' => [
                'type'    => 'string',
                'default' => 'simple default value'
            ],
            'lazyDefault' => [
                'type'    => 'string',
                'default' => function () {
                    return 'lazy default value';
                }
            ],
            'boundDefault' => [
                'type'    => 'string',
                'default' => function () {
                    return 'bound ' . $this->simple;
                }
            ]
        ];

        $props = new Props($schema, [
            'simple' => 'simple value',
        ]);

        $this->assertEquals($expected, $props->get($prop));
        $this->assertEquals($expected, $props->$prop);
    }

    public function testGetNonExisting()
    {
        $props = new Props([
            'a' => ['type' => 'string']
        ]);

        $this->assertNull($props->get('b'));
        $this->assertNull($props->b);
    }

    public function testIsset()
    {
        $props = new Props([
            'a' => ['type' => 'string']
        ]);

        $this->assertTrue(isset($props->a));
    }

    public function testNotIsset()
    {
        $props = new Props([
            'a' => ['type' => 'string']
        ]);

        $this->assertFalse(isset($props->b));
    }

    public function testSet()
    {
        $props = new Props([
            'a' => ['type' => 'string']
        ]);

        $this->assertInstanceOf(Props::class, $props->set('a', 'a'));
        $this->assertEquals('a', $props->a);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage "a" must be of type "string" not "boolean"
     */
    public function testSetInvalid()
    {
        $props = new Props([
            'a' => ['type' => 'string']
        ]);

        $props->set('a', true);
    }

    public function testDefault()
    {
        $props = new Props([
            'a' => [
                'type'    => 'string',
                'default' => 'a'
            ]
        ]);

        $this->assertEquals('a', $props->a);
        $this->assertEquals('a', $props->get('a'));
    }

    public function testLazyDefault()
    {
        $props = new Props([
            'a' => [
                'type'    => 'string',
                'default' => function () {
                    return 'a';
                }
            ]
        ]);

        $this->assertEquals('a', $props->a);
        $this->assertEquals('a', $props->get('a'));
    }

    public function testUnboundLazyDefault()
    {
        $props = new Props([
            'a' => [
                'type'    => 'string',
                'default' => function () {
                    return get_class($this);
                }
            ]
        ]);

        $this->assertEquals(Props::class, $props->a);
        $this->assertEquals(Props::class, $props->get('a'));
    }

    public function testBoundLazyDefault()
    {
        $props = new Props([
            'a' => [
                'type'    => 'string',
                'default' => function () {
                    return get_class($this);
                }
            ]
        ], [], $this);

        $this->assertEquals(PropsTest::class, $props->a);
        $this->assertEquals(PropsTest::class, $props->get('a'));
    }

    public function testDefaults()
    {
        $props = new Props([
            'a' => [
                'type'    => 'string',
                'default' => 'a'
            ],
            'b' => [
                'type'    => 'string',
                'default' => 'b'
            ]
        ]);

        $expected = ['a' => 'a', 'b' => 'b'];

        $this->assertEquals($expected, $props->defaults());
    }

    public function testHas()
    {
        $props = new Props([
            'a' => [
                'type' => 'string',
            ]
        ]);

        $this->assertTrue($props->has('a'));
    }

    public function testHasNot()
    {
        $props = new Props([
            'a' => [
                'type' => 'string',
            ]
        ]);

        $this->assertFalse($props->has('b'));
    }

    public function testSchema()
    {
        $props = new Props([
            'a' => [
                'type' => 'string'
            ]
        ]);

        $this->assertInstanceOf(Schema::class, $props->schema());
    }

    public function testToArray()
    {
        $props = new Props([
            'a' => [
                'type' => 'string'
            ],
            'b' => [
                'type'    => 'string',
                'default' => 'b'
            ]
        ], ['a' => 'a', 'c' => 'c']);

        $expected = [
            'a' => 'a',
            'b' => 'b',
            'c' => 'c'
        ];

        $this->assertEquals($expected, $props->toArray());
    }

    public function testToArrayStrict()
    {
        $props = new Props([
            'a' => [
                'type' => 'string'
            ],
            'b' => [
                'type'    => 'string',
                'default' => 'b'
            ]
        ], ['a' => 'a', 'c' => 'c']);

        $expected = [
            'a' => 'a',
            'b' => 'b',
        ];

        $this->assertEquals($expected, $props->toArray(true));
    }

    public function testValidate()
    {
        $props = new Props([
            'a' => [
                'type' => 'string'
            ],
            'b' => [
                'type' => 'boolean',
            ]
        ]);

        $this->assertTrue($props->validate('a', 'a'));
        $this->assertTrue($props->validate('b', true));
    }

    public function testValidateArray()
    {
        $props = new Props([
            'a' => [
                'type' => 'string'
            ],
            'b' => [
                'type' => 'boolean',
            ]
        ]);

        $this->assertTrue($props->validate([
            'a' => 'a',
            'b' => true
        ]));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage "a" must be of type "string" not "boolean"
     */
    public function testValidationFails()
    {
        $props = new Props([
            'a' => [
                'type' => 'string'
            ],
        ]);

        $props->validate('a', true);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage "b" must be of type "boolean" not "string"
     */
    public function testArrayValidationFails()
    {
        $props = new Props([
            'a' => [
                'type' => 'string'
            ],
            'b' => [
                'type' => 'boolean',
            ]
        ]);

        $props->validate([
            'a' => 'a',
            'b' => 'true'
        ]);
    }

}
