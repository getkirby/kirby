<?php

namespace Kirby\Panel;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Panel\Areas\AreaTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PageCreateDialog::class)]
class PageCreateDialogTest extends AreaTestCase
{
	public function setUp(): void
	{
		parent::setUp();
		$this->install();
		$this->login();
	}

	public function testCoreFields(): void
	{
		$dialog = new PageCreateDialog(
			null,
			null,
			'test',
			null
		);

		$fields = $dialog->coreFields();

		$this->assertCount(6, $fields);
		$this->assertSame('Title', $fields['title']['label']);
		$this->assertSame('/', $fields['slug']['path']);
	}

	public function testCoreFieldWithoutTitleSlug(): void
	{
		$this->app([
			'blueprints' => [
				'pages/test' => [
					'create' => [
						'title' => 'A simple title',
						'slug'  => 'a-simple-slug'
					]
				]
			]
		]);

		$dialog = new PageCreateDialog(
			null,
			null,
			'test',
			null
		);

		$fields = $dialog->coreFields();

		$this->assertArrayNotHasKey('title', $fields);
		$this->assertArrayNotHasKey('slug', $fields);
	}

	public function testCoreFieldInvalidTitleSlug(): void
	{
		$this->app([
			'blueprints' => [
				'pages/test' => [
					'create' => [
						'title' => false
					]
				]
			]
		]);

		$dialog = new PageCreateDialog(
			null,
			null,
			'test',
			null
		);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Page create dialog: title and slug must not be false');

		$dialog->coreFields();
	}

	public function testResolveFieldTemplates(): void
	{
		$this->app([
			'blueprints' => [
				'pages/test' => [
					'create' => [
						'title' => 'This is a {{ page.foo }}',
						'slug'  => 'page-{{ page.bar }}'
					]
				]
			]
		]);

		$dialog = new PageCreateDialog(
			null,
			null,
			'test',
			null
		);

		$input = $dialog->resolveFieldTemplates([
			'foo' => 'Foo',
			'bar' => 'foo',
		]);

		$this->assertSame([
			'foo'   => 'Foo',
			'bar'   => 'foo',
			'title' => 'This is a Foo',
			'slug'  => 'page-foo',
		], $input);
	}

	public function testSanitize(): void
	{
		$this->app([
			'blueprints' => [
				'pages/test' => [
					'create' => [
						'fields' => ['foo', 'bar']
					],
					'fields' => [
						'foo' => [
							'type'     => 'text',
							'required' => true
						],
						'bar' => [
							'type'     => 'text',
							'required' => true,
							'default'  => 'bar'
						]
					]
				]
			]
		]);

		$dialog = new PageCreateDialog(
			null,
			null,
			'test',
			null
		);

		$input = $dialog->sanitize([
			'slug'  => 'foo',
			'title' => 'Foo',
			'foo'   => 'bar'
		]);

		$this->assertSame([
			'content'  => [
				'foo'   => 'bar',
				'bar'   => 'bar',
				'title' => 'Foo',
			],
			'slug'     => 'foo',
			'template' => 'test',
		], $input);
	}

	public function testValidateInvalidTitle(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.changeTitle.empty');

		$dialog = new PageCreateDialog(
			null,
			null,
			'test',
			null
		);

		$dialog->validate(['content' => ['title' => '']]);
	}

	public function testValidateInvalidSlug(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.slug.invalid');

		$dialog = new PageCreateDialog(
			null,
			null,
			'test',
			null
		);

		$dialog->validate([
			'slug'    => '',
			'content' => ['title' => 'Foo']
		]);
	}

	public function testValidateInvalidFields(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.changeStatus.incomplete');

		$this->app([
			'blueprints' => [
				'pages/test' => [
					'fields' => [
						'foo' => [
							'type' => 'text',
							'required' => true
						]
					]
				]
			]
		]);


		$dialog = new PageCreateDialog(
			null,
			null,
			'test',
			null
		);

		$dialog->validate([
			'slug'    => 'foo',
			'content' => ['title' => 'Foo']
		], 'listed');
	}

	public function testValidateValidFields(): void
	{
		$this->app([
			'blueprints' => [
				'pages/test' => [
					'fields' => [
						'foo' => [
							'type'     => 'text',
							'required' => true
						]
					]
				]
			]
		]);

		$dialog = new PageCreateDialog(
			null,
			null,
			'test',
			null
		);

		$valid = $dialog->validate([
			'slug'    => 'foo',
			'content' => ['title' => 'Foo', 'foo' => 'bar']
		], 'listed');

		$this->assertTrue($valid);
	}

	public function testValue(): void
	{
		$this->app([
			'blueprints' => [
				'pages/test' => [
					'create' => [
						'fields' => ['foo']
					],
					'fields' => [
						'foo' => [
							'type'     => 'text',
							'required' => true,
							'default'  => 'bar'
						]
					]
				]
			]
		]);

		$dialog = new PageCreateDialog(
			null,
			null,
			'test',
			null
		);

		$value = $dialog->value();

		$this->assertSame([
			'parent'   => 'site',
			'section'  => null,
			'slug'     => '',
			'template' => 'test',
			'title'    => '',
			'view'     => null,
			'foo'      => 'bar'
		], $value);
	}
}
