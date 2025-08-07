<?php

namespace Kirby\Cms;

use Kirby\Exception\LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(FilePermissions::class)]
class FilePermissionsTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.FilePermissions';

	public function setUp(): void
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

	public function testCanFromCache(): void
	{
		$this->app->impersonate('admin');

		$page = new Page(['slug' => 'test']);
		$file = new File([
			'filename'  => 'test.jpg',
			'parent'    => $page,
			'template'  => 'some-template',
			'blueprint' => [
				'name' => 'files/some-template',
				'options' => [
					'access' => false,
					'list'   => false
				]
			]
		]);

		$this->assertFalse(FilePermissions::canFromCache($file, 'access'));
		$this->assertFalse(FilePermissions::canFromCache($file, 'access'));
		$this->assertFalse(FilePermissions::canFromCache($file, 'list'));
		$this->assertFalse(FilePermissions::canFromCache($file, 'list'));
	}

	public function testCanFromCacheDynamic(): void
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Cannot use permission cache for dynamically-determined permission');

		$page = new Page(['slug' => 'test']);
		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $page,
			'template' => 'some-template',
		]);

		FilePermissions::canFromCache($file, 'changeTemplate');
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
					'sections' => [
						'section-a' => [
							'type' => 'files',
							'template' => 'for-section/a'
						],
						'section-b' => [
							'type' => 'files',
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

		$this->app->impersonate('kirby');

		$page = new Page(['slug' => 'test', 'template' => 'test']);
		$file = new File(['filename' => 'test.jpg', 'parent' => $page]);

		$this->assertTrue($file->permissions()->can('changeTemplate'));
	}
}
