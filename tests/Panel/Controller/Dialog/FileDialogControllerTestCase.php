<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use Kirby\Panel\TestCase;

abstract class FileDialogControllerTestCase extends TestCase
{
	public const CONTROLLER = FileDialogController::class;

	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'files' => [
							[
								'filename' => 'a.jpg',
								'content'  => ['template' => 'a']
							],
							['filename' => 'b.jpg'],
							['filename' => 'c.jpg']
						]
					]
				],
				'files' => [
					[
						'filename' => 'a.jpg',
						'content'  => ['template' => 'a']
					],
					['filename' => 'b.jpg'],
					['filename' => 'c.jpg']
				]
			],
			'users' => [
				[
					'id'    => 'test',
					'files' => [
						[
							'filename' => 'a.jpg',
							'content'  => ['template' => 'a']
						],
						['filename' => 'b.jpg'],
						['filename' => 'c.jpg']
					]
				]
			]
		]);

		$this->app->impersonate('kirby');
	}

	protected function assertFactory(
		File $file,
		string $parent
	): void {
		$controller = static::CONTROLLER::factory($parent, $file->filename());
		$this->assertInstanceOf(static::CONTROLLER, $controller);
		$this->assertSame($file, $controller->file);
	}

	abstract protected function assertLoad(File $file): void;

	abstract protected function assertSubmit(
		File $file,
		Page|Site|User|null $parent = null,
		bool|string $redirect = false
	): void;

	public function testFactoryForPage(): void
	{
		$file = $this->app->page('test')->file('a.jpg');
		$this->assertFactory($file, 'pages/test');
	}

	public function testFactoryForSite(): void
	{
		$file = $this->app->site()->file('a.jpg');
		$this->assertFactory($file, 'site');
	}

	public function testFactoryForUser(): void
	{
		$file = $this->app->user('test')->file('a.jpg');
		$this->assertFactory($file, 'users/test');
	}

	public function testLoadForPage(): void
	{
		$file = $this->app->page('test')->file('a.jpg');
		$this->assertLoad($file);
	}

	public function testLoadForSite(): void
	{
		$file = $this->app->site()->file('a.jpg');
		$this->assertLoad($file);
	}

	public function testLoadForUser(): void
	{
		$file = $this->app->user('test')->file('a.jpg');
		$this->assertLoad($file);
	}

	public function testSubmitForPage(): void
	{
		$parent = $this->app->page('test');
		$file   = $parent->file('a.jpg');
		$this->assertSubmit($file, $parent);
	}

	public function testSubmitForSite(): void
	{
		$parent = $this->app->site();
		$file   = $parent->file('a.jpg');
		$this->assertSubmit($file, $parent);
	}

	public function testSubmitForUser(): void
	{
		$parent = $this->app->user('test');
		$file   = $parent->file('a.jpg');
		$this->assertSubmit($file, $parent);
	}
}
