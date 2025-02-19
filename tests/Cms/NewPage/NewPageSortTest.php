<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Page::class)]
class NewPageSortTest extends NewPageTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewPageSortTest';

	public function site()
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

	/**
	 * @dataProvider sortProvider
	 */
	public function testSort($id, $position, $expected)
	{
		$site = new Site([
			'children' => [
				[
					'slug' => 'a',
					'num'  => 1,
				],
				[
					'slug' => 'b',
					'num'  => 2,
				],
				[
					'slug' => 'c',
					'num'  => 3,
				],
				[
					'slug' => 'd',
					'num'  => 4,
				]
			]
		]);

		$page = $site->find($id);
		$page = $page->changeSort($position);

		$this->assertSame($expected, implode(',', $site->children()->keys()));
	}

	public function testSortDateBased()
	{
		$site = new Site([
			'children' => [
				[
					'slug' => 'a',
					'num'  => 1,
				],
				[
					'slug' => 'b',
					'num'  => 2,
				],
				[
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
				],
				[
					'slug' => 'd',
					'num'  => 4,
				],
				[
					'slug' => 'e',
					'num'  => 0,
					'blueprint' => [
						'title' => 'ZeroBased',
						'name'  => 'zerobased',
						'num'   => 'zero'
					],
				],
			]
		]);

		$page = $site->find('b');
		$page = $page->changeSort(3);

		$this->assertSame(1, $site->find('a')->num());
		$this->assertSame(2, $site->find('d')->num());
		$this->assertSame(3, $site->find('b')->num());

		$this->assertSame(20180104, $site->find('c')->num());
		$this->assertSame(0, $site->find('e')->num());
	}

	public function testMassSorting()
	{
		foreach ($chars = range('a', 'd') as $slug) {
			$page = Page::create([
				'slug' => $slug
			]);

			$page = $page->changeStatus('unlisted');

			$this->assertTrue($page->exists());
			$this->assertNull($page->num());
		}

		$this->assertSame($chars, $this->site()->children()->keys());

		foreach ($this->site()->children()->flip()->values() as $index => $page) {
			$page = $page->changeSort($index + 1);
		}

		$this->assertSame(array_reverse($chars), $this->site()->children()->keys());

		$this->assertDirectoryExists(static::TMP . '/content/4_a');
		$this->assertDirectoryExists(static::TMP . '/content/3_b');
		$this->assertDirectoryExists(static::TMP . '/content/2_c');
		$this->assertDirectoryExists(static::TMP . '/content/1_d');
	}

}
