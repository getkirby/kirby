<?php

namespace Kirby\Form\Field;

use Kirby\Cms\Fieldsets;
use Kirby\Cms\Layouts;

class LayoutFieldTest extends TestCase
{
	public function testDefaultProps()
	{
		$field = $this->field('layout', []);

		$this->assertSame('layout', $field->type());
		$this->assertSame('layout', $field->name());
		$this->assertNull($field->max());
		$this->assertInstanceOf(Fieldsets::class, $field->fieldsets());
		$this->assertSame([], $field->value());
		$this->assertSame([['1/1']], $field->layouts());
		$this->assertNull($field->settings());
		$this->assertTrue($field->save());
	}

	public function testExtends()
	{
		$this->app->clone([
			'blueprints' => [
				'fields/layout-settings' => [
					'fields' => [
						'id' => [
							'type' => 'text'
						],
						'class' => [
							'type' => 'text'
						],
						'background-color' => [
							'type' => 'text'
						]
					]
				]
			]
		]);

		// no settings
		$field = $this->field('layout');

		$this->assertNull($field->settings());

		// extend with simple string
		$field = $this->field('layout', [
			'settings' => 'fields/layout-settings'
		]);

		$fields = $field->settings()->fields();
		$this->assertCount(3, $fields);
		$this->assertArrayHasKey('id', $fields);
		$this->assertArrayHasKey('class', $fields);
		$this->assertArrayHasKey('background-color', $fields);

		// extend with array
		$field = $this->field('layout', [
			'settings' => [
				'extends' => 'fields/layout-settings'
			]
		]);

		$fields = $field->settings()->fields();
		$this->assertCount(3, $fields);
		$this->assertArrayHasKey('id', $fields);
		$this->assertArrayHasKey('class', $fields);
		$this->assertArrayHasKey('background-color', $fields);
	}

	public function testLayouts()
	{
		$field = $this->field('layout', [
			'layouts' => $layouts = [
				['1/1'],
				['1/2', '1/2'],
				['1/4', '1/4', '1/4', '1/4'],
			]
		]);

		$this->assertSame($layouts, $field->layouts());
	}

	public function testProps()
	{
		$field = $this->field('layout');

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
		$this->assertSame('Layout', $props['label']);
		$this->assertSame('layout', $props['name']);
		$this->assertNull($props['placeholder']);
		$this->assertFalse($props['required']);
		$this->assertTrue($props['saveable']);
		$this->assertTrue($props['translate']);
		$this->assertSame('layout', $props['type']);
		$this->assertSame('1/1', $props['width']);
		$this->assertNull($props['settings']);
		$this->assertSame([['1/1']], $props['layouts']);
	}

	public function testRoutes()
	{
		$field = $this->field('layout');

		$routes = $field->routes();

		$this->assertIsArray($routes);
		$this->assertCount(7, $routes);
	}

	public function testToStoredValue()
	{
		$value = [
			[
				'columns' => [
					[
						'blocks' => [
							[
								'type'    => 'heading',
								'content' => [
									'text' => 'A nice block/heäding',
								]
							]
						]
					]
				],
				'attrs' => [
					'url' => 'https://getkirby.com/',
				]
			]
		];

		$field = $this->field('layout', [
			'settings' => [
				'fields' => [
					'url' => [
						'type' => 'url'
					]
				]
			],
			'value' => $value
		]);

		$store = $field->toStoredValue();
		$this->assertIsString($store);

		// ensure that the Unicode characters and slashes are not encoded
		$this->assertStringContainsString('A nice block/heäding', $store);

		$result = json_decode($store, true);

		$this->assertSame(['url' => 'https://getkirby.com/'], $result[0]['attrs']);
		$this->assertArrayHasKey('id', $result[0]);
		$this->assertArrayHasKey('columns', $result[0]);
		$this->assertIsArray($result[0]['columns']);
		$this->assertSame('heading', $result[0]['columns'][0]['blocks'][0]['type']);
		$this->assertSame('A nice block/heäding', $result[0]['columns'][0]['blocks'][0]['content']['text']);

		// empty tests
		$field->fill(null);
		$this->assertSame('', $field->toStoredValue());

		$field->fill([]);
		$this->assertSame('', $field->toStoredValue());
	}

