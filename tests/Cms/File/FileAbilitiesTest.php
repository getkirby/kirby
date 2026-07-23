<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FileAbilities::class)]
class FileAbilitiesTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.FileAbilities';

	public function testChangeTemplateWithMultipleBlueprints(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/test' => [
					'sections' => [
						'section-a' => [
							'type'     => 'files',
							'template' => 'for-section/a'
						],
						'section-b' => [
							'type'     => 'files',
							'template' => 'for-section/b'
						]
					]
				],
				'files/for-section/a' => [
					'title' => 'Type A'
				],
				'files/for-section/b' => [
					'title' => 'Type B'
				]
			]
		]);

		$page = new Page([
			'slug'     => 'test',
			'template' => 'test'
		]);

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $page
		]);

		$abilities = new FileAbilities($file);

		$this->assertGreaterThan(1, count($file->blueprints()));
		$this->assertTrue($abilities->changeTemplate());
	}

	public function testChangeTemplateWithSingleBlueprint(): void
	{
		$page = new Page(['slug' => 'test']);

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $page
		]);

		$abilities = new FileAbilities($file);

		$this->assertCount(1, $file->blueprints());
		$this->assertFalse($abilities->changeTemplate());
	}

	public function testHasWithoutCheckMethod(): void
	{
		$page = new Page(['slug' => 'test']);

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $page
		]);

		$abilities = new FileAbilities($file);

		$this->assertFalse($abilities->has('delete'));
		$this->assertFalse($abilities->has('update'));
	}
}
