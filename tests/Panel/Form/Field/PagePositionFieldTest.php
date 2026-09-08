<?php

namespace Kirby\Panel\Form\Field;

use Kirby\Form\Field\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PagePositionField::class)]
class PagePositionFieldTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'a', 'num' => 1],
					['slug' => 'b', 'num' => 2],
					['slug' => 'c', 'num' => 3]
				]
			]
		]);

		$this->app->impersonate('kirby');
	}

	public function testProps(): void
	{
		$field = new PagePositionField(
			page: $this->app->page('b')
		);

		$props = $field->props();

		ksort($props);

		$expected = [
			'autofocus'   => false,
			'disabled'    => false,
			'help'        => null,
			'hidden'      => false,
			'icon'        => null,
			'label'       => 'Please select a position',
			'name'        => 'position',
			'options'     => [
				[
					'value' => 1,
					'text'  => 1
				],
				[
					'value'    => 'a',
					'text'     => 'a',
					'disabled' => true
				],
				[
					'value' => 2,
					'text'  => 2
				],
				[
					'value'    => 'c',
					'text'     => 'c',
					'disabled' => true
				],
				[
					'value' => 3,
					'text'  => 3
				],
			],
			'placeholder' => '—',
			'required'    => false,
			'saveable'    => true,
			'translate'   => true,
			'type'        => 'select',
			'when'        => null,
			'width'       => '1/1',
		];

		$this->assertSame($expected, $props);
	}

	public function testLabel(): void
	{
		$field = new PagePositionField(
			page: $this->app->page('b'),
			label: 'Test'
		);

		$this->assertSame('Test', $field->label());
	}

	public function testOptionsWithProtectedSibling(): void
	{
		// the permission cache is keyed by template and role,
		// so both need to be unique for this test
		$uuid = uuid();

		$app = $this->app->clone([
			'blueprints' => [
				'pages/secret-' . $uuid => [
					'options' => ['list' => false]
				]
			],
			'roles' => [
				['name' => 'editor-' . $uuid]
			],
			'site' => [
				'children' => [
					['slug' => 'a', 'num' => 1],
					['slug' => 'b', 'num' => 2],
					['slug' => 'c', 'num' => 3, 'template' => 'secret-' . $uuid]
				]
			],
			'users' => [
				[
					'email' => 'editor@getkirby.com',
					'role'  => 'editor-' . $uuid
				]
			],
			'user' => 'editor@getkirby.com'
		]);

		$field = new PagePositionField(
			page: $app->page('b')
		);

		$options = $field->options();

		// the protected sibling keeps its slot, so that
		// the selectable positions are not shifted
		$this->assertCount(5, $options);

		$this->assertSame(1, $options[0]['value']);
		$this->assertSame('a', $options[1]['text']);
		$this->assertSame(2, $options[2]['value']);

		// ... but it must not disclose the page
		$this->assertSame('-2', $options[3]['value']);
		$this->assertSame('–', $options[3]['text']);
		$this->assertTrue($options[3]['disabled']);

		$this->assertSame(3, $options[4]['value']);
	}
}
