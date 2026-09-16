<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\TestCase;
use Kirby\Toolkit\A;
use PHPUnit\Framework\Attributes\CoversClass;

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

			if (F::extension($item)) {
				F::write($root, '');
			} else {
				Dir::make($root);
			}
		}
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
}
