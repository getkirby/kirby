<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class PageErrorsTest extends ModelTestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.PageErrors';

	public function testErrors(): void
	{
		$page = new Page(['slug' => 'test']);
		$this->assertSame([], $page->errors());
	}

	public function testErrorsWithInfoSection(): void
	{
		$page = new Page([
			'slug' => 'test',
			'blueprint' => [
				'name' => 'test',
				'sections' => [
					'info' => [
						'type'     => 'info',
						'headline' => 'Info',
						'text'     => 'info'
					]
				]
			]
		]);

		$this->assertSame([], $page->errors());
	}

	public function testErrorsWithInfoField(): void
	{
		$page = new Page([
			'slug' => 'test',
			'blueprint' => [
				'name'   => 'test',
				'fields' => [
					'info' => [
						'type' => 'info',
						'text' => 'info'
					]
				]
			]
		]);

		// fields without a value don't have errors
		$this->assertSame([], $page->errors());
	}

	public function testErrorsWithPagesSectionField(): void
	{
		$page = new Page([
			'slug' => 'test',
			'blueprint' => [
				'name' => 'test',
				'fields' => [
					'drafts' => [
						'type'    => 'section',
						'section' => 'pages',
						'status'  => 'drafts',
						'min'     => 1
					]
				]
			]
		]);

		$this->assertSame([
			'drafts' => [
				'label'   => 'Drafts',
				'message' => [
					'min' => 'The "Drafts" section requires at least one page'
				]
			]
		], $page->errors());
	}

	public function testErrorsWithPagesSectionFieldWhenInactive(): void
	{
		$page = new Page([
			'slug' => 'test',
			'blueprint' => [
				'name' => 'test',
				'fields' => [
					'toggle' => [
						'type'    => 'toggle',
						'default' => false
					],
					'drafts' => [
						'type'    => 'section',
						'section' => 'pages',
						'status'  => 'drafts',
						'min'     => 1,
						'when'    => ['toggle' => true]
					]
				]
			]
		]);

		// the section is hidden by the `when` condition
		// and must not block changing the page status
		$this->assertSame([], $page->errors());
	}

	public function testErrorsWithRequiredField(): void
	{
		$page = new Page([
			'slug' => 'test',
			'blueprint' => [
				'name' => 'test',
				'fields' => [
					'test' => [
						'required' => true,
						'type'     => 'text'
					]
				]
			]
		]);

		$this->assertSame([
			'test' => [
				'label'   => 'Test',
				'message' => [
					'required' => 'Please enter something'
				]
			]
		], $page->errors());
	}

	public function testErrorsWithRequiredFieldAndContent(): void
	{
		$page = new Page([
			'slug' => 'test',
			'content' => [
				'test' => 'test'
			],
			'blueprint' => [
				'name' => 'test',
				'fields' => [
					'test' => [
						'required' => true,
						'type'     => 'text'
					]
				]
			]
		]);

		$this->assertSame([], $page->errors());
	}
}
