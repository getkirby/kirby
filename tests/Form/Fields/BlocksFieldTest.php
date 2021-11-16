<?php

namespace Kirby\Form\Fields;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Form\Fields;

class BlocksFieldTest extends TestCase
{
    public function testDefaultProps()
    {
        $field = $this->field('blocks', []);

        $this->assertSame('blocks', $field->type());
        $this->assertSame('blocks', $field->name());
        $this->assertSame(null, $field->max());
        $this->assertInstanceOf('Kirby\Cms\Fieldsets', $field->fieldsets());
        $this->assertSame([], $field->value());
        $this->assertTrue($field->save());
    }

    public function testGroups()
    {
        $field = $this->field('blocks', [
            'group'     => 'test',
            'fieldsets' => [
                'text' => [
                    'label'     => 'Text',
                    'type'      => 'group',
                    'fieldsets' => [
                        'text'    => true,
                        'heading' => true
                    ]
                ],
                'media' => [
                    'label' => 'Media',
                    'type'  => 'group',
                    'fieldsets' => [
                        'image' => true,
                        'video' => true
                    ]
                ]
            ]
        ]);

        $group  = $field->group();
        $groups = $field->fieldsets()->groups();

        $this->assertSame('test', $group);

        $this->assertArrayHasKey('text', $groups);
        $this->assertArrayHasKey('media', $groups);

        $this->assertSame(['text', 'heading'], $groups['text']['sets']);
        $this->assertSame(['image', 'video'], $groups['media']['sets']);
    }

    public function testMax()
    {
        $field = $this->field('blocks', [
            'value' => [
                [
                    'type'    => 'heading',
                    'content' => [
                        'text' => 'a'
                    ]
                ],
                [
                    'type'    => 'heading',
                    'content' => [
                        'text' => 'b'
                    ]
                ],
            ],
            'max' => 1
        ]);

        $this->assertSame(1, $field->max());
        $this->assertFalse($field->isValid());
        $this->assertSame($field->errors()['blocks'], 'You must not add more than one block');
    }

    public function testMin()
    {
        $field = $this->field('blocks', [
            'value' => [
                [
                    'type'    => 'heading',
                    'content' => ['text' => 'a']
                ],
            ],
            'min' => 2
        ]);

        $this->assertSame(2, $field->min());
        $this->assertFalse($field->isValid());
        $this->assertSame($field->errors()['blocks'], 'You must add at least 2 blocks');
    }

