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
    }

    public function tearDown(): void
    {
        Field::$types = [];
    }

    public function testBooleanValid()
    {
        $page  = new Page(['slug' => 'test']);
        $field = new Field('test', ['model' => $page]);
        $this->assertTrue(Validations::boolean($field, true));
    }

    public function testBooleanInvalid()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Please confirm or deny');

        $page  = new Page(['slug' => 'test']);
        $field = new Field('test', ['model' => $page]);
        Validations::boolean($field, 'nope');
    }

    public function testDateValid()
    {
        $page  = new Page(['slug' => 'test']);
        $field = new Field('test', ['model' => $page]);
        $this->assertTrue(Validations::date($field, '2012-12-12'));
    }

    public function testDateInvalid()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Please enter a valid date');

        $page  = new Page(['slug' => 'test']);
        $field = new Field('test', ['model' => $page]);
        Validations::date($field, 'somewhen');
    }

    public function testEmailValid()
    {
        $page  = new Page(['slug' => 'test']);
        $field = new Field('test', ['model' => $page]);
        $this->assertTrue(Validations::email($field, 'test@getkirby.com'));
    }

    public function testEmailInvalid()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Please enter a valid email address');

        $page  = new Page(['slug' => 'test']);
        $field = new Field('test', ['model' => $page]);
        Validations::email($field, 'test[at]getkirby.com');
    }

    public function testMaxValid()
    {
        $page  = new Page(['slug' => 'test']);
        $field = new Field('test', [
            'model' => $page,
            'max' => 5
        ]);

        $this->assertTrue(Validations::max($field, 4));
    }

    public function testMaxInvalid()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Please enter a value equal to or lower than 5');

        $page  = new Page(['slug' => 'test']);
        $field = new Field('test', [
            'max' => 5,
            'model' => $page
        ]);

        Validations::max($field, 6);
    }

    public function testMaxLengthValid()
    {
        $page  = new Page(['slug' => 'test']);
        $field = new Field('test', [
            'maxlength' => 5,
            'model' => $page
        ]);

        $this->assertTrue(Validations::maxlength($field, 'test'));
    }

    public function testMaxLengthInvalid()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Please enter a shorter value. (max. 5 characters)');

        $page  = new Page(['slug' => 'test']);
        $field = new Field('test', [
            'maxlength' => 5,
            'model' => $page
        ]);

        Validations::maxlength($field, 'testest');
    }

    public function testMinValid()
    {
        $page  = new Page(['slug' => 'test']);
        $field = new Field('test', [
            'min' => 5,
            'model' => $page
        ]);

        $this->assertTrue(Validations::min($field, 6));
    }

    public function testMinInvalid()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Please enter a value equal to or greater than 5');

        $page  = new Page(['slug' => 'test']);
        $field = new Field('test', [
            'min' => 5,
            'model' => $page
        ]);

        Validations::min($field, 4);
    }

    public function testMinLengthValid()
    {
        $page  = new Page(['slug' => 'test']);
        $field = new Field('test', [
            'minlength' => 5,
            'model' => $page
        ]);

        $this->assertTrue(Validations::minlength($field, 'testest'));
    }

    public function testMinLengthInvalid()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Please enter a longer value. (min. 5 characters)');

        $page  = new Page(['slug' => 'test']);
        $field = new Field('test', [
            'minlength' => 5,
            'model' => $page
        ]);

        Validations::minlength($field, 'test');
    }

    public function testPatternValid()
    {
        $page  = new Page(['slug' => 'test']);
        $field = new Field('test', [
            'pattern' => '^#?([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$',
            'model' => $page
        ]);

        $this->assertTrue(Validations::pattern($field, '#fff'));
        $this->assertTrue(Validations::pattern($field, '#222'));
        $this->assertTrue(Validations::pattern($field, '#afafaf'));
        $this->assertTrue(Validations::pattern($field, '#34b3cd'));
    }

    public function testPatternInvalid()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The value does not match the expected pattern');

        $page  = new Page(['slug' => 'test']);
        $field = new Field('test', [
            'pattern' => '^#?([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$',
            'model' => $page
        ]);

        Validations::pattern($field, '#MMM');
    }

    public function testRequiredValid()
    {
        $page  = new Page(['slug' => 'test']);
        $field = new Field('test', [
            'required' => true,
            'model' => $page
        ]);

        $this->assertTrue(Validations::required($field, 'something'));
    }

    public function testRequiredInvalid()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Please enter something');

        $page  = new Page(['slug' => 'test']);
        $field = new Field('test', [
            'required' => true,
            'model' => $page
        ]);

        Validations::required($field, '');
    }

    public function testOptionValid()
    {
        $page  = new Page(['slug' => 'test']);
        $field = new Field('test', [
            'options' => [
                ['value' => 'a'],
                ['value' => 'b']
            ],
            'model' => $page
        ]);

        $this->assertTrue(Validations::option($field, 'a'));
    }

    public function testOptionInvalid()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Please select a valid option');

        $page  = new Page(['slug' => 'test']);
        $field = new Field('test', [
            'options' => [
                ['value' => 'a'],
                ['value' => 'b']
            ],
            'model' => $page
        ]);

        Validations::option($field, 'c');
    }

    public function testOptionsValid()
    {
        $page  = new Page(['slug' => 'test']);
        $field = new Field('test', [
            'options' => [
                ['value' => 'a'],
                ['value' => 'b']
            ],
            'model' => $page
        ]);

        $this->assertTrue(Validations::options($field, ['a', 'b']));
    }

    public function testOptionsInvalid()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Please select a valid option');

        $page  = new Page(['slug' => 'test']);
        $field = new Field('test', [
            'options' => [
                ['value' => 'a'],
                ['value' => 'b']
            ],
            'model' => $page
        ]);

        Validations::options($field, ['a', 'c']);
    }

    public function testTimeValid()
    {
        $page  = new Page(['slug' => 'test']);
        $field = new Field('test', ['model' => $page]);
        $this->assertTrue(Validations::time($field, '10:12'));
    }

    public function testTimeInvalid()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Please enter a valid time');

        $page  = new Page(['slug' => 'test']);
        $field = new Field('test', ['model' => $page]);
        Validations::time($field, '99:99');
    }
}
