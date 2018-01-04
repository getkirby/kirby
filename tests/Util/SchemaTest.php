<?php

namespace Kirby\Util;

use PHPUnit\Framework\TestCase;

class SchemaTest extends TestCase
{

    public function schemaData(): array
    {
        return [
            'array' => [
                'type' => 'array'
            ],
            'boolean' => [
                'type' => 'boolean'
            ],
            'class' => [
                'type' => Schema::class
            ],
            'double' => [
                'type' => 'double'
            ],
            'integer' => [
                'type' => 'integer'
            ],
            'number' => [
                'type' => 'number'
            ],
            'scalar' => [
                'type' => 'scalar'
            ],
            'string' => [
                'type' => 'string',
            ],
        ];
    }

    public function schema()
    {
        return new Schema($this->schemaData());
    }

    public function validDataProvider()
    {
        return [
            ['array', []],
            ['boolean', true],
            ['boolean', false],
            ['class', new Schema([])],
            ['double', 1.1],
            ['integer', 1],
            ['number', 0],
            ['number', 1],
            ['number', 1.1],
            ['scalar', 1],
            ['scalar', 'string'],
            ['scalar', true],
            ['string', 'string'],
        ];
    }

    public function invalidDataProvider()
    {
        return [
            ['array', 'array'],
            ['boolean', 'true'],
            ['class', false],
            ['double', 1],
            ['integer', 1.1],
            ['number', 'a'],
            ['number', '1'],
            ['number', '1.1'],
            ['scalar', new Schema([])],
            ['scalar', []],
            ['string', 1],
        ];
    }

    public function testHas()
    {
        $schema = new Schema([
            'a' => [
                'type' => 'string'
            ],
            'b' => [
                'type' => 'string'
            ]
        ]);

        $this->assertTrue($schema->has('a'));
        $this->assertTrue($schema->has('b'));
        $this->assertFalse($schema->has('c'));
    }

    public function testGet()
    {
        $schema = new Schema([
            'a' => $a = [
                'type' => 'string'
            ]
        ]);

        $this->assertEquals($a, $schema->get('a'));
        $this->assertNull($schema->get('b'));
    }

    public function testKeys()
    {
        $schema = new Schema([
            'a' => [
                'type' => 'string'
            ],
            'b' => [
                'type' => 'string'
            ]
        ]);

        $this->assertEquals(['a', 'b'], $schema->keys());
    }

    /**
     * @dataProvider validDataProvider
     */
    public function testAccepts($key, $value)
    {
        $this->assertTrue($this->schema()->accepts($key, $value));
    }

    /**
     * @dataProvider invalidDataProvider
     */
    public function testDoesNotAccept($key, $value)
    {
        $this->assertFalse($this->schema()->accepts($key, $value));
    }

    /**
     * @dataProvider validDataProvider
     */
    public function testValidates($key, $value)
    {
        $this->assertTrue($this->schema()->validate($key, $value));
    }

    /**
     * @dataProvider invalidDataProvider
     * @expectedException Exception
     */
    public function testDoesNotValidate($key, $value)
    {
        $this->schema()->validate($key, $value);
    }

    public function testValidatesMultiple()
    {
        $schema = new Schema([
            'a' => ['type' => 'string'],
            'b' => ['type' => 'boolean']
        ]);

        $input = [
            'a' => 'a',
            'b' => true
        ];

        $this->assertTrue($schema->validate($input));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage "c" is required and must not be null
     */
    public function testDoesNotValidateMultiple()
    {
        $schema = new Schema([
            'a' => ['type' => 'string'],
            'b' => ['type' => 'boolean'],
            'c' => ['type' => 'string', 'required' => true]
        ]);

        $input = [
            'a' => 'a',
            'b' => true
        ];

        $this->assertTrue($schema->validate($input));
    }

    public function testPluck()
    {
        $schema = new Schema([
            'a' => ['type' => 'string'],
            'b' => ['type' => 'string'],
            'c' => ['type' => 'string']
        ]);

        $input = [
            'a' => 'a',
            'b' => 'b',
        ];

        $expected = [
            'a' => 'a',
            'b' => 'b',
            'c' => null,
        ];

        $this->assertEquals($expected, $schema->pluck($input));
    }

    public function testToArray()
    {
        $this->assertEquals($this->schemaData(), $this->schema()->toArray());
    }

}
