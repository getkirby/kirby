<?php

namespace Kirby\Form\Field;

use Kirby\Blueprint\Section;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SectionField::class)]
class SectionFieldTest extends TestCase
{
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
