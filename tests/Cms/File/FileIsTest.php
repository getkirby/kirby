<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class FileIsTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.FileIs';

	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'users' => [
				[
					'email' => 'admin@getkirby.com',
					'role'  => 'admin'
				]
			],
			'user' => 'admin@getkirby.com'
		]);
	}

	public function testIsReadable(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'files/foo' => [
					'options' => ['read' => false]
				]
			]
		]);

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $this->app->site()
		]);
		$this->assertTrue($file->isReadable());

		$file = new File([
			'filename' => 'test.jpg',
			'template' => 'foo',
			'parent'   => $this->app->site()
		]);
		$this->assertFalse($file->isReadable());
	}

	public function testIsAccessible(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'files/bar' => [
					'options' => ['access' => false]
				]
			]
		]);

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $this->app->site()
		]);
		$this->assertTrue($file->isReadable());
		$this->assertTrue($file->isAccessible());
		$this->assertTrue($file->isListable());

		$file = new File([
			'filename' => 'test.jpg',
			'template' => 'bar',
			'parent'   => $this->app->site()
		]);
		$this->assertTrue($file->isReadable());
		$this->assertFalse($file->isAccessible());
		$this->assertFalse($file->isListable());
	}

	public function testIsAccessibleRead(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'files/bar-read' => [
					'options' => ['read' => false, 'access' => true]
				]
			]
		]);

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $this->app->site()
		]);
		$this->assertTrue($file->isReadable());
		$this->assertTrue($file->isAccessible());
		$this->assertTrue($file->isListable());

		$file = new File([
			'filename' => 'test.jpg',
			'template' => 'bar-read',
			'parent'   => $this->app->site()
		]);
		$this->assertFalse($file->isReadable());
		$this->assertFalse($file->isAccessible());
		$this->assertFalse($file->isListable());
	}

	public function testIsListable(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'files/baz' => [
					'options' => ['list' => false]
				]
			]
		]);

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $this->app->site()
		]);
		$this->assertTrue($file->isReadable());
		$this->assertTrue($file->isAccessible());
		$this->assertTrue($file->isListable());

		$file = new File([
			'filename' => 'test.jpg',
			'template' => 'baz',
			'parent'   => $this->app->site()
		]);
		$this->assertTrue($file->isReadable());
		$this->assertTrue($file->isAccessible());
		$this->assertFalse($file->isListable());
	}

	public function testIsListableRead(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'files/baz-read' => [
					'options' => ['read' => false, 'list' => true]
				]
			]
		]);

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $this->app->site()
		]);
		$this->assertTrue($file->isReadable());
		$this->assertTrue($file->isAccessible());
		$this->assertTrue($file->isListable());

		$file = new File([
			'filename' => 'test.jpg',
			'template' => 'baz-read',
			'parent'   => $this->app->site()
		]);
		$this->assertFalse($file->isReadable());
		$this->assertFalse($file->isAccessible());
		$this->assertFalse($file->isListable());
	}
}
