<?php

namespace Kirby\Cms;

use Kirby\Blueprint\Blueprint;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Blueprint::class)]
#[CoversClass(FilePermissions::class)]
class FileListTemplateChangeTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.FileListTemplateChange';

	protected function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'blueprints' => [
				// User's files blueprints from the forum post
				'files/images' => [
					'title' => 'Images',
					'options' => [
						'changeName' => true,
						'changeTemplate' => true,
						'delete' => true,
						'read' => true,
						'replace' => true,
						'update' => true,
					],
					'accept' => [
						'extension' => 'jpg,jpeg,png'
					]
				],
				'files/documents' => [
					'title' => 'Documents',
					'options' => [
						'changeName' => true,
						'changeTemplate' => true,
						'delete' => true,
						'read' => true,
						'replace' => true,
						'update' => true,
					],
					'accept' => [
						'extension' => 'pdf,doc,docx'
					]
				],
				'files/videos' => [
					'title' => 'Videos',
					'options' => [
						'changeName' => true,
						'changeTemplate' => true,
						'delete' => true,
						'read' => true,
						'replace' => true,
						'update' => true,
					],
					'accept' => [
						'extension' => 'mp4,avi,mov'
					]
				],
				// User's page blueprint from the forum post
				'pages/default' => [
					'title' => 'Default',
					'fields' => [
						'sitemedia' => [
							'type' => 'filelist',
							'layout' => 'cards',
							'size' => 'medium',
							'info' => 'Template: {{ file.template }} <br /> {{ file.dimensions.width }} x {{ file.dimensions.height }} px <br /> {{ file.niceSize }}',
							'search' => true,
							'limit' => 60,
							'sortable' => true,
							// No template specified - this was the bug!
						]
					]
				],
			],
			'users' => [
				['id' => 'admin', 'role' => 'admin']
			]
		]);

	}

	protected function tearDown(): void
	{
		if (is_dir(static::TMP)) {
			rmdir(static::TMP);
		}
	}

	public function testFileListWithoutTemplateShowsAllAvailableTemplates(): void
	{
		$this->app->impersonate('admin');

		$model = new Page(['slug' => 'test']);

		$blueprint = new Blueprint([
			'model' => $model,
			'name'  => 'default',
			'fields' => [
				'sitemedia' => [
					'type' => 'filelist',
					'layout' => 'cards',
					'size' => 'medium',
					'info' => 'Template: {{ file.template }} <br /> {{ file.dimensions.width }} x {{ file.dimensions.height }} px <br /> {{ file.niceSize }}',
					'search' => true,
					'limit' => 60,
					'sortable' => true,
					// No template specified - this should now show all available templates
				]
			]
		]);

		$templates = $blueprint->acceptedFileTemplates();

		// Should include all available file templates + default
		$expected = ['default', 'images', 'documents', 'videos'];
		sort($expected);
		sort($templates);

		$this->assertSame($expected, $templates);
	}

	public function testFileCanChangeTemplateWithMultipleBlueprintsAvailable(): void
	{
		$this->app->impersonate('admin');

		$page = new Page(['slug' => 'test', 'template' => 'default']);
		$file = new File(['filename' => 'test.jpg', 'parent' => $page]);

		// This should return true because multiple templates are available
		$this->assertTrue($file->permissions()->can('changeTemplate'));

		// Verify we get templates that can actually handle jpg files
		$availableTemplates = $file->blueprints();
		$templateNames = array_map(fn ($bp) => $bp['name'], $availableTemplates);

		// Should include default and images (which accepts jpg), but not documents/videos
		$expectedNames = ['default', 'images'];
		sort($expectedNames);
		sort($templateNames);

		$this->assertSame($expectedNames, $templateNames);
	}
}
