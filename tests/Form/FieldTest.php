<?php

namespace Kirby\Form;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use PHPUnit\Framework\TestCase;

class FieldTest extends TestCase
{
    public function setUp(): void
    {
        new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);

        Field::$types  = [];
        Field::$mixins = [];
    }

    public function tearDown(): void
    {
        Field::$types  = [];
        Field::$mixins = [];
    }

    public function testAfter()
    {
        Field::$types = [
            'test' => []
        ];

        // untranslated
        $field = new Field('test', [
            'model' => new Page(['slug' => 'test']),
            'after' => 'test'
        ]);

        $this->assertEquals('test', $field->after());
        $this->assertEquals('test', $field->after);

        // translated
        $field = new Field('test', [
            'model' => new Page(['slug' => 'test']),
            'after' => [
                'en' => 'en',
                'de' => 'de'
            ]
        ]);

        $this->assertEquals('en', $field->after());
        $this->assertEquals('en', $field->after);
    }

    public function testAutofocus()
    {
        Field::$types = [
            'test' => []
        ];

        // default autofocus
        $field = new Field('test', [
            'model'  => new Page(['slug' => 'test']),
        ]);

        $this->assertFalse($field->autofocus());
        $this->assertFalse($field->autofocus);

        // enabled autofocus
        $field = new Field('test', [
            'model' => new Page(['slug' => 'test']),
            'autofocus' => true
        ]);

        $this->assertTrue($field->autofocus());
        $this->assertTrue($field->autofocus);
    }

    public function testBefore()
    {
        Field::$types = [
            'test' => []
        ];

        // untranslated
        $field = new Field('test', [
            'model'  => new Page(['slug' => 'test']),
            'before' => 'test'
        ]);

        $this->assertEquals('test', $field->before());
        $this->assertEquals('test', $field->before);

        // translated
        $field = new Field('test', [
            'model' => new Page(['slug' => 'test']),
            'before' => [
                'en' => 'en',
                'de' => 'de'
            ]
        ]);

        $this->assertEquals('en', $field->before());
        $this->assertEquals('en', $field->before);
    }

    public function testDefault()
    {
        Field::$types = [
            'test' => []
        ];

        // default
        $field = new Field('test', [
            'model'  => new Page(['slug' => 'test']),
        ]);

        $this->assertNull($field->default());
        $this->assertNull($field->default);
        $this->assertNull($field->value());
        $this->assertNull($field->value);

        // specific default
        $field = new Field('test', [
            'model'   => new Page(['slug' => 'test']),
            'default' => 'test'
        ]);

        $this->assertEquals('test', $field->default());
        $this->assertEquals('test', $field->default);
        $this->assertEquals('test', $field->data(true));

        // don't overwrite existing values
        $field = new Field('test', [
            'model'   => new Page(['slug' => 'test']),
            'default' => 'test',
            'value'   => 'something'
        ]);

        $this->assertEquals('test', $field->default());
        $this->assertEquals('test', $field->default);
        $this->assertEquals('something', $field->value());
        $this->assertEquals('something', $field->value);
        $this->assertEquals('something', $field->data(true));
    }

    public function testDisabled()
    {
        Field::$types = [
            'test' => []
        ];

        // default state
        $field = new Field('test', [
            'model'  => new Page(['slug' => 'test']),
        ]);

        $this->assertFalse($field->disabled());
        $this->assertFalse($field->disabled);

        // disabled
        $field = new Field('test', [
            'model' => new Page(['slug' => 'test']),
            'disabled' => true
        ]);

        $this->assertTrue($field->disabled());
        $this->assertTrue($field->disabled);
    }

    public function testErrors()
    {
        Field::$types = [
            'test' => []
        ];

        // default
        $field = new Field('test', [
            'model' => new Page(['slug' => 'test']),
        ]);

        $this->assertEquals([], $field->errors());

        // required
        $field = new Field('test', [
            'model'    => new Page(['slug' => 'test']),
            'required' => true
        ]);

        $expected = [
            'required' => 'Please enter something',
        ];

        $this->assertEquals($expected, $field->errors());
    }

    public function testHelp()
    {
        Field::$types = [
            'test' => []
        ];

        // untranslated
        $field = new Field('test', [
            'model'  => new Page(['slug' => 'test']),
            'help' => 'test'
        ]);

        $this->assertEquals('test', $field->help());
        $this->assertEquals('test', $field->help);

        // translated
        $field = new Field('test', [
            'model' => new Page(['slug' => 'test']),
            'help' => [
                'en' => 'en',
                'de' => 'de'
            ]
        ]);

        $this->assertEquals('en', $field->help());
        $this->assertEquals('en', $field->help);
    }

    public function testIcon()
    {
        Field::$types = [
            'test' => []
        ];

        // default
        $field = new Field('test', [
            'model' => new Page(['slug' => 'test']),
        ]);

        $this->assertNull($field->icon());
        $this->assertNull($field->icon);

        // specific icon
        $field = new Field('test', [
            'model' => new Page(['slug' => 'test']),
            'icon'  => 'test'
        ]);

        $this->assertEquals('test', $field->icon());
        $this->assertEquals('test', $field->icon);

        Field::$types = [
            'test' => [
                'props' => [
                    'icon' => function (string $icon = 'test') {
                        return $icon;
                    }
                ]
            ]
        ];

        // prop default
        $field = new Field('test', [
            'model' => new Page(['slug' => 'test']),
        ]);

        $this->assertEquals('test', $field->icon());
        $this->assertEquals('test', $field->icon);
    }

    public function emptyValuesProvider()
    {
        return [
            ['', true],
            [null, true],
            [[], true],
            [0, false],
            ['0', false]
        ];
    }

    /**
     * @dataProvider emptyValuesProvider
     */
    public function testIsEmpty($value, $expected)
    {
        Field::$types = [
            'test' => []
        ];

        $field = new Field('test', [
            'model' => new Page(['slug' => 'test']),
            'value' => $value
        ]);

        $this->assertEquals($expected, $field->isEmpty());
        $this->assertEquals($expected, $field->isEmpty($value));
    }

    public function testIsEmptyWithCustomFunction()
    {
        Field::$types = [
            'test' => [
                'isEmpty' => function ($value) {
                    return $value === 0;
                }
            ]
        ];

        $field = new Field('test', [
            'model' => new Page(['slug' => 'test'])
        ]);

        $this->assertFalse($field->isEmpty(null));
        $this->assertTrue($field->isEmpty(0));
    }

    public function testIsInvalidOrValid()
    {
        Field::$types = [
            'test' => []
        ];

        // default
        $field = new Field('test', [
            'model' => new Page(['slug' => 'test']),
        ]);

        $this->assertTrue($field->isValid());
        $this->assertFalse($field->isInvalid());

        // required
        $field = new Field('test', [
            'model'    => new Page(['slug' => 'test']),
            'required' => true
        ]);

        $this->assertFalse($field->isValid());
        $this->assertTrue($field->isInvalid());
    }

    public function testIsRequired()
    {
        Field::$types = [
            'test' => []
        ];

        $field = new Field('test', [
            'model' => new Page(['slug' => 'test']),
        ]);

        $this->assertFalse($field->isRequired());

        $field = new Field('test', [
            'model'    => new Page(['slug' => 'test']),
            'required' => true
        ]);

        $this->assertTrue($field->isRequired());
    }

    public function testKirby()
    {
        Field::$types = [
            'test' => []
        ];

        $field = new Field('test', [
            'model' => $model = new Page(['slug' => 'test'])
        ]);

        $this->assertEquals($model->kirby(), $field->kirby());
    }

    public function testLabel()
    {
        Field::$types = [
            'test' => []
        ];

        // untranslated
        $field = new Field('test', [
            'model'  => new Page(['slug' => 'test']),
            'label' => 'test'
        ]);

        $this->assertEquals('test', $field->label());
        $this->assertEquals('test', $field->label);

        // translated
        $field = new Field('test', [
            'model' => new Page(['slug' => 'test']),
            'label' => [
                'en' => 'en',
                'de' => 'de'
            ]
        ]);

        $this->assertEquals('en', $field->label());
        $this->assertEquals('en', $field->label);
    }

    public function testModel()
    {
        Field::$types = [
            'test' => []
        ];

        $field = new Field('test', [
            'model' => $model = new Page(['slug' => 'test'])
        ]);

        $this->assertEquals($model, $field->model());
    }

    public function testName()
    {
        Field::$types = [
            'test' => []
        ];

        // no specific name. type should be used
        $field = new Field('test', [
            'model' => $model = new Page(['slug' => 'test'])
        ]);

        $this->assertEquals('test', $field->name());

        // specific name
        $field = new Field('test', [
            'model' => $model = new Page(['slug' => 'test']),
            'name'  => 'mytest'
        ]);

        $this->assertEquals('mytest', $field->name());
    }

    public function testPlaceholder()
    {
        Field::$types = [
            'test' => []
        ];

        // untranslated
        $field = new Field('test', [
            'model'       => new Page(['slug' => 'test']),
            'placeholder' => 'test'
        ]);

        $this->assertEquals('test', $field->placeholder());
        $this->assertEquals('test', $field->placeholder);

        // translated
        $field = new Field('test', [
            'model' => new Page(['slug' => 'test']),
            'placeholder' => [
                'en' => 'en',
                'de' => 'de'
            ]
        ]);

        $this->assertEquals('en', $field->placeholder());
        $this->assertEquals('en', $field->placeholder);
    }

    public function testSave()
    {
        Field::$types = [
            'store-me' => [
                'save' => true
            ],
            'dont-store-me' => [
                'save' => false
            ]
        ];

        $a = new Field('store-me', [
            'model' => new Page(['slug' => 'test'])
        ]);

        $this->assertTrue($a->save());

        $b = new Field('dont-store-me', [
            'model' => new Page(['slug' => 'test'])
        ]);

        $this->assertFalse($b->save());
    }

    public function testSaveHandler()
    {
        Field::$types = [
            'test' => [
                'props' => [
                    'value' => function ($value) {
                        return $value;
                    }
                ],
                'save' => function ($value) {
                    return implode(', ', $value);
                }
            ]
        ];

        $field = new Field('test', [
            'model' => new Page(['slug' => 'test']),
            'value' => ['a', 'b', 'c']
        ]);

        $this->assertEquals('a, b, c', $field->data());
    }

    public function testToArray()
    {
        Field::$types = [
            'test' => [
                'props' => [
                    'foo' => function ($foo) {
                        return $foo;
                    }
                ]
            ]
        ];

        $field = new Field('test', [
            'model' => $model = new Page(['slug' => 'test']),
            'foo'   => 'bar'
        ]);

        $array = $field->toArray();

        $this->assertEquals('test', $array['name']);
        $this->assertEquals('test', $array['type']);
        $this->assertEquals('bar', $array['foo']);
        $this->assertEquals('1/1', $array['width']);

        $this->assertArrayHasKey('signature', $array);
        $this->assertArrayNotHasKey('model', $array);
    }

    public function testWidth()
    {
        Field::$types = [
            'test' => []
        ];

        // default width
        $field = new Field('test', [
            'model' => new Page(['slug' => 'test']),
        ]);

        $this->assertEquals('1/1', $field->width());
        $this->assertEquals('1/1', $field->width);

        // specific width
        $field = new Field('test', [
            'model' => new Page(['slug' => 'test']),
            'width' => '1/2'
        ]);

        $this->assertEquals('1/2', $field->width());
        $this->assertEquals('1/2', $field->width);
    }
}
