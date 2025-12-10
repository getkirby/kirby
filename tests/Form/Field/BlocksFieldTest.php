<?php

namespace Kirby\Form\Field;

use Kirby\Cms\App;
use Kirby\Cms\Fieldsets;
use Kirby\Cms\Page;
use Kirby\Exception\NotFoundException;
use Kirby\Form\Fields;
use Kirby\Panel\Controller\Dialog\FieldDialogController;
use Kirby\Panel\Controller\Drawer\FieldDrawerController;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(BlocksField::class)]
class BlocksFieldTest extends TestCase
{
	public function testApi(): void
	{
		$field = $this->field('blocks');

		$routes = $field->api();

		$this->assertIsArray($routes);
		$this->assertCount(4, $routes);
	}

	public function testApiUUID(): void
	{
		$field = $this->field('blocks');
		$route = $field->api()[0];

		$response = $route['action']();

		$this->assertIsArray($response);
		$this->assertArrayHasKey('uuid', $response);
	}

	public function testApiPaste(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'html' => '<p>Test</p>'
				]
			]
		]);

		$field = $this->field('blocks');
		$route = $field->api()[1];

		$response = $route['action']();

		$this->assertCount(1, $response);
		$this->assertSame(['text' => '<p>Test</p>'], $response[0]['content']);
		$this->assertFalse($response[0]['isHidden']);
		$this->assertSame('text', $response[0]['type']);
	}

	public function testApiPasteFieldsets(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'html' => '<h1>Hello World</h1><p>Test</p><h6>Sincerely</h6>'
				]
			]
		]);

		$field = $this->field('blocks', ['fieldsets' => ['heading']]);
		$route = $field->api()[1];

		$response = $route['action']();

		$this->assertCount(2, $response);
		$this->assertSame(['level' => 'h1', 'text' => 'Hello World'], $response[0]['content']);
		$this->assertSame('heading', $response[0]['type']);
		$this->assertSame(['level' => 'h6', 'text' => 'Sincerely'], $response[1]['content']);
		$this->assertSame('heading', $response[1]['type']);
	}

	public function testApiFieldset(): void
	{
		$field = $this->field('blocks');
		$route = $field->api()[2];

		$response = $route['action']('text');

		$this->assertSame(['text' => ''], $response['content']);
		$this->assertArrayHasKey('id', $response);
		$this->assertFalse($response['isHidden']);
		$this->assertSame('text', $response['type']);
	}

	public function testDefaultProps(): void
	{
		$field = $this->field('blocks', []);

		$this->assertSame('blocks', $field->type());
		$this->assertSame('blocks', $field->name());
		$this->assertNull($field->max());
		$this->assertInstanceOf(Fieldsets::class, $field->fieldsets());
		$this->assertSame([], $field->value());
		$this->assertTrue($field->save());
	}

	public function testDialogs(): void
	{
		$field = $this->field('blocks', []);

		$result = $field->dialogs()[0]['action']('text', 'text', 'test-path');

		$this->assertInstanceOf(FieldDialogController::class, $result);
		$this->assertInstanceOf(BaseField::class, $result->field);
		$this->assertSame('text', $result->field->name());
		$this->assertSame('test-path', $result->path);
	}

	public function testDrawers(): void
	{
		$field = $this->field('blocks', []);

		$result = $field->drawers()[0]['action']('text', 'text', 'test-path');

		$this->assertInstanceOf(FieldDrawerController::class, $result);
		$this->assertInstanceOf(BaseField::class, $result->field);
		$this->assertSame('text', $result->field->name());
		$this->assertSame('test-path', $result->path);
	}

	public function testGroups(): void
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

	public function testMax(): void
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

	public function testMin(): void
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

	public function testPretty(): void
	{
		$value = [
			[
				'id'	  => 'uuid',
				'type'    => 'heading',
				'content' => [
					'text' => 'A nice block/heäding'
				]
			],
		];

		$expected = [
			[
				'content' => [
					'level' => '',
					'text'  => 'A nice block/heäding'
				],
				'id'       => 'uuid',
				'isHidden' => false,
				'type'     => 'heading',
			],
		];

		$field = $this->field('blocks', [
			'pretty' => true,
			'value'  => $value
		]);

		$pretty = json_encode($expected, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

		$this->assertTrue($field->pretty());
		$this->assertSame($pretty, $field->toStoredValue());
	}

	public function testProps(): void
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
		$this->assertFalse($props['autofocus']);
		$this->assertNull($props['default']);
		$this->assertFalse($props['disabled']);
		$this->assertNull($props['help']);
		$this->assertSame('Blocks', $props['label']);
		$this->assertSame('blocks', $props['name']);
		$this->assertFalse($props['required']);
		$this->assertTrue($props['saveable']);
		$this->assertTrue($props['translate']);
		$this->assertSame('blocks', $props['type']);
		$this->assertSame('1/1', $props['width']);
	}

	public function testRequired(): void
	{
		$field = $this->field('blocks', [
			'required' => true
		]);

		$this->assertTrue($field->required());
	}

	public function testRequiredInvalid(): void
	{
		$field = $this->field('blocks', [
			'required' => true
		]);

		$this->assertFalse($field->isValid());
	}

	public function testRequiredValid(): void
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

	public function testReset(): void
	{
		$field = $this->field('blocks');

		$field->fill([
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
		]);

		$this->assertCount(2, $field->toFormValue());

		$field->reset();

		$this->assertSame([], $field->toFormValue());
	}

	public function testToStoredValue(): void
	{
		$value = [
			[
				'id'	  => 'uuid',
				'type'    => 'heading',
				'content' => [
					'text' => 'A nice block/heäding'
				]
			],
		];

		$expected = [
			[
				'content' => [
					'level' => '',
					'text'  => 'A nice block/heäding'
				],
				'id'       => 'uuid',
				'isHidden' => false,
				'type'     => 'heading',
			],
		];

		$field = $this->field('blocks', [
			'value' => $value
		]);

		$this->assertSame(
			json_encode($expected, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
			$field->toStoredValue()
		);

		// empty tests
		$field->fill(null);
		$this->assertSame('', $field->toStoredValue());

		$field->fill([]);
		$this->assertSame('', $field->toStoredValue());
	}

	public function testTranslateField(): void
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

		// we need an authenticated user to make sure
		// that the fields are not disabled by default
		$app->impersonate('kirby');

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

	public function testTranslateFieldset(): void
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
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The fieldset not-exists could not be found');

		$field->fieldset('not-exists');
	}

	public function testValidations(): void
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

	public function testValidationsInvalid(): void
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
		$this->assertSame([
			'blocks' => 'There\'s an error on the "Video-URL" field in block 2 using the "Video" block type'
		], $field->errors());
	}

	public function testValidationsWithInvalidBlockType(): void
	{
		$field = $this->field('blocks', [
			'value' => [
				[
					'type' => 'not-exists',
				]
			]
		]);

		$this->assertTrue($field->isValid());
		$this->assertSame([], $field->errors());
	}

	public function testEmpty(): void
	{
		$field = $this->field('blocks', [
			'empty' => $value = 'Custom empty text'
		]);

		$this->assertSame($value, $field->empty());
	}

	public function testWhen(): void
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

	public function testDefault(): void
	{
		$field = $this->field('blocks', [
			'default' => [
				[
					'type' => 'heading',
					'text' => 'Some title'
				]
			]
		]);

		$default = $field->default();

		$this->assertCount(1, $default);
		$this->assertSame('heading', $default[0]['type']);
		$this->assertSame('Some title', $default[0]['text']);
		$this->assertArrayHasKey('id', $default[0]);
	}

	public function testInvalidType(): void
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
					'type'    => 'not-exists',
					'content' => [
						'text' => 'b'
					]
				],
				[
					'type'    => 'text',
					'content' => [
						'text' => 'c'
					]
				],
			]
		]);

		$this->assertCount(3, $field->value());
		$this->assertSame('heading', $field->value()[0]['type']);
		$this->assertSame('not-exists', $field->value()[1]['type']);
		$this->assertSame(['text' => 'b'], $field->value()[1]['content']);
		$this->assertSame('text', $field->value()[2]['type']);
	}
}