	public function testValidations()
	{
		$field = $this->field('layout', [
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
		$field = $this->field('layout', [
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
			'layout' => 'There\'s an error on the "Video-URL" field in block 2 using the "Video" block type in layout 1'
		], $field->errors());
	}

	public function testValidationsSettings()
	{
		$field = $this->field('layout', [
			'settings' => [
				'fields' => [
					'url' => [
						'type' => 'url'
					]
				]
			],
			'fieldsets' => [
				'heading' => true,
			],
			'value' => [
				[
					'attrs' => [
						'url' => 'Invalid URL',
					]
				]
			]
		]);

		$this->assertFalse($field->isValid());
		$this->assertSame(['layout' => 'There\'s an error in layout 1 settings'], $field->errors());
	}

	public function testEmpty()
	{
		$field = $this->field('layout', [
			'empty' => $value = 'Custom empty text'
		]);

		$this->assertSame($value, $field->empty());
	}

	public function testDefault()
	{
		$field = $this->field('layout', [
			'default' => [
				[
					'columns' => [
						[
							'width' => '1/2',
							'blocks' => [
								[
									'type' => 'heading',
									'text' => 'Some title'
								]
							]
						]
					]
				]
			]
		]);

		$default = $field->default();

		$layout = $default[0];
		$column = $layout['columns'][0];
		$block = $column['blocks'][0];

		$this->assertCount(1, $default);
		$this->assertArrayHasKey('id', $layout);
		$this->assertArrayHasKey('id', $column);
		$this->assertArrayHasKey('id', $block);
		$this->assertSame('heading', $block['type']);
		$this->assertSame('Some title', $block['text']);
	}

	public function testToValues()
	{
		$value = [
			[
				'columns' => [
					[
						'blocks' => [
							[
								'type'    => 'heading',
								'content' => [
									'text' => 'A nice block',
								]
							]
						]
					]
				]
			]
		];

		$field = $this->field('layout', [
			'value' => $value
		]);

		$form = $field->layoutsToValues($value);

		$this->assertArrayHasKey('id', $form[0]);
		$this->assertArrayHasKey('columns', $form[0]);
		$this->assertIsArray($form[0]['columns']);
		$this->assertArrayHasKey('id', $form[0]['columns'][0]);
		$this->assertArrayHasKey('blocks', $form[0]['columns'][0]);
		$this->assertArrayHasKey('id', $form[0]['columns'][0]['blocks'][0]);
	}

	public function testPaste()
	{
		$value = [
			[
				'columns' => [
					[
						'blocks' => [
							[
								'type'    => 'heading',
								'content' => [
									'text' => 'A nice block',
								]
							]
						]
					]
				]
			]
		];

		$field = $this->field('layout', [
			'value' => $value
		]);

		$original = $field->value();
		$layouts  = Layouts::factory($value);
		$pasted   = $field->pasteLayouts($layouts->toArray());

		// layout id
		$this->assertNotEmpty($pasted[0]['id']);
		$this->assertNotSame($original[0]['id'], $pasted[0]['id']);

		// layout column id
		$this->assertNotEmpty($pasted[0]['columns'][0]);
		$this->assertNotSame($original[0]['columns'][0]['id'], $pasted[0]['columns'][0]['id']);

		// block id
		$this->assertNotEmpty($pasted[0]['columns'][0]);
		$this->assertNotSame($original[0]['columns'][0]['blocks'][0]['id'], $pasted[0]['columns'][0]['blocks'][0]['id']);

		// block content
		$this->assertNotEmpty($pasted[0]['id']);
		$this->assertSame($original[0]['columns'][0]['blocks'][0]['content'], $pasted[0]['columns'][0]['blocks'][0]['content']);
	}

	public function testRoutePaste()
	{
		$value = [
			[
				'columns' => [
					[
						'blocks' => [
							[
								'type'    => 'heading',
								'content' => [
									'text' => 'A nice block',
								]
							]
						]
					]
				]
			]
		];

		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'json' => json_encode($value)
				]
			]
		]);

		$field = $this->field('layout');
		$route = $field->routes()[5];

		$response = $route['action']();

		$this->assertCount(1, $response);
		$this->assertArrayHasKey('id', $response[0]);
		$this->assertArrayHasKey('columns', $response[0]);
		$this->assertIsArray($response[0]['columns']);
		$this->assertArrayHasKey('id', $response[0]['columns'][0]);
		$this->assertArrayHasKey('blocks', $response[0]['columns'][0]);
		$this->assertArrayHasKey('id', $response[0]['columns'][0]['blocks'][0]);
	}
}
