<?php

namespace Kirby\Form;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use PHPUnit\Framework\TestCase;

class ValidationsTest extends TestCase
{
    public function setUp(): void
    {
        new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);

        Field::$types = [
            'test' => [
                'props' => [
                    'options' => function (array $options = []) {
                        return $options;
                    }
                ]
            ]
        ];

        Field::$mixins = [];
    }

    public function tearDown(): void
    {
        Field::$types  = [];
        Field::$mixins = [];
    }

    public function testBooleanValid()
    {
        $field = new Field('test');
        $this->assertTrue(Validations::boolean($field, true));
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     * @expectedExceptionMessage Please confirm or deny
     */
    public function testBooleanInvalid()
    {
        $field = new Field('test');
        Validations::boolean($field, 'nope');
    }

    public function testDateValid()
    {
        $field = new Field('test');
        $this->assertTrue(Validations::date($field, '2012-12-12'));
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     * @expectedExceptionMessage Please enter a valid date
     */
    public function testDateInvalid()
    {
        $field = new Field('test');
        Validations::date($field, 'somewhen');
    }

    public function testEmailValid()
    {
        $field = new Field('test');
        $this->assertTrue(Validations::email($field, 'test@getkirby.com'));
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     * @expectedExceptionMessage Please enter a valid email address
     */
    public function testEmailInvalid()
    {
        $field = new Field('test');
        Validations::email($field, 'test[at]getkirby.com');
    }

    public function testMaxValid()
    {
        $field = new Field('test', [
            'max' => 5
        ]);

        $this->assertTrue(Validations::max($field, 4));
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     * @expectedExceptionMessage Please enter a value equal to or lower than 5
     */
    public function testMaxInvalid()
    {
        $field = new Field('test', [
            'max' => 5
        ]);

        Validations::max($field, 6);
    }

    public function testMaxLengthValid()
    {
        $field = new Field('test', [
            'maxlength' => 5
        ]);

        $this->assertTrue(Validations::maxlength($field, 'test'));
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     * @expectedExceptionMessage Please enter a shorter value. (max. 5 characters)
     */
    public function testMaxLengthInvalid()
    {
        $field = new Field('test', [
            'maxlength' => 5
        ]);

        Validations::maxlength($field, 'testest');
    }

    public function testMinValid()
    {
        $field = new Field('test', [
            'min' => 5
        ]);

        $this->assertTrue(Validations::min($field, 6));
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     * @expectedExceptionMessage Please enter a value equal to or greater than 5
     */
    public function testMinInvalid()
    {
        $field = new Field('test', [
            'min' => 5
        ]);

        Validations::min($field, 4);
    }

    public function testMinLengthValid()
    {
        $field = new Field('test', [
            'minlength' => 5
        ]);

        $this->assertTrue(Validations::minlength($field, 'testest'));
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     * @expectedExceptionMessage Please enter a longer value. (min. 5 characters)
     */
    public function testMinLengthInvalid()
    {
        $field = new Field('test', [
            'minlength' => 5
        ]);

        Validations::minlength($field, 'test');
    }

    public function testRequiredValid()
    {
        $field = new Field('test', [
            'required' => true
        ]);

        $this->assertTrue(Validations::required($field, 'something'));
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     * @expectedExceptionMessage Please enter something
     */
    public function testRequiredInvalid()
    {
        $field = new Field('test', [
            'required' => true
        ]);

        Validations::required($field, '');
    }

    public function testOptionValid()
    {
        $field = new Field('test', [
            'options' => [
                ['value' => 'a'],
                ['value' => 'b']
            ]
        ]);

        $this->assertTrue(Validations::option($field, 'a'));
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     * @expectedExceptionMessage Please select a valid option
     */
    public function testOptionInvalid()
    {
        $field = new Field('test', [
            'options' => [
                ['value' => 'a'],
                ['value' => 'b']
            ]
        ]);

        Validations::option($field, 'c');
    }

    public function testOptionsValid()
    {
        $field = new Field('test', [
            'options' => [
                ['value' => 'a'],
                ['value' => 'b']
            ]
        ]);

        $this->assertTrue(Validations::options($field, ['a', 'b']));
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     * @expectedExceptionMessage Please select a valid option
     */
    public function testOptionsInvalid()
    {
        $field = new Field('test', [
            'options' => [
                ['value' => 'a'],
                ['value' => 'b']
            ]
        ]);

        Validations::options($field, ['a', 'c']);
    }
}
