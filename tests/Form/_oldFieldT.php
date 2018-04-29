<?php

// namespace Kirby\Form;

// class FieldTest extends \PHPUnit\Framework\TestCase
// {

//     public function testField()
//     {
//         $field = new Field([
//             'type'  => 'dummy'
//         ], __DIR__ . '/fixtures/DummyField.php');

//         $this->assertInstanceOf(Field::class, $field);
//         $this->assertEquals('dummy', $field->type());
//     }

//     public function testValue()
//     {
//         $field = new Field([
//             'value' => 'true',
//         ], __DIR__ . '/fixtures/DummyField.php');

//         $this->assertEquals(true, $field->value());
//         $this->assertEquals('true', $field->valueToString());
//     }

//     public function testPropDefault()
//     {
//         $field = new Field([], __DIR__ . '/fixtures/DummyField.php');

//         $this->assertEquals('not to last', $field->default());
//         $this->assertEquals('call me maybe', $field->callable());

//         $field = new Field([
//             'default'  => 'overwritten'
//         ], __DIR__ . '/fixtures/DummyField.php');

//         $this->assertEquals('overwritten', $field->default());
//     }

//     public function testCustomMethod()
//     {
//         $field = new Field([], __DIR__ . '/fixtures/DummyField.php');

//         $this->assertEquals('me', $field->isMe());
//     }

//     public function testToArray()
//     {
//         $field = new Field([], __DIR__ . '/fixtures/DummyField.php');

//         $this->assertEquals([
//             'name'     => null,
//             'value'    => false,
//             'disabled' => false,
//             'help'     => null,
//             'label'    => '',
//             'width'    => '1/1',
//             'required' => false,
//             'callable' => 'call me maybe',
//             'default'  => 'not to last'
//         ], $field->toArray());
//     }

//     public function testFactory()
//     {
//         Field::$types['text'] = dirname(dirname(__DIR__)) . '/config/fields/TextField.php';

//         $field = Field::factory([
//             'name' => 'intro',
//             'type' => 'text',
//         ]);

//         $this->assertInstanceOf(Field::class, $field);
//         $this->assertEquals('text', $field->type());
//         $this->assertEquals('intro', $field->name());
//     }

// }
