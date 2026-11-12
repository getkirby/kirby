<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Cms\FilePermissions
 */
class FilePermissionsTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.FilePermissions';

	protected $app;

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);
	}

	public function tearDown(): void
	{
		Blueprint::$loaded = [];
		Dir::remove(static::TMP);
	}

	public static function actionProvider(): array
	{
		return [
			['changeName'],
			['create'],
			['delete'],
			['replace'],
			['update']
		];
	}

	/**
	 * @covers \Kirby\Cms\ModelPermissions::can
	 * @dataProvider actionProvider
	 */
	public function testWithAdmin($action)
	{
		$this->app->impersonate('kirby');

		$page = new Page([
			'slug' => 'test'
		]);

		$file  = new File(['filename' => 'test.jpg', 'parent' => $page]);
		$perms = $file->permissions();

		$this->assertTrue($perms->can($action));
	}

	/**
	 * @covers \Kirby\Cms\ModelPermissions::can
	 * @dataProvider actionProvider
	 */
	public function testWithNobody($action)
	{
		$page  = new Page(['slug' => 'test']);
		$file  = new File(['filename' => 'test.jpg', 'parent' => $page]);
		$perms = $file->permissions();

		$this->assertFalse($perms->can($action));
	}

	/**
	 * @covers ::canChangeTemplate
	 */
	public function testCannotChangeTemplate()
	{
		$this->app->impersonate('kirby');

		$page = new Page(['slug' => 'test']);
		$file  = new File(['filename' => 'test.jpg', 'parent' => $page]);

		$this->assertFalse($file->permissions()->can('changeTemplate'));
	}

	/**
	 * @covers ::canChangeTemplate
	 */
	public function testCanChangeTemplate()
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
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

		$page  = new Page(['slug' => 'test', 'template' => 'test']);
		$file  = new File(['filename' => 'test.jpg', 'parent' => $page]);

		$this->assertTrue($file->permissions()->can('changeTemplate'));
	}

	/**
	 * @covers \Kirby\Cms\ModelPermissions::can
	 */
	public function testWithPathTraversalTemplate(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'roles' => [
				[
					'name'        => 'editor',
					'permissions' => [
						'files' => [
							'delete' => false,
						]
					]
				]
			],
			'users' => [
				['email' => 'editor@domain.com', 'role' => 'editor']
			]
		]);

		// a page blueprint that would allow the deletion
		// if it was loaded as the file blueprint
		F::write(static::TMP . '/site/blueprints/pages/public.yml', "options:\n  delete: true\n");
		F::write(static::TMP . '/site/blueprints/files/image.yml', "title: Image\n");

		$this->app->impersonate('editor@domain.com');

		// the template can already be stored in the content file
		$page = new Page(['slug' => 'test']);
		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $page,
			'content'  => ['template' => '../pages/public']
		]);

		$this->assertSame('files/default', $file->blueprint()->name());
		$this->assertFalse($file->permissions()->can('delete'));
	}
}
