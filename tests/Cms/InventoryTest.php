<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\TestCase;
use Kirby\Toolkit\A;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Inventory::class)]
class InventoryTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.Inventory';

	protected function setUp(): void
	{
		static::setUpTmp();
	}

	protected function tearDown(): void
	{
		parent::tearDown();
		static::tearDownTmp();
		Page::$models = [];
	}

	protected function create(array $items): void
	{
		foreach ($items as $item) {
			$root = static::TMP . '/' . $item;

			// a trailing slash creates a directory with a dot in its name
			if (str_ends_with($item, '/') === false && F::extension($item)) {
				F::write($root, '');
			} else {
				Dir::make($root);
			}
		}
	}

	public function testChildren(): void
	{
		$this->create([
			'1_project-a',
			'cover.jpg',
			'project.txt'
		]);

		$inventory = new Inventory(static::TMP);

		$this->assertCount(1, $inventory->children());
		$this->assertSame('project-a', $inventory->children()[0]['slug']);
	}

	public function testFiles(): void
	{
		$this->create([
			'1_project-a',
			'cover.jpg',
			'cover.jpg.txt',
			'index.php',
			'project.txt'
		]);

		$inventory = new Inventory(static::TMP);

		$this->assertSame(['cover.jpg'], array_keys($inventory->files()));
	}

	public function testFor(): void
	{
		$this->create([
			'1_project-a',
			'2_project-b',
			'cover.jpg',
			'cover.jpg.txt',
			'projects.txt',
			'_ignore.txt',
			'.invisible'
		]);

		$inventory = Inventory::for(static::TMP);

		$this->assertSame('project-a', $inventory['children'][0]['slug']);
		$this->assertSame(1, $inventory['children'][0]['num']);

		$this->assertSame('project-b', $inventory['children'][1]['slug']);
		$this->assertSame(2, $inventory['children'][1]['num']);

		$this->assertSame('cover.jpg', $inventory['files']['cover.jpg']['filename']);
		$this->assertSame('jpg', $inventory['files']['cover.jpg']['extension']);
		$this->assertArrayNotHasKey('_ignore.txt', $inventory['files']);
		$this->assertArrayNotHasKey('.invisible', $inventory['files']);

		$this->assertSame('projects', $inventory['template']);
	}

	public function testForWithSkippedFiles(): void
	{
		$this->create([
			'valid.jpg',
			'skipped.html',
			'skipped.htm',
			'skipped.php'
		]);

		$inventory = Inventory::for(static::TMP);

		$this->assertSame(['valid.jpg'], A::pluck($inventory['files'], 'filename'));
	}

	public function testForChildSorting(): void
	{
		$this->create([
			'1_project-c',
			'10_project-b',
			'11_project-a',
		]);

		$inventory = Inventory::for(static::TMP);

		$this->assertSame('project-c', $inventory['children'][0]['slug']);
		$this->assertSame('project-b', $inventory['children'][1]['slug']);
		$this->assertSame('project-a', $inventory['children'][2]['slug']);
	}

	public function testForChildWithLeadingZero(): void
	{
		$this->create([
			'01_project-c',
			'02_project-b',
			'03_project-a',
		]);

		$inventory = Inventory::for(static::TMP);

		$this->assertSame('project-c', $inventory['children'][0]['slug']);
		$this->assertSame(1, $inventory['children'][0]['num']);

		$this->assertSame('project-b', $inventory['children'][1]['slug']);
		$this->assertSame(2, $inventory['children'][1]['num']);

		$this->assertSame('project-a', $inventory['children'][2]['slug']);
		$this->assertSame(3, $inventory['children'][2]['num']);
	}

	public function testForFileSorting(): void
	{
		$this->create([
			'1-c.jpg',
			'10-b.jpg',
			'11-a.jpg',
		]);

		$inventory = Inventory::for(static::TMP);
		$files     = array_values($inventory['files']);

		$this->assertSame('1-c.jpg', $files[0]['filename']);
		$this->assertSame('10-b.jpg', $files[1]['filename']);
		$this->assertSame('11-a.jpg', $files[2]['filename']);
	}

	public function testForMissingTemplate(): void
	{
		$this->create([
			'cover.jpg',
			'cover.jpg.txt'
		]);

		$inventory = Inventory::for(static::TMP);

		$this->assertSame('cover.jpg', $inventory['files']['cover.jpg']['filename']);
		$this->assertSame('default', $inventory['template']);
	}

	public function testForTemplateWithDotInFilename(): void
	{
		$this->create([
			'cover.jpg',
			'cover.jpg.txt',
			'article.video.txt'
		]);

		$inventory = Inventory::for(static::TMP);

		$this->assertSame('cover.jpg', $inventory['files']['cover.jpg']['filename']);
		$this->assertSame('article.video', $inventory['template']);
	}

	public function testForExtension(): void
	{
		$this->create([
			'cover.jpg',
			'cover.jpg.md',
			'article.md'
		]);

		$inventory = Inventory::for(static::TMP, 'md');

		$this->assertSame('cover.jpg', $inventory['files']['cover.jpg']['filename']);
		$this->assertSame('article', $inventory['template']);
	}

	public function testForIgnore(): void
	{
		$this->create([
			'cover.jpg',
			'article.txt'
		]);

		$inventory = Inventory::for(static::TMP, 'txt', ['cover.jpg']);

		$this->assertCount(0, $inventory['files']);
		$this->assertSame('article', $inventory['template']);
	}

	public function testForMultilang(): void
	{
		$this->create([
			'cover.jpg',
			'cover.jpg.en.txt',
			'article.en.txt',
			'article.de.txt'
		]);

		$inventory = Inventory::for(static::TMP, 'txt', null, true);

		$this->assertSame('cover.jpg', $inventory['files']['cover.jpg']['filename']);
		$this->assertSame('article', $inventory['template']);
	}

	public function testForMultilangWithoutChildren(): void
	{
		// the default language is only needed to match children
		new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);

		Page::$models = [
			'article' => 'A'
		];

		$this->create([
			'article.en.txt'
		]);

		$inventory = Inventory::for(static::TMP, 'txt', null, true);

		$this->assertSame([], $inventory['children']);
		$this->assertSame('article', $inventory['template']);
	}

	public function testForChildModels(): void
	{
		Page::$models = [
			'a' => 'A',
			'b' => 'A'
		];

		$this->create([
			'child-with-model-a/a.txt',
			'child-with-model-b/b.txt',
			'child-without-model-c/c.txt'
		]);

		$inventory = Inventory::for(static::TMP);

		$this->assertSame('a', $inventory['children'][0]['model']);
		$this->assertSame('b', $inventory['children'][1]['model']);
		$this->assertNull($inventory['children'][2]['model']);
	}

	public function testForChildModelsOfSiblings(): void
	{
		Page::$models = [
			'a' => 'A',
			'b' => 'A',
			'c' => 'A'
		];

		// siblings' models are tried first,
		// which must not leak into the next child
		$this->create([
			'1_child/c.txt',
			'2_child/c.txt',
			'3_child/a.txt',
			'4_child/d.txt',
			'5_child/b.txt',
			'6_child/c.txt',
		]);

		$inventory = Inventory::for(static::TMP);

		$this->assertSame(
			['c', 'c', 'a', null, 'b', 'c'],
			array_column($inventory['children'], 'model')
		);
	}

	public function testForNonExistingDir(): void
	{
		$inventory = Inventory::for('/does-not-exist-' . uniqid());

		$this->assertSame([
			'children' => [],
			'files'    => [],
			'template' => 'default',
		], $inventory);
	}

	public function testForChildWithoutNum(): void
	{
		$this->create([
			'about/about.txt',
		]);

		$inventory = Inventory::for(static::TMP);

		$this->assertSame('about', $inventory['children'][0]['dirname']);
		$this->assertSame('about', $inventory['children'][0]['slug']);
		$this->assertNull($inventory['children'][0]['num']);
	}

	public function testForTemplateLastWins(): void
	{
		// when multiple content files have no matching file in $files,
		// the last one wins (historical behaviour)
		$this->create([
			'article.txt',
			'note.txt',
		]);

		$inventory = Inventory::for(static::TMP);

		$this->assertSame('note', $inventory['template']);
	}

	public function testForChildMultilangModels(): void
	{
		new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'languages' => [
				[
					'code'    => 'en',
					'name'    => 'English',
					'default' => true
				],
				[
					'code'    => 'de',
					'name'    => 'Deutsch',
				]
			]
		]);

		Page::$models = [
			'a' => 'A',
			'b' => 'A'
		];

		$this->create([
			'child-with-model-a/a.de.txt',
			'child-with-model-a/a.en.txt',
			'child-with-model-b/b.de.txt',
			'child-with-model-b/b.en.txt',
			'child-without-model-c/c.de.txt',
			'child-without-model-c/c.en.txt'
		]);

		$inventory = Inventory::for(static::TMP, 'txt', null, true);

		$this->assertSame('a', $inventory['children'][0]['model']);
		$this->assertSame('b', $inventory['children'][1]['model']);
		$this->assertNull($inventory['children'][2]['model']);
	}

	public function testForDirectoryWithContentExtension(): void
	{
		// content files are never directories
		$this->create([
			'article.txt',
			'notes.txt/'
		]);

		$inventory = Inventory::for(static::TMP);

		$this->assertSame([], $inventory['children']);
		$this->assertSame('notes', $inventory['template']);
	}

	public static function templateProvider(): array
	{
		return [
			'single content file' => [
				['article.txt'],
				'article'
			],
			'meta file sorted after the content file' => [
				['article.txt', 'zebra.jpg', 'zebra.jpg.txt'],
				'article'
			],
			'meta file sorted before the content file' => [
				['aaa.jpg', 'aaa.jpg.txt', 'article.txt'],
				'article'
			],
			'only a meta file' => [
				['cover.jpg', 'cover.jpg.txt'],
				'default'
			],
			'dot in template name' => [
				['cover.jpg', 'cover.jpg.txt', 'article.video.txt'],
				'article.video'
			],
			'directory named like a content file' => [
				['article.txt', 'zzz', 'zzz.txt'],
				'zzz'
			],
			'directory named like a file' => [
				['article.txt', 'zzz.jpg/', 'zzz.jpg.txt'],
				'zzz.jpg'
			],
			'content file of a skipped file type' => [
				['article.txt', 'skipped.php', 'skipped.php.txt'],
				'skipped.php'
			],
			'ignored content files' => [
				['article.txt', '_hidden.txt', '.invisible.txt'],
				'article'
			],
			'last template wins' => [
				['article.txt', 'note.txt'],
				'note'
			],
			'natural sorting' => [
				['item10.txt', 'item9.txt'],
				'item10'
			],
			'no content file' => [
				['cover.jpg'],
				'default'
			],
			'empty directory' => [
				[],
				'default'
			],
		];
	}

	#[DataProvider('templateProvider')]
	public function testTemplate(array $items, string $expected): void
	{
		Dir::make(static::TMP);
		$this->create($items);

		$inventory = new Inventory(static::TMP);

		$this->assertSame($expected, $inventory->template());
	}

	public function testTemplateMultilang(): void
	{
		$this->create([
			'cover.jpg',
			'cover.jpg.en.txt',
			'article.en.txt',
			'article.de.txt'
		]);

		$inventory = new Inventory(static::TMP, 'txt', null, true);

		$this->assertSame('article', $inventory->template());
	}
}
