<?php

namespace Kirby\Cms;

use Kirby\Cms\NewPage as Page;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Page::class)]
class NewPageChangeSortTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewPageChangeSortTest';

	public function site(): Site
	{
		return $this->app->site();
	}

	public static function sortProvider(): array
	{
		return [
			['a', 2, 'b,a,c,d'],
			['b', 4, 'a,c,d,b'],
			['d', 1, 'd,a,b,c'],
		];
	}

	#[DataProvider('sortProvider')]
	public function testChangeSort(
		string $id,
		int $position,
		string $expected
	): void {
		Page::create([
			'slug' => 'a',
			'num'  => 1,
		]);

		Page::create([
			'slug' => 'b',
			'num'  => 2,
		]);

		Page::create([
			'slug' => 'c',
			'num'  => 3,
		]);

		Page::create([
			'slug' => 'd',
			'num'  => 4,
		]);

		$site = $this->site();
		$site->find($id)->changeSort($position);

		$this->assertSame($expected, implode(',', $site->children()->keys()));
	}

	public function testChangeSortDateBased(): void
	{
		Page::create([
			'slug' => 'a',
			'num'  => 1,
		]);

		Page::create([
			'slug' => 'b',
			'num'  => 2,
		]);

		Page::create([
			'slug' => 'c',
			'num'  => 20180104,
			'blueprint' => [
				'title' => 'DateBased',
				'name'  => 'datebased',
				'num'   => 'date'
			],
			'content' => [
				'date' => '2018-01-04'
			]
		]);

		Page::create([
			'slug' => 'd',
			'num'  => 4,
		]);

		Page::create([
			'slug' => 'e',
			'num'  => 0,
			'blueprint' => [
				'title' => 'ZeroBased',
				'name'  => 'zerobased',
				'num'   => 'zero'
			],
		]);

		$site = $this->site();
		$site->find('b')->changeSort(3);

		$this->assertSame(1, $site->find('a')->num());
		$this->assertSame(2, $site->find('d')->num());
		$this->assertSame(3, $site->find('b')->num());

		$this->assertSame(20180104, $site->find('c')->num());
		$this->assertSame(0, $site->find('e')->num());
	}

	public function testMassSorting(): void
	{
		foreach ($chars = range('a', 'd') as $slug) {
			$page = Page::create([
				'slug' => $slug
			]);

			$page = $page->changeStatus('unlisted');

			$this->assertTrue($page->exists());
			$this->assertNull($page->num());
		}

		$this->assertSame(
			$chars,
			$this->site()->children()->keys()
		);

		foreach ($this->site()->children()->flip()->values() as $index => $page) {
			$page = $page->changeSort($index + 1);
		}

		$this->assertSame(
			array_reverse($chars),
			$this->site()->children()->keys()
		);

		$this->assertDirectoryExists(static::TMP . '/content/4_a');
		$this->assertDirectoryExists(static::TMP . '/content/3_b');
		$this->assertDirectoryExists(static::TMP . '/content/2_c');
		$this->assertDirectoryExists(static::TMP . '/content/1_d');
	}
}
