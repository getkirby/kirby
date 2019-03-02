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

    public function testBooleanInvalid()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Please confirm or deny');

        $field = new Field('test');
        Validations::boolean($field, 'nope');
    }

    public function testDateValid()
    {
        $field = new Field('test');
        $this->assertTrue(Validations::date($field, '2012-12-12'));
    }

    public function testDateInvalid()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Please enter a valid date');

        $field = new Field('test');
        Validations::date($field, 'somewhen');
    }

    public function testEmailValid()
    {
        $field = new Field('test');
        $this->assertTrue(Validations::email($field, 'test@getkirby.com'));
    }

    public function testEmailInvalid()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Please enter a valid email address');

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

    public function testMaxInvalid()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Please enter a value equal to or lower than 5');

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

    public function testMaxLengthInvalid()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Please enter a shorter value. (max. 5 characters)');

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

    public function testMinInvalid()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Please enter a value equal to or greater than 5');

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

    public function testMinLengthInvalid()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Please enter a longer value. (min. 5 characters)');

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

    public function testRequiredInvalid()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Please enter something');

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

    public function testOptionInvalid()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Please select a valid option');

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

    public function testOptionsInvalid()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Please select a valid option');

        $field = new Field('test', [
            'options' => [
                ['value' => 'a'],
                ['value' => 'b']
            ]
        ]);

        Validations::options($field, ['a', 'c']);
    }
}
