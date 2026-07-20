<?php

namespace Kirby\Form\Field;

use Kirby\Blueprint\Section;
use Kirby\Cms\Page;
use Kirby\Form\Fields;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SectionField::class)]
class SectionFieldTest extends TestCase
{
	public function testErrors(): void
	{
		$field = $this->field('section', [
			'name'    => 'drafts',
			'section' => 'pages',
			'status'  => 'drafts',
			'min'     => 1
		]);

		$this->assertSame([
			'min' => 'The "Drafts" section requires at least one page'
		], $field->errors());
	}

	public function testErrorsWhenInactive(): void
	{
		$fields = new Fields([
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
		], new Page(['slug' => 'test']));

		// the section is hidden by the `when` condition
		// and must not block saving or publishing
		$this->assertSame([], $fields->get('drafts')->errors());
		$this->assertSame([], $fields->errors());
	}

	public function testLabel(): void
	{
		$field = $this->field('section', [
			'name'    => 'drafts',
			'section' => 'pages',
			'status'  => 'drafts',
			'headline' => 'My drafts',
			'min'     => 1
		]);

		// the section headline is used as error label
		$this->assertSame('My drafts', $field->label());
	}

	public function testProps(): void
	{
		$field = $this->field('section', [
			'section' => 'pages'
		]);

		$props = $field->props();

		ksort($props);

		$expected = [
			'hidden'      => false,
			'name'        => 'section',
			'saveable'    => false,
			'sectionType' => 'pages',
			'type'        => 'section',
			'when'        => null,
			'width'       => '1/1'
		];

		$this->assertSame($expected, $props);
	}

	public function testSection(): void
	{
		$field = $this->field('section', [
			'name'    => 'drafts',
			'section' => 'pages'
		]);

		$section = $field->section();

		$this->assertInstanceOf(Section::class, $section);
		$this->assertSame('drafts', $section->name());
		$this->assertSame('pages', $section->type());
	}

	public function testSectionWithMissingSectionType(): void
	{
		$field = $this->field('section', [
			'name' => 'pages'
		]);

		// the field name is used as fallback for the section type
		$this->assertSame('pages', $field->props()['sectionType']);
		$this->assertSame('pages', $field->section()->type());
	}
}
