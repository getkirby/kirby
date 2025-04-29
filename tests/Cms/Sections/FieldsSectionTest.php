<?php

namespace Kirby\Cms;

class FieldsSectionTest extends TestCase
{
	public function testErrors(): void
	{
		$section = new Section('fields', [
			'name'   => 'test',
			'model'  => new Page(['slug' => 'test']),
			'fields' => [
				'text' => [
					'label' => 'Text',
					'type' => 'textarea',
					'required' => true
				]
			]
		]);

		$this->assertSame([
			'text' => [
				'label'   => 'Text',
				'message' => [
					'required' => 'Please enter something'
				]
			]
		], $section->errors());
	}

	public function testErrorsWithContent(): void
	{
		$section = new Section('fields', [
			'name'   => 'test',
			'model'  => new Page([
				'slug' => 'test',
				'content' => [
					'text' => 'test'
				]
			]),
			'fields' => [
				'text' => [
					'label' => 'Text',
					'type' => 'textarea',
					'required' => true
				]
			]
		]);

		$this->assertSame([], $section->errors());
	}
}
