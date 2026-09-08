<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(FilePermissions::class)]
class FilePermissionsTest extends ModelTestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.FilePermissions';

	protected function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'users' => [
				['id' => 'admin', 'role' => 'admin']
			]
		]);
	}

	public static function actionProvider(): array
	{
		return [
			['access'],
			['changeName'],
			// ['changeTemplate'], Tested separately because of the needed blueprints
			['create'],
			['delete'],
			['list'],
			['replace'],
			['sort'],
			['update']
		];
	}

	#[DataProvider('actionProvider')]
	public function testWithAdmin(string $action): void
	{
		$this->app->impersonate('kirby');

		$page = new Page([
			'slug' => 'test'
		]);

		$file  = new File(['filename' => 'test.jpg', 'parent' => $page]);
		$perms = $file->permissions();

		$this->assertTrue($perms->can($action));
	}

	#[DataProvider('actionProvider')]
	public function testWithNobody(string $action): void
	{
		$page  = new Page(['slug' => 'test']);
		$file  = new File(['filename' => 'test.jpg', 'parent' => $page]);
		$perms = $file->permissions();

		$this->assertFalse($perms->can($action));
	}

	public function testCannotChangeTemplate(): void
	{
		$this->app->impersonate('kirby');

		$page = new Page(['slug' => 'test']);
		$file  = new File(['filename' => 'test.jpg', 'parent' => $page]);

		$this->assertFalse($file->permissions()->can('changeTemplate'));
	}

	public function testCanChangeTemplate(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/test' => [
					'fields' => [
						'list-a' => [
							'type'     => 'filelist',
							'template' => 'for-list/a'
						],
						'list-b' => [
							'type'     => 'filelist',
							'template' => 'for-list/b'
						]
					]
				],
				'files/for-list/a' => [
					'title' => 'Type A'
				],
				'files/for-list/b' => [
					'title' => 'Type B'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$page = new Page(['slug' => 'test', 'template' => 'test']);
		$file = new File(['filename' => 'test.jpg', 'parent' => $page]);

		$this->assertTrue($file->permissions()->can('changeTemplate'));
	}

	public function testCanChangeTemplateWithAllAvailable(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/test' => [
					'fields' => [
						'files' => [
							'type' => 'filelist',
							// No template specified - should get all available
						]
					]
				],
				'files/image' => [
					'title' => 'Image'
				],
				'files/document' => [
					'title' => 'Document'
				],
				'files/video' => [
					'title' => 'Video'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$page = new Page(['slug' => 'test', 'template' => 'test']);
		$file = new File(['filename' => 'test.jpg', 'parent' => $page]);

		// Should be able to change template because multiple templates are available
		$this->assertTrue($file->permissions()->can('changeTemplate'));
	}
}
