<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
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

	public function testChangeNum()
	{
		$site = $this->site();

		$page = new Page([
			'slug' => 'test',
			'num'  => 1
		]);

		$page = $page->save();

		$this->assertSame(1, $page->num());
		$this->assertSame('1_test', $page->dirname());
		$this->assertSame(1, $page->parentModel()->find('test')->num());
		$this->assertSame(1, $site->find('test')->num());

		$page = $page->changeNum(2);

		$this->assertSame(2, $page->num());
		$this->assertSame('2_test', $page->dirname());
		$this->assertSame(2, $site->find('test')->num());
	}

	public function testChangeStatusFromDraftToListed()
	{
		$page = Page::create([
			'slug' => 'test',
		]);

		$this->assertTrue($page->isDraft());

		$listed = $page->changeStatus('listed');

		$this->assertSame('listed', $listed->status());
		$this->assertSame(1, $listed->num());
		$this->assertFalse($listed->parentModel()->drafts()->has($listed));
		$this->assertTrue($listed->parentModel()->children()->listed()->has($listed));
	}

	public function testChangeStatusFromDraftToUnlisted()
	{
		$page = Page::create([
			'slug' => 'test',
		]);

		$this->assertTrue($page->isDraft());

		$unlisted = $page->changeStatus('unlisted');

		$this->assertSame('unlisted', $unlisted->status());
		$this->assertNull($unlisted->num());
		$this->assertFalse($unlisted->parentModel()->drafts()->has($unlisted));
		$this->assertTrue($unlisted->parentModel()->children()->unlisted()->has($unlisted));
	}

	public function testChangeStatusFromListedToUnlisted()
	{
		$page = Page::create([
			'slug' => 'test',
		]);

		$listed = $page->changeStatus('listed');
		$this->assertTrue($listed->isListed());
		$this->assertSame(1, $listed->num());

		$this->assertFalse($listed->parentModel()->children()->unlisted()->has($listed));
		$this->assertTrue($listed->parentModel()->children()->listed()->has($listed));

		$unlisted = $listed->changeStatus('unlisted');

		$this->assertTrue($unlisted->isUnlisted());
		$this->assertNull($unlisted->num());

		$this->assertFalse($unlisted->parentModel()->children()->listed()->has($unlisted));
		$this->assertTrue($unlisted->parentModel()->children()->unlisted()->has($unlisted));
	}

	public function testChangeStatusFromUnlistedToListed()
	{
		$page = Page::create([
			'slug' => 'test',
		]);

		// change to unlisted
		$unlisted = $page->changeStatus('unlisted');

		$this->assertTrue($unlisted->isUnlisted());
		$this->assertNull($unlisted->num());

		$this->assertFalse($unlisted->parentModel()->children()->listed()->has($unlisted));
		$this->assertTrue($unlisted->parentModel()->children()->unlisted()->has($unlisted));

		// change to listed
		$listed = $unlisted->changeStatus('listed');
		$this->assertTrue($listed->isListed());
		$this->assertSame(1, $listed->num());

		$this->assertFalse($listed->parentModel()->children()->unlisted()->has($listed));
		$this->assertTrue($listed->parentModel()->children()->listed()->has($listed));
	}

	public function testChangeStatusFromListedToDraft()
	{
		$page = Page::create([
			'slug' => 'test',
		]);

		$page = $page->changeStatus('listed');

		$this->assertSame('listed', $page->status());
		$this->assertSame(1, $page->num());
		$this->assertFalse($page->isDraft());

		$draft = $page->changeStatus('draft');

		$this->assertTrue($draft->isDraft());
		$this->assertSame('draft', $draft->status());
		$this->assertNull($draft->num());
		$this->assertTrue($draft->parentModel()->drafts()->has($draft));
		$this->assertFalse($draft->parentModel()->children()->listed()->has($draft));
	}

	public function testChangeStatusToInvalidStatus()
	{
		$page = Page::create([
			'slug' => 'test',
			'blueprint' => [
				'title'  => 'Test',
				'name'   => 'test',
				'status' => [
					'draft'  => 'Draft',
					'listed' => 'Published'
				]
			]
		]);

		$this->assertSame('draft', $page->status());

		$draft = $page->changeStatus('listed');
		$this->assertSame('listed', $draft->status());

		$this->expectException(InvalidArgumentException::class);

		$unlisted = $page->changeStatus('unlisted');
		$this->assertSame('unlisted', $unlisted->status());
	}

	public function testCreateDefaultNum()
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'     => 'one-child',
						'children' => [
							[
								'slug' => 'child-a'
							]
						]
					],
					[
						'slug'     => 'three-children',
						'children' => [
							[
								'slug' => 'child-a',
								'num'  => 1
							],
							[
								'slug' => 'child-b',
								'num'  => 2
							],
							[
								'slug' => 'child-c'
							]
						],
						'drafts' => [
							[
								'slug' => 'draft'
							]
						]
					]
				]
			]
		]);

		// no siblings
		$page = $app->page('one-child/child-a');
		$this->assertSame(1, $page->createNum());

		// two listed siblings / no position
		$page = $app->page('three-children/child-c');
		$this->assertSame(3, $page->createNum());

		// one listed sibling / valid position
		$page = $app->page('three-children/child-a');
		$this->assertSame(2, $page->createNum(2));

		// one listed sibling / position too low
		$page = $app->page('three-children/child-a');
		$this->assertSame(1, $page->createNum(-1));

		// one listed sibling / position too high
		$page = $app->page('three-children/child-a');
		$this->assertSame(2, $page->createNum(3));

		// draft / no position
		$page = $app->page('three-children/draft');
		$this->assertSame(3, $page->createNum());

		// draft / given position
		$page = $app->page('three-children/draft');
		$this->assertSame(1, $page->createNum(1));
	}

	public function testCreateZeroBasedNum()
	{
		$page = new Page([
			'slug' => 'test',
			'blueprint' => [
				'num' => 'zero'
			]
		]);

		$this->assertSame(0, $page->createNum());

		$page = new Page([
			'slug' => 'test',
			'blueprint' => [
				'num' => 0
			]
		]);

		$this->assertSame(0, $page->createNum());
	}

	public function testCreateDateBasedNum()
	{
		// without date
		$page = new Page([
			'slug' => 'test',
			'blueprint' => [
				'num' => 'date'
			]
		]);

		$this->assertSame((int)date('Ymd'), $page->createNum());

		// with date field
		$page = new Page([
			'slug' => 'test',
			'blueprint' => [
				'num' => 'date',
			],
			'content' => [
				'date' => '2012-12-12'
			]
		]);

		$this->assertSame(20121212, $page->createNum());
	}

	public function testCreateDateBasedNumWithDateHandler()
	{
		new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'options' => [
				'date.handler' => 'strftime'
			]
		]);

		// without date
		$page = new Page([
			'slug' => 'test',
			'blueprint' => [
				'num' => 'date'
			]
		]);

		$this->assertSame((int)date('Ymd'), $page->createNum());

		// with date field
		$page = new Page([
			'slug' => 'test',
			'blueprint' => [
				'num' => 'date',
			],
			'content' => [
				'date' => '2012-12-12'
			]
		]);

		$this->assertSame(20121212, $page->createNum());
	}

	public function testCreateNumWithTranslations()
	{
		$app = new App([
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
					'name'    => 'Deutsch'
				]
			]
		]);

		$page = new Page([
			'slug' => 'test',
			'blueprint' => [
				'num' => 'date'
			],
			'translations' => [
				[
					'code' => 'en',
					'content' => [
						'date' => '2019-01-01',
					]
				],
				[
					'code' => 'de',
					'content' => [
						'date' => '2018-01-01',
					]
				]
			]
		]);

		$this->assertSame(20190101, $page->createNum());

		$app->setCurrentLanguage('de');
		$app->setCurrentTranslation('de');

		$this->assertSame(20190101, $page->createNum());
	}

	public function testCreateCustomNum()
	{
		// valid
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'blueprint' => [
							'num' => '{{ page.year }}'
						],
						'content' => [
							'year' => 2016
						]
					]
				]
			]
		]);

		$this->assertSame(2016, $app->page('test')->createNum());

		// invalid
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'blueprint' => [
							'num' => '{{ page.year }}'
						]
					]
				]
			]
		]);

		$this->assertSame(0, $app->page('test')->createNum());

		// multilang with default language fallback
		$app = new App([
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
					'name'    => 'Deutsch'
				]
			],
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'blueprint' => [
							'num' => '{{ page.year }}'
						],
						'translations' => [
							[
								'code' => 'en',
								'content' => [
									'year' => 2016
								]
							],
							[
								'code' => 'de',
								'content' => [
									'year' => 1999
								]
							]
						]
					]
				]
			]
		]);

		$this->assertSame(2016, $app->page('test')->createNum());
	}

	public function testPublish()
	{
		// main page
		$page = Page::create([
			'slug' => 'test',
		]);

		$site      = $this->app->site();
		$published = $page->publish();

		$this->assertSame('unlisted', $published->status());

		$this->assertFalse($page->parentModel()->drafts()->has($published));
		$this->assertTrue($page->parentModel()->children()->has($published));

		$this->assertFalse($site->drafts()->has($published));
		$this->assertTrue($site->children()->has($published));

		// child
		$child = Page::create([
			'parent' => $page,
			'slug'   => 'child'
		]);

		$published = $child->publish();

		$this->assertSame('unlisted', $published->status());

		$this->assertFalse($child->parentModel()->drafts()->has($published->id()));
		$this->assertTrue($child->parentModel()->children()->has($published->id()));

		$this->assertFalse($page->drafts()->has($published->id()));
		$this->assertTrue($page->children()->has($published->id()));
	}

	public function testPublishAlreadyPublished()
	{
		$page = Page::create([
			'slug' => 'test'
		]);

		$page = $page->publish();

		$this->assertSame('unlisted', $page->status());
		$this->assertSame('unlisted', $page->publish()->status());
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

	public function testUpdateWithDateBasedNumbering()
	{
		$page = Page::create([
			'slug' => 'test',
			'blueprint' => [
				'title' => 'Test',
				'name'  => 'test',
				'num'   => 'date'
			],
			'content' => [
				'date' => '2012-12-12'
			]
		]);

		// publish the new page
		$page = $page->changeStatus('listed');

		$this->assertSame(20121212, $page->num());

		$modified = $page->update([
			'date' => '2016-11-21'
		]);

		$this->assertSame(20161121, $modified->num());
	}

}
