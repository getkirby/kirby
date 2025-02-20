<?php

namespace Kirby\Cms;

use Kirby\Cms\NewPage as Page;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class NewPageErrorsTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewPageErrorsTest';

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
}
