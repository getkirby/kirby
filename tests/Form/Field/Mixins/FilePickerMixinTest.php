<?php

namespace Kirby\Form\Field;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use Kirby\Form\Field;

class FilePickerMixinTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Form.Fields.FilePickerMixin';

	public function setUp(): void
	{
		$kirby = new App([
			'roots' => [
				'index' => static::TMP
			]
		]);

		$kirby->impersonate('kirby');
	}

	public function tearDown(): void
	{
		App::destroy();
	}

	public function testPageFiles()
	{
		Field::$types = [
			'test' => [
				'mixins'  => ['filepicker'],
				'methods' => [
					'files' => fn () => $this->filepicker()['data']
				]
			]
		];

		$page = new Page([
			'slug' => 'test',
			'files' => [
				['filename' => 'a.jpg'],
				['filename' => 'b.jpg'],
				['filename' => 'c.jpg'],
			]
		]);

		$field = $this->field('test', [
			'model' => $page
		]);

		$files = $field->files();

		$this->assertCount(3, $files);
		$this->assertSame('a.jpg', $files[0]['id']);
		$this->assertSame('b.jpg', $files[1]['id']);
		$this->assertSame('c.jpg', $files[2]['id']);
	}

	public function testFileFiles()
	{
		Field::$types = [
			'test' => [
				'mixins'  => ['filepicker'],
				'methods' => [
					'files' => fn () => $this->filepicker()['data']
				]
			]
		];

		$page = new Page([
			'slug' => 'test',
			'files' => [
				['filename' => 'a.jpg'],
				['filename' => 'b.jpg'],
				['filename' => 'c.jpg'],
			]
		]);

		$field = $this->field('test', [
			'model' => $page->file('b.jpg')
		]);

		$files = $field->files();

		$this->assertCount(3, $files);
		$this->assertSame('test/a.jpg', $files[0]['id']);
		$this->assertSame('test/b.jpg', $files[1]['id']);
		$this->assertSame('test/c.jpg', $files[2]['id']);
	}

	public function testUserFiles()
	{
		Field::$types = [
			'test' => [
				'mixins'  => ['filepicker'],
				'methods' => [
					'files' => fn () => $this->filepicker()['data']
				]
			]
		];

		$user = new User([
			'email' => 'test@getkirby.com',
			'files' => [
				['filename' => 'a.jpg'],
				['filename' => 'b.jpg'],
				['filename' => 'c.jpg'],
			]
		]);

		$field = $this->field('test', [
			'model' => $user
		]);

		$files = $field->files();

		$this->assertCount(3, $files);
		$this->assertSame('a.jpg', $files[0]['id']);
		$this->assertSame('b.jpg', $files[1]['id']);
		$this->assertSame('c.jpg', $files[2]['id']);
	}

	public function testSiteFiles()
	{
		Field::$types = [
			'test' => [
				'mixins'  => ['filepicker'],
				'methods' => [
					'files' => fn () => $this->filepicker()['data']
				]
			]
		];

		$site = new Site([
			'files' => [
				['filename' => 'a.jpg'],
				['filename' => 'b.jpg'],
				['filename' => 'c.jpg'],
			]
		]);

		$field = $this->field('test', [
			'model' => $site
		]);

		$files = $field->files();

		$this->assertCount(3, $files);
		$this->assertSame('a.jpg', $files[0]['id']);
		$this->assertSame('b.jpg', $files[1]['id']);
		$this->assertSame('c.jpg', $files[2]['id']);
	}

	public function testCustomQuery()
	{
		Field::$types = [
			'test' => [
				'mixins'  => ['filepicker'],
				'props' => [
					'query' => fn (string|null $query = null) => $query
				],
				'methods' => [
					'files' => fn () => $this->filepicker([
						'query' => $this->query
					])['data']
				]
			]
		];

		$site = new Site([
			'files' => [
				['filename' => 'a.jpg'],
				['filename' => 'b.jpg'],
				['filename' => 'c.jpg'],
			],
			'children' => [
				['slug' => 'test']
			]
		]);

		$field = $this->field('test', [
			'model' => $site->find('test'),
			'query' => 'site.files'
		]);

		$files = $field->files();

		$this->assertCount(3, $files);
		$this->assertSame('a.jpg', $files[0]['id']);
		$this->assertSame('b.jpg', $files[1]['id']);
		$this->assertSame('c.jpg', $files[2]['id']);
	}

	public function testMap()
	{
		Field::$types = [
			'test' => [
				'mixins'  => ['filepicker'],
				'props' => [
					'query' => fn (string|null $query = null) => $query
				],
				'methods' => [
					'files' => fn () => $this->filepicker([
						'map' => fn ($file) => $file->id()
					])['data']
				]
			]
		];

		$page = new Page([
			'slug' => 'test',
			'files' => [
				['filename' => 'a.jpg'],
				['filename' => 'b.jpg'],
				['filename' => 'c.jpg'],
			],
		]);

		$field = $this->field('test', [
			'model' => $page,
		]);

		$files = $field->files();

		$expected = [
			'test/a.jpg',
			'test/b.jpg',
			'test/c.jpg'
		];

		$this->assertSame($expected, $files);
	}
}
