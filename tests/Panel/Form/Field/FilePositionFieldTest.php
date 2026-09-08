<?php

namespace Kirby\Panel\Form\Field;

use Kirby\Form\Field\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FilePositionField::class)]
class FilePositionFieldTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'site' => [
				'files' => [
					['filename' => 'a.jpg'],
					['filename' => 'b.jpg'],
					['filename' => 'c.jpg']
				]
			]
		]);

		$this->app->impersonate('kirby');
	}

	public function testProps(): void
	{
		$file = $this->app->site()->file('b.jpg');

		$field = new FilePositionField(
			file: $file
		);

		$props = $field->props();

		ksort($props);

		$expected = [
			'autofocus'   => false,
			'disabled'    => false,
			'help'        => null,
			'hidden'      => false,
			'icon'        => null,
			'label'       => 'Change position',
			'name'        => 'position',
			'options'     => [
				[
					'value' => 1,
					'text'  => 1
				],
				[
					'value'    => 'a.jpg',
					'text'     => 'a.jpg',
					'disabled' => true
				],
				[
					'value' => 2,
					'text'  => 2
				],
				[
					'value'    => 'c.jpg',
					'text'     => 'c.jpg',
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
		$field = new FilePositionField(
			file: $this->app->file('b.jpg'),
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
				'files/secret-' . $uuid => [
					'options' => ['list' => false]
				]
			],
			'roles' => [
				['name' => 'editor-' . $uuid]
			],
			'site' => [
				'files' => [
					['filename' => 'a.jpg'],
					['filename' => 'b.jpg'],
					['filename' => 'c.jpg', 'template' => 'secret-' . $uuid]
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

		$field = new FilePositionField(
			file: $app->site()->file('b.jpg')
		);

		$options = $field->options();

		// the protected sibling keeps its slot, so that
		// the selectable positions are not shifted
		$this->assertCount(5, $options);

		$this->assertSame(1, $options[0]['value']);
		$this->assertSame('a.jpg', $options[1]['text']);
		$this->assertSame(2, $options[2]['value']);

		// ... but it must not disclose the file
		$this->assertSame('-2', $options[3]['value']);
		$this->assertSame('–', $options[3]['text']);
		$this->assertTrue($options[3]['disabled']);

		$this->assertSame(3, $options[4]['value']);
	}
}