    public function testPretty()
    {
        $value = [
            [
                'type'    => 'heading',
                'content' => [
                    'text' => 'A nice heading'
                ]
            ],
        ];

        $expected = [
            [
                'type'    => 'heading',
                'content' => [
                    'level' => '',
                    'text'  => 'A nice heading'
                ]
            ],
        ];

        $field = $this->field('blocks', [
            'pretty' => true,
            'value'  => $value
        ]);

        $pretty = json_encode($expected, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $this->assertTrue($field->pretty());
        $this->assertSame($pretty, $field->store($value));
    }

    public function testProps()
    {
        $field = $this->field('blocks');

        $props     = $field->props();
        $fieldsets = $props['fieldsets'];

        $this->assertIsArray($props);
        $this->assertNull($props['empty']);
        $this->assertSame([
            'code', 'gallery', 'heading', 'image', 'line', 'list', 'markdown', 'quote', 'text', 'video'
        ], array_keys($fieldsets));
        $this->assertNull($props['fieldsetGroups']);
        $this->assertSame('blocks', $props['group']);
        $this->assertNull($props['max']);
        $this->assertNull($props['min']);
        $this->assertNull($props['after']);
        $this->assertFalse($props['autofocus']);
        $this->assertNull($props['before']);
        $this->assertNull($props['default']);
        $this->assertFalse($props['disabled']);
        $this->assertNull($props['help']);
        $this->assertNull($props['icon']);
        $this->assertSame('Blocks', $props['label']);
        $this->assertSame('blocks', $props['name']);
        $this->assertNull($props['placeholder']);
        $this->assertFalse($props['required']);
        $this->assertTrue($props['saveable']);
        $this->assertTrue($props['translate']);
        $this->assertSame('blocks', $props['type']);
        $this->assertSame('1/1', $props['width']);
    }

    public function testRequired()
    {
        $field = $this->field('blocks', [
            'required' => true
        ]);

        $this->assertTrue($field->required());
    }

    public function testRequiredInvalid()
    {
        $field = $this->field('blocks', [
            'required' => true
        ]);

        $this->assertFalse($field->isValid());
    }

    public function testRequiredValid()
    {
        $field = $this->field('blocks', [
            'value' => [
                [
                    'type'    => 'heading',
                    'content' => [
                        'text' => 'A nice heading'
                    ]
                ],
            ],
            'required' => true
        ]);

        $this->assertTrue($field->isValid());
    }

    public function testRoutes()
    {
        $field = $this->field('blocks');

        $routes = $field->routes();

        $this->assertIsArray($routes);
        $this->assertCount(4, $routes);
    }

    public function testRouteUUID()
    {
        $field = $this->field('blocks');
        $route = $field->routes()[0];

        $response = $route['action']();

        $this->assertIsArray($response);
        $this->assertArrayHasKey('uuid', $response);
    }

    public function testRoutePaste()
    {
        $this->app = $this->app->clone([
            'request' => [
                'query' => [
                    'html' => '<p>Test</p>'
                ]
            ]
        ]);

        $field = $this->field('blocks');
        $route = $field->routes()[1];

        $response = $route['action']();

        $this->assertCount(1, $response);
        $this->assertSame(['text' => '<p>Test</p>'], $response[0]['content']);
        $this->assertFalse($response[0]['isHidden']);
        $this->assertSame('text', $response[0]['type']);
    }

    public function testRouteFieldset()
    {
        $field = $this->field('blocks');
        $route = $field->routes()[2];

        $response = $route['action']('text');

        $this->assertSame(['text' => ''], $response['content']);
        $this->assertArrayHasKey('id', $response);
        $this->assertFalse($response['isHidden']);
        $this->assertSame('text', $response['type']);
    }

    public function testStore()
    {
        $value = [
            [
                'type'    => 'heading',
                'content' => [
                    'text' => 'A nice heading'
                ]
            ],
        ];

        $expected = [
            [
                'type'    => 'heading',
                'content' => [
                    'level' => '',
                    'text'  => 'A nice heading'
                ]
            ],
        ];

        $field = $this->field('blocks', [
            'value' => $value
        ]);

        $this->assertSame(json_encode($expected), $field->store($value));

        // empty tests
        $this->assertSame('', $field->store(null));
        $this->assertSame('', $field->store([]));
    }

    public function testTranslateField()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'options' => [
                'languages' => true
            ],
            'languages' => [
                [
                    'code'    => 'en',
                    'default' => true
                ],
                [
                    'code' => 'de',
                ]
            ]
        ]);

        $props = [
            'fieldsets' => [
                'heading' => [
                    'fields' => [
                        'text' => [
                            'type' => 'text',
                            'translate' => false,
                        ]
                    ]
                ]
            ]
        ];

        // default language
        $app->setCurrentLanguage('en');
        $field = $this->field('blocks', $props);

        $this->assertFalse($field->fields('heading')['text']['translate']);
        $this->assertFalse($field->fields('heading')['text']['disabled']);

        // secondary language
        $app = $app->clone();
        $app->setCurrentLanguage('de');

        $field = $this->field('blocks', $props);
        $this->assertFalse($field->fields('heading')['text']['translate']);
        $this->assertTrue($field->fields('heading')['text']['disabled']);
    }

    public function testTranslateFieldset()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'options' => [
                'languages' => true
            ],
            'languages' => [
                [
                    'code'    => 'en',
                    'default' => true
                ],
                [
                    'code' => 'de',
                ]
            ]
        ]);

        $props = [
            'fieldsets' => [
                'heading' => [
                    'translate' => false,
                    'fields'    => [
                        'text' => [
                            'type' => 'text'
                        ]
                    ]
                ]
            ]
        ];

        // default language
        $app->setCurrentLanguage('en');
        $field = $this->field('blocks', $props);

        $this->assertFalse($field->fieldset('heading')->translate());
        $this->assertFalse($field->fieldset('heading')->disabled());

        // secondary language
        $app = $app->clone();
        $app->setCurrentLanguage('de');

        $field = $this->field('blocks', $props);
        $this->assertFalse($field->fieldset('heading')->translate());
        $this->assertTrue($field->fieldset('heading')->disabled());

        // invalid fieldset calling
        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('The fieldset not-exists could not be found');

        $field->fieldset('not-exists');
    }

    public function testValidations()
    {
        $field = $this->field('blocks', [
            'value' => [
                [
                    'type'    => 'heading',
                    'content' => [
                        'text' => 'A nice heading',
                    ]
                ],
                [
                    'type'    => 'video',
                    'content' => [
                        'url' => 'https://www.youtube.com/watch?v=EDVYjxWMecc',
                    ]
                ]
            ],
            'required' => true
        ]);

        $this->assertTrue($field->isValid());
    }

    public function testValidationsInvalid()
    {
        $field = $this->field('blocks', [
            'value' => [
                [
                    'type'    => 'heading',
                    'content' => [
                        'text' => 'A nice heading',
                    ]
                ],
                [
                    'type'    => 'video',
                    'content' => [
                        'url' => 'Invalid URL',
                    ]
                ]
            ],
            'required' => true
        ]);

        $this->assertFalse($field->isValid());
        $this->assertSame(['blocks' => 'There\'s an error in block 2'], $field->errors());
    }

    public function testEmpty()
    {
        $field = $this->field('blocks', [
            'empty' => $value = 'Custom empty text'
        ]);

        $this->assertSame($value, $field->empty());
    }

    public function testWhen()
    {
        $page = new Page(['slug' => 'test']);

        $fields = new Fields([
            'foo' => [
                'type'  => 'text',
                'model' => $page,
                'value' => 'a'
            ],
            'bar' => [
                'type'  => 'blocks',
                'model' => $page,
                'value' => []
            ]
        ]);

        // default
        $field = $this->field('blocks', [
            'model' => $page,
        ], $fields);

        $this->assertSame([], $field->errors());

        // passed
        $field = $this->field('blocks', [
            'model' => $page,
            'required' => true,
            'when' => [
                'foo' => 'x'
            ]
        ], $fields);

        $this->assertSame([], $field->errors());

        // failed
        $field = $this->field('blocks', [
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
    }
}
