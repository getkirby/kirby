<?php

namespace Kirby\Form;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FieldTest extends TestCase
{
    protected $originalMixins;

    public function setUp(): void
    {
        new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);

        Field::$types = [];

        // make a backup of the system mixins
        $this->originalMixins = Field::$mixins;
    }

    public function tearDown(): void
    {
        Field::$types = [];

        Field::$mixins = $this->originalMixins;
    }

    public function testWithoutModel()
    {
        Field::$types = [
            'test' => []
        ];

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Field requires a model');

        $field = new Field('test');
    }

    public function testAfter()
    {
        Field::$types = [
            'test' => []
        ];

        $page = new Page(['slug' => 'blog']);

        // untranslated
        $field = new Field('test', [
            'model' => $page,
            'after' => 'test'
        ]);

        $this->assertEquals('test', $field->after());
        $this->assertEquals('test', $field->after);

        // translated
        $field = new Field('test', [
            'model' => $page,
            'after' => [
                'en' => 'en',
                'de' => 'de'
            ]
        ]);

        $this->assertEquals('en', $field->after());
        $this->assertEquals('en', $field->after);

        // with query
        $field = new Field('test', [
            'model' => $page,
            'after' => '{{ page.slug }}'
        ]);

        $this->assertEquals('blog', $field->after());
        $this->assertEquals('blog', $field->after);
    }

    public function testAutofocus()
    {
        Field::$types = [
            'test' => []
        ];

        $page = new Page(['slug' => 'test']);

        // default autofocus
        $field = new Field('test', [
            'model'  => $page,
        ]);

        $this->assertFalse($field->autofocus());
        $this->assertFalse($field->autofocus);

        // enabled autofocus
        $field = new Field('test', [
            'model' => $page,
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

        $page = new Page(['slug' => 'blog']);

        // untranslated
        $field = new Field('test', [
            'model' => $page,
            'before' => 'test'
        ]);

        $this->assertEquals('test', $field->before());
        $this->assertEquals('test', $field->before);

        // translated
        $field = new Field('test', [
            'model' => $page,
            'before' => [
                'en' => 'en',
                'de' => 'de'
            ]
        ]);

        $this->assertEquals('en', $field->before());
        $this->assertEquals('en', $field->before);

        // with query
        $field = new Field('test', [
            'model' => $page,
            'before' => '{{ page.slug }}'
        ]);

        $this->assertEquals('blog', $field->before());
        $this->assertEquals('blog', $field->before);
    }

    public function testDefault()
    {
        Field::$types = [
            'test' => []
        ];

        $page = new Page(['slug' => 'blog']);

        // default
        $field = new Field('test', [
            'model' => $page
        ]);

        $this->assertNull($field->default());
        $this->assertNull($field->default);
        $this->assertNull($field->value());
        $this->assertNull($field->value);

        // specific default
        $field = new Field('test', [
            'model'   => $page,
            'default' => 'test'
        ]);

        $this->assertEquals('test', $field->default());
        $this->assertEquals('test', $field->default);
        $this->assertEquals('test', $field->data(true));

        // don't overwrite existing values
        $field = new Field('test', [
            'model'   => $page,
            'default' => 'test',
            'value'   => 'something'
        ]);

        $this->assertEquals('test', $field->default());
        $this->assertEquals('test', $field->default);
        $this->assertEquals('something', $field->value());
        $this->assertEquals('something', $field->value);
        $this->assertEquals('something', $field->data(true));

        // with query
        $field = new Field('test', [
            'model' => $page,
            'default' => '{{ page.slug }}'
        ]);

        $this->assertEquals('blog', $field->default());
        $this->assertEquals('blog', $field->default);
        $this->assertEquals('blog', $field->data(true));
    }

    public function testDisabled()
    {
        Field::$types = [
            'test' => []
        ];

        $page = new Page(['slug' => 'test']);

        // default state
        $field = new Field('test', [
            'model'  => $page
        ]);

        $this->assertFalse($field->disabled());
        $this->assertFalse($field->disabled);

        // disabled
        $field = new Field('test', [
            'model' => $page,
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

        $page = new Page(['slug' => 'test']);

        // default
        $field = new Field('test', [
            'model' => $page,
        ]);

        $this->assertEquals([], $field->errors());

        // required
        $field = new Field('test', [
            'model'    => $page,
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

        $page = new Page(['slug' => 'test']);

        // untranslated
        $field = new Field('test', [
            'model'  => $page,
            'help' => 'test'
        ]);

        $this->assertEquals('<p>test</p>', $field->help());
        $this->assertEquals('<p>test</p>', $field->help);

        // translated
        $field = new Field('test', [
            'model' => $page,
            'help' => [
                'en' => 'en',
                'de' => 'de'
            ]
        ]);

        $this->assertEquals('<p>en</p>', $field->help());
        $this->assertEquals('<p>en</p>', $field->help);
    }

    public function testIcon()
    {
        Field::$types = [
            'test' => []
        ];

        $page = new Page(['slug' => 'test']);

        // default
        $field = new Field('test', [
            'model' => $page,
        ]);

        $this->assertNull($field->icon());
        $this->assertNull($field->icon);

        // specific icon
        $field = new Field('test', [
            'model' => $page,
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
            'model' => $page,
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

        $page = new Page(['slug' => 'test']);

        $field = new Field('test', [
            'model' => $page,
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

        $page = new Page(['slug' => 'test']);

        $field = new Field('test', [
            'model' => $page
        ]);

        $this->assertFalse($field->isEmpty(null));
        $this->assertTrue($field->isEmpty(0));
    }

    public function testIsInvalidOrValid()
    {
        Field::$types = [
            'test' => []
        ];

        $page = new Page(['slug' => 'test']);

        // default
        $field = new Field('test', [
            'model' => $page,
        ]);

        $this->assertTrue($field->isValid());
        $this->assertFalse($field->isInvalid());

        // required
        $field = new Field('test', [
            'model'    => $page,
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

        $page = new Page(['slug' => 'test']);

        $field = new Field('test', [
            'model' => $page,
        ]);

        $this->assertFalse($field->isRequired());

        $field = new Field('test', [
            'model'    => $page,
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

        $page = new Page(['slug' => 'blog']);

        // untranslated
        $field = new Field('test', [
            'model'  => $page,
            'label' => 'test'
        ]);

        $this->assertEquals('test', $field->label());
        $this->assertEquals('test', $field->label);

        // translated
        $field = new Field('test', [
            'model' => $page,
            'label' => [
                'en' => 'en',
                'de' => 'de'
            ]
        ]);

        $this->assertEquals('en', $field->label());
        $this->assertEquals('en', $field->label);

        // with query
        $field = new Field('test', [
            'model' => $page,
            'label' => '{{ page.slug }}'
        ]);

        $this->assertEquals('blog', $field->label());
        $this->assertEquals('blog', $field->label);
    }

    public function testMixinMin()
    {
        Field::$mixins['min'] = include kirby()->root('kirby') . '/config/fields/mixins/min.php';

        Field::$types = [
            'test' => ['mixins' => ['min']]
        ];

        $page = new Page(['slug' => 'test']);

        $field = new Field('test', [
            'model' => $page,
        ]);

        $this->assertFalse($field->isRequired());
        $this->assertNull($field->min());

        $field = new Field('test', [
            'model' => $page,
            'min'   => 5
        ]);

        $this->assertTrue($field->isRequired());
        $this->assertEquals(5, $field->min());

        $field = new Field('test', [
            'model' => $page,
            'required' => true
        ]);

        $this->assertTrue($field->isRequired());
        $this->assertEquals(1, $field->min());

        $field = new Field('test', [
            'model'    => $page,
            'required' => true,
            'min'      => 5
        ]);

        $this->assertTrue($field->isRequired());
        $this->assertEquals(5, $field->min());
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

        $page = new Page(['slug' => 'blog']);

        // untranslated
        $field = new Field('test', [
            'model'       => $page,
            'placeholder' => 'test'
        ]);

        $this->assertEquals('test', $field->placeholder());
        $this->assertEquals('test', $field->placeholder);

        // translated
        $field = new Field('test', [
            'model' => $page,
            'placeholder' => [
                'en' => 'en',
                'de' => 'de'
            ]
        ]);

        $this->assertEquals('en', $field->placeholder());
        $this->assertEquals('en', $field->placeholder);

        // with query
        $field = new Field('test', [
            'model' => $page,
            'placeholder' => '{{ page.slug }}'
        ]);

        $this->assertEquals('blog', $field->placeholder());
        $this->assertEquals('blog', $field->placeholder);
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

        $page = new Page(['slug' => 'test']);

        $a = new Field('store-me', [
            'model' => $page
        ]);

        $this->assertTrue($a->save());

        $b = new Field('dont-store-me', [
            'model' => $page
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

        $page = new Page(['slug' => 'test']);

        $field = new Field('test', [
            'model' => $page,
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

    public function testValidateByAttr()
    {
        Field::$types = [
            'test' => []
        ];

        $model = new Page(['slug' => 'test']);

        // with simple string validation
        $field = new Field('test', [
            'model'    => $model,
            'value'    => 'https://getkirby.com',
            'validate' => 'url'
        ]);
        $this->assertTrue($field->isValid());

        $field = new Field('test', [
            'model'    => $model,
            'value'    => 'definitely not a URL',
            'validate' => 'url'
        ]);
        $this->assertFalse($field->isValid());

        // with an array of validators
        $field = new Field('test', [
            'model'    => $model,
            'value'    => 'thisIsATest',
            'validate' => [
                'startsWith' => 'this',
                'alpha'
            ]
        ]);
        $this->assertTrue($field->isValid());

        $field = new Field('test', [
            'model'    => $model,
            'value'    => 'thisIsATest',
            'validate' => [
                'startsWith' => 'that',
                'alpha'
            ]
        ]);
        $this->assertFalse($field->isValid());

        $field = new Field('test', [
            'model'    => $model,
            'value'    => 'thisIsA123',
            'validate' => [
                'startsWith' => 'this',
                'alpha'
            ]
        ]);
        $this->assertFalse($field->isValid());
    }

    public function testWidth()
    {
        Field::$types = [
            'test' => []
        ];

        $page = new Page(['slug' => 'test']);

        // default width
        $field = new Field('test', [
            'model' => $page,
        ]);

        $this->assertEquals('1/1', $field->width());
        $this->assertEquals('1/1', $field->width);

        // specific width
        $field = new Field('test', [
            'model' => $page,
            'width' => '1/2'
        ]);

        $this->assertEquals('1/2', $field->width());
        $this->assertEquals('1/2', $field->width);
    }

    public function testValidate()
    {
        Field::$types = [
            'test' => []
        ];

        $page = new Page(['slug' => 'test']);

        // default
        $field = new Field('test', [
            'model'    => $page,
            'validate' => [
                'integer'
            ],
        ]);

        $this->assertEquals([], $field->errors());

        // required
        $field = new Field('test', [
            'model'    => $page,
            'required' => true,
            'validate' => [
                'integer'
            ],
        ]);

        $expected = [
            'required' => 'Please enter something',
            'integer'  => 'Please enter a valid integer',
        ];

        $this->assertEquals($expected, $field->errors());

        // invalid
        $field = new Field('test', [
            'model'    => $page,
            'value'    => 'abc',
            'validate' => [
                'integer'
            ],
        ]);

        $expected = [
            'integer' => 'Please enter a valid integer',
        ];

        $this->assertEquals($expected, $field->errors());
    }

    public function testWhenRequired()
    {
        $page = new Page(['slug' => 'test']);

        Field::$types = [
            'foo' => [],
            'bar' => [],
            'baz' => [],
        ];

        $fields = new Fields([
            'foo' => [
                'type'  => 'foo',
                'model' => $page,
                'value' => 'a'
            ],
            'bar' => [
                'type'  => 'bar',
                'model' => $page,
                'value' => 'b'
            ],
            'baz' => [
                'type'  => 'baz',
                'model' => $page,
                'value' => 'c'
            ]
        ]);

        // default
        $field = new Field('foo', [
            'model' => $page,
        ]);

        $this->assertSame([], $field->errors());

        // passed (simple)
        // 'bar' is required if 'foo' value is 'x'
        $field = new Field('bar', [
            'model' => $page,
            'required' => true,
            'when' => [
                'foo' => 'x'
            ]
        ], $fields);

        $this->assertSame([], $field->errors());

        // passed (multiple conditions without any match)
        // 'baz' is required if 'foo' value is 'x' and 'bar' value is 'y'
        $field = new Field('baz', [
            'model' => $page,
            'required' => true,
            'when' => [
                'foo' => 'x',
                'bar' => 'y'
            ]
        ], $fields);

        $this->assertSame([], $field->errors());

        // passed (multiple conditions with single match)
        // 'baz' is required if 'foo' value is 'a' and 'bar' value is 'y'
        $field = new Field('baz', [
            'model' => $page,
            'required' => true,
            'when' => [
                'foo' => 'a',
                'bar' => 'y'
            ]
        ], $fields);

        $this->assertSame([], $field->errors());

        // failed (simple)
        // 'bar' is required if 'foo' value is 'a'
        $field = new Field('bar', [
            'model' => $page,
            'required' => true,
            'when' => [
                'foo' => 'a'
            ]
        ], $fields);

        $expected = [
            'required' => 'Please enter something',
        ];

        $this->assertSame($expected, $field->errors());

        // failed (multiple conditions)
        // 'baz' is required if 'foo' value is 'a' and 'bar' value is 'b'
        $field = new Field('baz', [
            'model' => $page,
            'required' => true,
            'when' => [
                'foo' => 'a',
                'bar' => 'b'
            ]
        ], $fields);

        $this->assertSame($expected, $field->errors());
    }

    public function testCustomValidations()
    {
        Field::$types = [
            'test' => [
                'validations' => [
                    'test' => function ($value) {
                        throw new InvalidArgumentException('Invalid value: ' . $value);
                    }
                ]
            ]
        ];

        $model = new Page(['slug' => 'test']);

        $field = new Field('test', [
            'model' => $model,
            'value' => 'abc'
        ]);

        $this->assertFalse($field->isValid());
        $this->assertSame(['test' => 'Invalid value: abc'], $field->errors());
    }

    public function testApi()
    {
        // no defined as default
        Field::$types = [
            'test' => []
        ];

        $model = new Page(['slug' => 'test']);

        $field = new Field('test', [
            'model' => $model,
        ]);

        $this->assertNull($field->api());

        // return simple string
        Field::$types = [
            'test' => [
                'api' => function () {
                    return 'Hello World';
                }
            ]
        ];

        $model = new Page(['slug' => 'test']);

        $field = new Field('test', [
            'model' => $model,
        ]);

        $this->assertSame('Hello World', $field->api());
    }

    public function testUnsaveable()
    {
        Field::$types = [
            'test' => [
                'save' => false
            ]
        ];

        $model = new Page(['slug' => 'test']);

        $field = new Field('test', [
            'model' => $model,
            'value' => 'something'
        ]);

        $this->assertNull($field->data());
    }
}
