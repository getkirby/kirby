<?php

namespace Kirby\Panel;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Panel\Areas\AreaTestCase;

/**
 * @coversDefaultClass \Kirby\Panel\PageCreateDialog
 */
class PageCreateDialogTest extends AreaTestCase
{
	public function setUp(): void
	{
		parent::setUp();
		$this->install();
		$this->login();
	}

	/**
	 * @covers ::coreFields
	 */
	public function testCoreFields(): void
	{
		$dialog = new PageCreateDialog(
			null,
			null,
			'test',
			null
		);

		$fields = $dialog->coreFields();

		$this->assertSame('Title', $fields['title']['label']);
		$this->assertSame('/', $fields['slug']['path']);
	}

	/**
	 * @covers ::validate
	 */
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

	/**
	 * @covers ::validate
	 */
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

	/**
	 * @covers ::validate
	 */
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

	/**
	 * @covers ::validate
	 */
	public function testValidateValidFields(): void
	{
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

		$valid = $dialog->validate([
			'slug'    => 'foo',
			'content' => ['title' => 'Foo', 'foo' => 'bar']
		], 'listed');

		$this->assertTrue($valid);
	}
}
