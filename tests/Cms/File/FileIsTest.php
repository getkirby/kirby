<?php

namespace Kirby\Cms;

use Kirby\Filesystem\F;
use Kirby\Filesystem\File as BaseFile;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class FileIsTest extends ModelTestCase
{
	public const string FIXTURES = __DIR__ . '/fixtures/files';
	public const string TMP      = KIRBY_TMP_DIR . '/Cms.FileIs';

	protected function setUp(): void
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

	/**
	 * Creates a page with a real file on disk,
	 * so that the file can be compared with an upload
	 */
	protected function pageWithFile(string $template): Page
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'  => 'test',
						'files' => [
							[
								'filename' => 'test.jpg',
								'content'  => ['template' => $template]
							]
						]
					]
				]
			]
		]);

		$page = $this->app->page('test');

		F::copy(static::FIXTURES . '/test.jpg', $page->root() . '/test.jpg');
		F::write($page->root() . '/test.jpg.txt', 'Template: ' . $template);

		return $page;
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

	public function testIsIdentical(): void
	{
		$page   = $this->pageWithFile(template: 'test');
		$upload = new BaseFile(static::FIXTURES . '/test.jpg');

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $page,
			'content'  => ['template' => 'test']
		]);

		$this->assertTrue($file->isIdentical($upload));
	}

	public function testIsIdenticalWithDifferentTemplate(): void
	{
		$page   = $this->pageWithFile(template: 'test');
		$upload = new BaseFile(static::FIXTURES . '/test.jpg');

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $page,
			'content'  => ['template' => 'cover']
		]);

		$this->assertFalse($file->isIdentical($upload));
	}

	public function testIsIdenticalWithMissingFile(): void
	{
		$upload = new BaseFile(static::FIXTURES . '/test.jpg');

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $this->app->site()
		]);

		$this->assertFalse($file->isIdentical($upload));
	}
}
