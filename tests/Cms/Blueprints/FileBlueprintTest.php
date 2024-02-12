<?php

use Kirby\Cms\Blueprint;
use Kirby\Cms\File;
use Kirby\Cms\FileBlueprint;
use Kirby\Cms\Page;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Cms\FileBlueprint
 */
class FileBlueprintTest extends TestCase
{
	protected ?Page $parent;
	protected array $acceptCases;

	protected function setUp(): void
	{
		$this->parent = Page::factory([
			'slug' => 'test'
		]);

		$this->acceptCases = [
			'acceptWildcard' => [
				'accept' => 'image/*',
				'expected' => ['.jpg', '.jpeg', '.gif', '.png'],
				'notExpected' => ['.js', '.pdf', '.docx', '.zip']
			],
			'acceptMimeAsString' => [
				'accept' => 'image/jpeg, image/png',
				'expected' => ['.jpg', '.jpeg', '.png'],
				'notExpected' => ['.gif', '.js', '.pdf', '.docx', '.zip']
			],
			'acceptMimeAsProperty' => [
				'accept' => [
					'mime' => 'image/jpeg, image/png'
				],
				'expected' => ['.jpg', '.jpeg', '.png'],
				'notExpected' => ['.gif', '.js', '.pdf', '.docx', '.zip']
			],
			'acceptExtensions' => [
				'accept' => [
					'extension' => 'jpg, png'
				],
				'expected' => ['.jpg', '.png'],
				'notExpected' => ['.gif', '.jpeg', '.js', '.pdf', '.docx', '.zip']
			],
			'acceptExtensionsAndMime' => [
				'accept' => [
					'extension' => 'foo, bar', // when mime is present, extensions are ignored
					'mime' => 'image/jpeg, image/png'
				],
				'expected' => ['.jpg', '.jpeg', '.png'],
				'notExpected' => ['.gif', '.js', '.pdf', '.docx', '.zip', '.foo', '.bar']
			],
			'acceptType' => [
				'accept' => [
					'type' => 'image'
				],
				'expected' => ['.jpg', '.jpeg', '.gif', '.png'],
				'notExpected' => ['.js', '.pdf', '.docx', '.zip']
			],
			'acceptTypeAndMime' => [
				'accept' => [
					'type' => 'document', // when mime is present, type is ignored
					'mime' => 'image/jpeg, image/png'
				],
				'expected' => ['.jpg', '.jpeg', '.png'],
				'notExpected' => ['.gif', '.js', '.pdf', '.docx', '.zip']
			],
			'acceptInteresect' => [
				'accept' => [
					'type' => 'image',
					'extension' => 'jpg, png, foo, bar', // foo bar should be ignored
				],
				'expected' => ['.jpg', '.png'],
				'notExpected' => ['.gif', '.js', '.pdf', '.docx', '.zip', '.foo', '.bar']
			],
		];

		// set up the blueprint files
		foreach ($this->acceptCases as $name => $case) {
			Blueprint::$loaded['files/' . $name] = [
				'accept' => $case['accept']
			];
		}
	}

	protected function tearDown(): void
	{
		Blueprint::$loaded = [];
		$this->parent = null;
	}

	/**
	 * @covers ::acceptAttribute
	 */
	public function testAcceptAttribute()
	{
		foreach ($this->acceptCases as $name => $case) {
			$file = new File([
				'filename' => 'tmp',
				'parent'   => $this->parent,
				'template' => $name
			]);
			$acceptAttribute = $file->blueprint()->acceptAttribute();

			$expected = $case['expected'];
			$notExpected = $case['notExpected'];

			foreach ($expected as $extension) {
				$this->assertStringContainsString($extension, $acceptAttribute, "Case $name: $extension should be accepted");
			}

			foreach ($notExpected as $extension) {
				$this->assertStringNotContainsString($extension, $acceptAttribute, "Case $name: $extension should not be accepted");
			}
		}
	}
}
