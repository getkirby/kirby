<?php

namespace Kirby\Content;

use PHPUnit\Framework\TestCase;

class FieldTest extends TestCase
{

    protected function _field()
    {
        return new Field('test', 'test content');
    }

    public function testKey()
    {
        $this->assertEquals('test', $this->_field()->key());
    }

    public function testValue()
    {
        // getter
        $this->assertEquals('test content', $this->_field()->value());

        $field  = $this->_field();
        $result = $field->value('modified content');

        $this->assertInstanceOf(Field::class, $result);
        $this->assertEquals('modified content', $field->value());
    }

    public function testWithIntValue()
    {
        $field = new Field('test', 1);
        $this->assertEquals('1', $field->value());
    }

    public function testWithFloatValue()
    {
        $field = new Field('test', 1.1);
        $this->assertEquals('1.1', $field->value());
    }

    public function testWithBoolValue()
    {
        $field = new Field('test', false);
        $this->assertEquals('', $field->value());
    }

    public function testWithTrueValue()
    {
        $field = new Field('test', true);
        $this->assertEquals('1', $field->value());
    }

    /**
     * Test with invalid field type
     *
     * @expectedException TypeError
     */
    public function testNonStringValue()
    {
        new Field('test', []);
    }

    public function testValueCallback()
    {
        $field = new Field('test', 'a');
        $field->value(function ($value) {
            return 'b';
        });

        $this->assertEquals('b', $field->value());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid field value type: array
     */
    public function testInvalidValue()
    {
        $field = new Field('test', 'a');
        $field->value([]);
    }

    public function testToString()
    {
        $field = new Field('test', 'test content');

        $this->assertEquals('test content', $field->__toString());
        $this->assertEquals($field->value(), $field->__toString());
        $this->assertEquals('test content', (string)$field);
    }

    public function testMethodAndCall()
    {
        $field = new Field('test', 'lorem');
        $field->method('upper', function () {
            return $this->value(function ($value) {
                return strtoupper($value);
            });
        });

        $this->assertInstanceOf(Field::class, $field->call('upper'));
        $this->assertInstanceOf(Field::class, $field->upper());
        $this->assertEquals('LOREM', $field->call('upper')->value());
        $this->assertEquals('LOREM', $field->upper()->value());
    }

    public function testMultiMethods()
    {
        $field = new Field('test', 'lorem');
        $field->method([
            'a' => function () {
                return 'a';
            },
            'b' => function () {
                return 'b';
            }
        ]);

        $this->assertEquals('a', $field->a());
        $this->assertEquals('b', $field->b());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Please pass a valid field method closure
     */
    public function testInvalidMethod()
    {
        $field = new Field('test', 'lorem');
        $field->method('a', null);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage he field method: doesNotExist is not available
     */
    public function testCallForMissingMethod()
    {
        $field = new Field('test', 'lorem');
        $field->doesNotExist();
    }
}
