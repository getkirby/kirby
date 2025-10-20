<?php

namespace Kirby\Cms;

use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Pages::class)]
class PagesTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.Pages';

	public function pages()
	{
		return new Pages([
			new Page(['slug' => 'a', 'num' => 1]),
			new Page(['slug' => 'b', 'num' => 2]),
			new Page(['slug' => 'c'])
		]);
	}

	public function testAddPage(): void
	{
		$pages = Pages::factory([
			['slug' => 'a']
		]);

		$page = new Page([
			'slug' => 'b'
		]);

		$result = $pages->add($page);

		$this->assertCount(2, $result);
		$this->assertSame('a', $result->nth(0)->slug());
		$this->assertSame('b', $result->nth(1)->slug());
	}

	public function testAddCollection(): void
	{
		$a = Pages::factory([
			['slug' => 'a']
		]);

		$b = Pages::factory([
			['slug' => 'b'],
			['slug' => 'c']
		]);

		$c = $a->add($b);

		$this->assertCount(3, $c);
		$this->assertSame('a', $c->nth(0)->slug());
		$this->assertSame('b', $c->nth(1)->slug());
		$this->assertSame('c', $c->nth(2)->slug());
	}

	public function testAddById(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'children' => [
							['slug' => 'aa']
						]
					],
					[
						'slug' => 'b',
					]
				]
			]
		]);

		$pages = $app->site()->children()->add('a/aa');

		$this->assertCount(3, $pages);
		$this->assertSame('a', $pages->nth(0)->id());
		$this->assertSame('b', $pages->nth(1)->id());
		$this->assertSame('a/aa', $pages->nth(2)->id());
	}

	public function testAddNull(): void
	{
		$pages = new Pages();
		$this->assertCount(0, $pages);

		$pages->add(null);

		$this->assertCount(0, $pages);
	}

	public function testAddFalse(): void
	{
		$pages = new Pages();
		$this->assertCount(0, $pages);

		$pages->add(false);

		$this->assertCount(0, $pages);
	}

	public function testAddInvalidObject(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('You must pass a Pages or Page object or an ID of an existing page to the Pages collection');

		$site  = new Site();
		$pages = new Pages();
		$pages->add($site);
	}

	public function testAudio(): void
	{
		$pages = Pages::factory([
			[
				'slug' => 'a',
				'files' => [
					['filename' => 'a.mp3'],
					['filename' => 'a.pdf']
				]
			],
			[
				'slug' => 'b',
				'files' => [
					['filename' => 'b.mp3']
				]
			],
		]);

		$this->assertSame(['a.mp3', 'b.mp3'], $pages->audio()->pluck('filename'));
	}

	public function testCode(): void
	{
		$pages = Pages::factory([
			[
				'slug' => 'a',
				'files' => [
					['filename' => 'a.js'],
					['filename' => 'a.pdf']
				]
			],
			[
				'slug' => 'b',
				'files' => [
					['filename' => 'b.js']
				]
			],
		]);

		$this->assertSame(['a.js', 'b.js'], $pages->code()->pluck('filename'));
	}

	public function testConstructWithCollection(): void
	{
		$pages = new Pages($this->pages()->not('a'));

		$this->assertCount(2, $pages);
	}

	public function testChildren(): void
	{
		$pages = Pages::factory([
			[
				'slug' => 'a',
				'children' => [
					['slug' => 'aa'],
					['slug' => 'ab']
				]
			],
			[
				'slug' => 'b',
				'children' => [
					['slug' => 'ba'],
					['slug' => 'bb']
				]
			]
		]);

		$expected = [
			'a/aa',
			'a/ab',
			'b/ba',
			'b/bb',
		];

		$this->assertSame($expected, $pages->children()->keys());
	}

	public function testDelete(): void
	{
		$this->app->impersonate('kirby');

		$a = Page::create([
			'slug'  => 'a',
			'draft' => false
		]);

		$b = Page::create([
			'slug'  => 'b',
			'draft' => false
		]);

		$c = Page::create([
			'slug'  => 'c',
			'draft' => false
		]);

		$pages = $this->app->site()->children();

		$this->assertCount(3, $pages);

		$this->assertDirectoryExists($a->root());
		$this->assertDirectoryExists($b->root());
		$this->assertDirectoryExists($c->root());

		$pages->delete([
			'a',
			'b',
		]);

		$this->assertCount(1, $pages);

		$this->assertDirectoryDoesNotExist($a->root());
		$this->assertDirectoryDoesNotExist($b->root());
		$this->assertDirectoryExists($c->root());
	}

	public function testDeleteSortedAndFiltered(): void
	{
		$this->app->impersonate('kirby');

		$a = Page::create([
			'slug'  => 'a',
			'draft' => false
		]);

		$a = $a->changeNum(1);

		$b = Page::create([
			'slug'  => 'b',
			'draft' => false
		]);

		$b = $b->changeNum(2);

		$c = Page::create([
			'slug'  => 'c',
			'draft' => false
		]);

		$c = $c->changeNum(3);

		$d = Page::create([
			'slug'  => 'd',
			'draft' => false
		]);

		$d = $d->changeNum(4);

		$pages = $this->app->site()->children();

		$this->assertCount(4, $pages);

		$this->assertDirectoryExists($a->root());
		$this->assertDirectoryExists($b->root());
		$this->assertDirectoryExists($c->root());
		$this->assertDirectoryExists($d->root());

		// Files should not interfer with deleting the pages
		F::write($a->root() . '/test.md', '');
		F::write($b->root() . '/test.md', '');

		$filtered = $pages->filter('slug', 'in', ['a', 'b', 'c']);

		$this->assertCount(3, $filtered);

		$filtered->delete([
			'a',
			'b',
		]);

		$this->assertCount(1, $filtered);

		// removed
		$this->assertDirectoryDoesNotExist($a->root());
		$this->assertDirectoryDoesNotExist($b->root());

		// Still existing. We need to fetch those fresh from the
		// Pages collection because they have been resorted in the
		// meantime and the root has changed.
		$this->assertDirectoryExists($pages->get('c')->root());
		$this->assertDirectoryExists($pages->get('d')->root());
	}

	public function testDeleteWithInvalidIds(): void
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					['slug' => 'a'],
					['slug' => 'b']
				]
			]
		]);

		$app->impersonate('kirby');

		$pages = $app->site()->children();

		$this->assertCount(2, $pages);

		$a = $pages->get('a')->root();
		$b = $pages->get('b')->root();

		// pretend the files exist
		Dir::make($a);
		Dir::make($b);

		$this->assertDirectoryExists($a);
		$this->assertDirectoryExists($b);

		try {
			$pages->delete([
				'a',
				'c',
			]);
		} catch (Exception $e) {
			$this->assertSame('Not all pages could be deleted. Try each remaining page individually to see the specific error that prevents deletion.', $e->getMessage());
		}

		$this->assertCount(1, $pages);
		$this->assertSame('b', $pages->first()->slug());

		$this->assertDirectoryDoesNotExist($a);
		$this->assertDirectoryExists($b);
	}

	public function testDocuments(): void
	{
		$pages = Pages::factory([
			[
				'slug' => 'a',
				'files' => [
					['filename' => 'a.pdf'],
					['filename' => 'a.js']
				]
			],
			[
				'slug' => 'b',
				'files' => [
					['filename' => 'b.pdf']
				]
			],
		]);

		$this->assertSame(['a.pdf', 'b.pdf'], $pages->documents()->pluck('filename'));
	}

	public function testDrafts(): void
	{
		$pages = Pages::factory([
			[
				'slug' => 'a',
				'drafts' => [
					['slug' => 'aa'],
					['slug' => 'ab']
				]
			],
			[
				'slug' => 'b',
				'drafts' => [
					['slug' => 'ba'],
					['slug' => 'bb']
				]
			]
		]);

		$expected = [
			'a/aa',
			'a/ab',
			'b/ba',
			'b/bb',
		];

		$this->assertSame($expected, $pages->drafts()->keys());
	}

	public function testFiles(): void
	{
		$pages = Pages::factory([
			[
				'slug' => 'a',
				'files' => [
					['filename' => 'a.jpg']
				]
			],
			[
				'slug' => 'b',
				'files' => [
					['filename' => 'b.pdf']
				]
			],
		]);

		$this->assertSame(['a.jpg', 'b.pdf'], $pages->files()->pluck('filename'));
	}

	public function testFind(): void
	{
		$this->assertIsPage('a', $this->pages()->find('a'));
		$this->assertIsPage('b', $this->pages()->find('b'));
		$this->assertIsPage('c', $this->pages()->find('c'));
	}

	public function testFindWithExtension(): void
	{
		$this->assertIsPage('a', $this->pages()->find('a.xml'));
		$this->assertIsPage('b', $this->pages()->find('b.json'));
	}

	public function testFindByUuid(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'a', 'content' => ['uuid' => 'test-a']],
					['slug' => 'b', 'content' => ['uuid' => 'test-b']],
					[
						'slug' => 'c',
						'content' => ['uuid' => 'test-c'],
						'children' => [
							['slug' => 'd', 'content' => ['uuid' => 'test-d']]
						]
					]
				]
			]
		]);

		$pages = $app->site()->children();
		$this->assertIsPage('a', $pages->find('page://test-a'));
		$this->assertIsPage('b', $pages->find('page://test-b'));
		$this->assertIsPage('c', $pages->find('page://test-c'));

		$this->assertIsPage('a', $app->page('page://test-a'));
		$this->assertIsPage('b', $app->page('page://test-b'));
		$this->assertIsPage('c', $app->page('page://test-c'));
		$this->assertIsPage('c/d', $app->page('page://test-d'));
	}

	public function testFindChildren(): void
	{
		$site = new Site([
			'children' => [
				[
					'slug' => 'grandma',
					'children' => [
						[
							'slug' => 'mother',
							'children' => [
								[
									'slug' => 'child',
								]
							]
						]
					]
				]
			]
		]);

		$this->assertIsPage('grandma', $site->children()->find('grandma'));
		$this->assertIsPage('grandma', $site->children()->find('grandma/'));
		$this->assertIsPage('grandma', $site->children()->find('grandma.json'));
		$this->assertIsPage('grandma/mother', $site->children()->find('grandma/mother'));
		$this->assertIsPage('grandma/mother', $site->children()->find('grandma/mother/'));
		$this->assertIsPage('grandma/mother', $site->children()->find('grandma/mother.json'));
		$this->assertIsPage('grandma/mother', $site->children()->find('grandma')->children()->find('mother'));
		$this->assertIsPage('grandma/mother', $site->children()->find('grandma')->children()->find('grandma/mother'));
		$this->assertNull($site->children()->find('mother'));
		$this->assertIsPage('grandma/mother/child', $site->children()->find('grandma/mother/child'));
		$this->assertIsPage('grandma/mother/child', $site->children()->find('grandma/mother/child/'));
		$this->assertIsPage('grandma/mother/child', $site->children()->find('grandma/mother/child.json'));
		$this->assertIsPage('grandma/mother/child', $site->children()->find('grandma/mother')->children()->find('child'));
		$this->assertIsPage('grandma/mother/child', $site->children()->find('grandma/mother')->children()->find('grandma/mother/child'));
		$this->assertNull($site->children()->find('child'));

		$this->assertIsPage('grandma/mother/child', $site->find('grandma')->grandChildren()->find('grandma/mother/child'));
		$this->assertIsPage('grandma/mother/child', $site->find('grandma')->grandChildren()->find('grandma/mother/child/'));
		$this->assertIsPage('grandma/mother/child', $site->find('grandma')->grandChildren()->find('grandma/mother/child.json'));
		$this->assertNull($site->find('grandma')->grandChildren()->find('grandma/mother'));
		$this->assertNull($site->find('grandma')->grandChildren()->find('mother'));
		$this->assertNull($site->find('grandma')->grandChildren()->find('mother/child'));
		$this->assertNull($site->find('grandma')->grandChildren()->find('child'));

		$pages = new Pages($site->children()->find('grandma', 'grandma/mother', 'grandma/mother/child'));
		$this->assertIsPage('grandma', $pages->find('grandma'));
		$this->assertIsPage('grandma/mother', $pages->find('grandma/mother'));
		$this->assertNull($pages->find('mother'));
		$this->assertIsPage('grandma/mother/child', $pages->find('grandma/mother/child'));
		$this->assertNull($pages->find('child'));
		$this->assertNull($pages->find(null));
	}

	public function testFindChildrenTranslated(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'languages' => [
				[
					'code' => 'en',
					'default' => true,
				],
				[
					'code' => 'de',
				],
			],
			'site' => [
				'children' => [
					[
						'slug' => 'grandma',
						'translations' => [
							[
								'code' => 'en',
							],
							[
								'code' => 'de',
								'slug' => 'oma',
							],
						],
						'children' => [
							[
								'slug' => 'mother',
								'translations' => [
									[
										'code' => 'en',
									],
									[
										'code' => 'de',
										'slug' => 'mutter'
									],
								],
								'children' => [
									[
										'slug' => 'child',
										'translations' => [
											[
												'code' => 'en',
											],
											[
												'code' => 'de',
												'slug' => 'kind',
											],
										],
									]
								]
							]
						]
					]
				]
			]
		]);

		$site = $app->site();

		$this->assertIsPage('grandma', $site->children()->find('grandma'));
		$this->assertIsPage('grandma', $site->children()->find('grandma/'));
		$this->assertIsPage('grandma', $site->children()->find('grandma.json'));
		$this->assertIsPage('grandma/mother', $site->children()->find('grandma/mother'));
		$this->assertIsPage('grandma/mother', $site->children()->find('grandma/mother/'));
		$this->assertIsPage('grandma/mother', $site->children()->find('grandma/mother.json'));
		$this->assertIsPage('grandma/mother', $site->children()->find('grandma')->children()->find('mother'));
		$this->assertIsPage('grandma/mother', $site->children()->find('grandma')->children()->find('grandma/mother'));
		$this->assertNull($site->children()->find('mother'));
		$this->assertIsPage('grandma/mother/child', $site->children()->find('grandma/mother/child'));
		$this->assertIsPage('grandma/mother/child', $site->children()->find('grandma/mother/child/'));
		$this->assertIsPage('grandma/mother/child', $site->children()->find('grandma/mother/child.json'));
		$this->assertIsPage('grandma/mother/child', $site->children()->find('grandma/mother')->children()->find('child'));
		$this->assertIsPage('grandma/mother/child', $site->children()->find('grandma/mother')->children()->find('grandma/mother/child'));
		$this->assertIsPage('grandma/mother/child', $site->children()->find('grandma')->children()->find('mother')->children()->find('child'));
		$this->assertNull($site->children()->find('child'));

		$this->assertIsPage('grandma/mother/child', $site->find('grandma')->grandChildren()->find('grandma/mother/child'));
		$this->assertIsPage('grandma/mother/child', $site->find('grandma')->grandChildren()->find('grandma/mother/child/'));
		$this->assertIsPage('grandma/mother/child', $site->find('grandma')->grandChildren()->find('grandma/mother/child.json'));
		$this->assertNull($site->find('grandma')->grandChildren()->find('grandma/mother'));
		$this->assertNull($site->find('grandma')->grandChildren()->find('mother'));
		$this->assertNull($site->find('grandma')->grandChildren()->find('mother/child'));
		$this->assertNull($site->find('grandma')->grandChildren()->find('child'));

		$pages = new Pages($site->children()->find('grandma', 'grandma/mother', 'grandma/mother/child'));
		$this->assertIsPage('grandma', $pages->find('grandma'));
		$this->assertIsPage('grandma/mother', $pages->find('grandma/mother'));
		$this->assertNull($pages->find('mother'));
		$this->assertIsPage('grandma/mother/child', $pages->find('grandma/mother/child'));
		$this->assertNull($pages->find('child'));

		$app->setCurrentLanguage('de');

		$this->assertIsPage('grandma', $site->children()->find('oma'));
		$this->assertIsPage('grandma', $site->children()->find('oma/'));
		$this->assertIsPage('grandma', $site->children()->find('oma.json'));
		$this->assertIsPage('grandma/mother', $site->children()->find('oma/mutter/'));
		$this->assertIsPage('grandma/mother', $site->children()->find('oma/mutter.json'));
		$this->assertIsPage('grandma/mother', $site->children()->find('oma')->children()->find('mutter'));
		$this->assertIsPage('grandma/mother', $site->children()->find('oma')->children()->find('mother'));
		$this->assertIsPage('grandma/mother', $site->children()->find('oma')->children()->find('grandma/mother'));
		$this->assertIsPage('grandma/mother/child', $site->children()->find('oma/mutter/kind'));
		$this->assertIsPage('grandma/mother/child', $site->children()->find('oma/mutter/kind/'));
		$this->assertIsPage('grandma/mother/child', $site->children()->find('oma/mutter/kind.json'));
		$this->assertIsPage('grandma/mother/child', $site->children()->find('oma/mutter')->children()->find('kind'));
		$this->assertIsPage('grandma/mother/child', $site->children()->find('oma/mutter')->children()->find('child'));
		$this->assertIsPage('grandma/mother/child', $site->children()->find('oma/mutter')->children()->find('grandma/mother/child'));
		$this->assertIsPage('grandma', $site->children()->find('grandma'));
		$this->assertIsPage('grandma/mother', $site->children()->find('grandma/mother'));
		$this->assertIsPage('grandma/mother', $site->children()->find('grandma/mutter'));
		$this->assertIsPage('grandma/mother/child', $site->children()->find('grandma/mother/child'));
		$this->assertIsPage('grandma/mother/child', $site->children()->find('grandma/mother/kind'));
		$this->assertIsPage('grandma', $site->children()->find('grandma'));
		$this->assertIsPage('grandma/mother/child', $site->children()->find('grandma')->children()->find('mother')->children()->find('child'));
		$this->assertIsPage('grandma/mother/child', $site->children()->find('oma')->children()->find('mutter')->children()->find('kind'));
		$this->assertNull($site->children()->find('child'));
		$this->assertNull($site->children()->find('kind'));
		$this->assertNull($site->children()->find('oma/mother'));
		$this->assertNull($site->children()->find('oma/mother/kind'));
		$this->assertNull($site->children()->find('oma/mutter/child'));
		$this->assertNull($site->children()->find('grandmother/mutter/child'));
		$this->assertNull($site->children()->find('grandmother/mutter/kind'));

		$this->assertIsPage('grandma/mother/child', $site->find('grandma')->grandChildren()->find('oma/mutter/kind'));
		$this->assertIsPage('grandma/mother/child', $site->find('grandma')->grandChildren()->find('oma/mutter/kind/'));
		$this->assertIsPage('grandma/mother/child', $site->find('grandma')->grandChildren()->find('oma/mutter/kind.json'));
		$this->assertIsPage('grandma/mother/child', $site->find('grandma')->grandChildren()->find('grandma/mother/child'));
		$this->assertIsPage('grandma/mother/child', $site->find('grandma')->grandChildren()->find('grandma/mother/child.json'));
		$this->assertNull($site->find('grandma')->grandChildren()->find('grandma/mutter/child'));
		$this->assertNull($site->find('grandma')->grandChildren()->find('oma/mutter'));
		$this->assertNull($site->find('grandma')->grandChildren()->find('grandma/mother'));
		$this->assertNull($site->find('grandma')->grandChildren()->find('mutter'));
		$this->assertNull($site->find('grandma')->grandChildren()->find('mother'));
		$this->assertNull($site->find('grandma')->grandChildren()->find('mutter/kind'));
		$this->assertNull($site->find('grandma')->grandChildren()->find('mother/child'));
		$this->assertNull($site->find('grandma')->grandChildren()->find('kind'));
		$this->assertNull($site->find('grandma')->grandChildren()->find('child'));

		$pages = new Pages($site->children()->find('grandma', 'grandma/mother', 'grandma/mother/child'));
		$this->assertIsPage('grandma', $pages->find('grandma'));
		$this->assertIsPage('grandma', $pages->find('oma'));
		$this->assertIsPage('grandma/mother', $pages->find('grandma/mother'));
		$this->assertIsPage('grandma/mother', $pages->find('grandma/mutter'));
		$this->assertIsPage('grandma/mother', $pages->find('oma/mutter'));
		$this->assertNull($pages->find('mother'));
		$this->assertNull($pages->find('mutter'));
		$this->assertIsPage('grandma/mother/child', $pages->find('grandma/mother/child'));
		$this->assertIsPage('grandma/mother/child', $pages->find('grandma/mother/kind'));
		$this->assertIsPage('grandma/mother/child', $pages->find('grandma/mutter/kind'));
		$this->assertIsPage('grandma/mother/child', $pages->find('oma/mutter/kind'));
		$this->assertNull($pages->find('oma/mother/kind'));
		$this->assertNull($pages->find('child'));
		$this->assertNull($pages->find('kind'));
	}

	public function testFindChildrenWithSwappedSlugsTranslated(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'languages' => [
				[
					'code' => 'en',
					'default' => true,
				],
				[
					'code' => 'de',
				],
			],
			'site' => [
				'children' => [
					[
						'slug' => 'aaa',
						'translations' => [
							[
								'code' => 'en',
							],
							[
								'code' => 'de',
								'slug' => 'zzz',
							],
						],
						'children' => [
							[
								'slug' => 'bbb',
								'translations' => [
									[
										'code' => 'en',
									],
									[
										'code' => 'de',
										'slug' => 'yyy'
									],
								],
							],
						],
					],
					[
						'slug' => 'zzz',
						'translations' => [
							[
								'code' => 'en',
							],
							[
								'code' => 'de',
								'slug' => 'aaa',
							],
						],
						'children' => [
							[
								'slug' => 'yyy',
								'translations' => [
									[
										'code' => 'en',
									],
									[
										'code' => 'de',
										'slug' => 'bbb'
									],
								],
							],
						],
					],
				],
			],
		]);

		$site = $app->site();

		$this->assertIsPage('aaa', $site->children()->find('aaa'));
		$this->assertIsPage('aaa/bbb', $site->children()->find('aaa/bbb'));
		$this->assertIsPage('aaa/bbb', $site->children()->find('aaa')->children()->find('bbb'));
		$this->assertIsPage('zzz', $site->children()->find('zzz'));
		$this->assertIsPage('zzz/yyy', $site->children()->find('zzz/yyy'));
		$this->assertIsPage('zzz/yyy', $site->children()->find('zzz')->children()->find('yyy'));

		$pages = new Pages($site->children()->find('aaa', 'aaa/bbb', 'zzz', 'zzz/yyy'));
		$this->assertIsPage('aaa', $pages->find('aaa'));
		$this->assertIsPage('aaa/bbb', $pages->find('aaa/bbb'));
		$this->assertIsPage('zzz', $pages->find('zzz'));
		$this->assertIsPage('zzz/yyy', $pages->find('zzz/yyy'));

		$pages = new Pages($site->children()->find('aaa', 'aaa/bbb', 'zzz'));
		$this->assertIsPage('aaa', $pages->find('aaa'));
		$this->assertIsPage('aaa/bbb', $pages->find('aaa/bbb'));
		$this->assertIsPage('zzz', $pages->find('zzz'));
		$this->assertIsPage('zzz/yyy', $pages->find('zzz/yyy'));

		$app->setCurrentLanguage('de');

		$this->assertIsPage('aaa', $site->children()->find('aaa'));
		$this->assertIsPage('aaa/bbb', $site->children()->find('aaa/bbb'));
		$this->assertIsPage('aaa/bbb', $site->children()->find('aaa')->children()->find('bbb'));
		$this->assertIsPage('zzz', $site->children()->find('zzz'));
		$this->assertIsPage('zzz/yyy', $site->children()->find('zzz/yyy'));
		$this->assertIsPage('zzz/yyy', $site->children()->find('zzz')->children()->find('yyy'));

		$pages = new Pages($site->children()->find('aaa', 'aaa/bbb', 'zzz', 'zzz/yyy'));
		$this->assertIsPage('aaa', $pages->find('aaa'));
		$this->assertIsPage('aaa/bbb', $pages->find('aaa/bbb'));
		$this->assertIsPage('zzz', $pages->find('zzz'));
		$this->assertIsPage('zzz/yyy', $pages->find('zzz/yyy'));

		$pages = new Pages($site->children()->find('aaa', 'aaa/bbb', 'zzz'));
		$this->assertIsPage('aaa', $pages->find('aaa'));
		$this->assertIsPage('aaa/bbb', $pages->find('aaa/bbb'));
		$this->assertIsPage('zzz', $pages->find('zzz'));
		$this->assertIsPage('zzz/yyy', $pages->find('zzz/yyy'));
	}

	public function testFindMultiple(): void
	{
		$pages = Pages::factory([
			[
				'slug' => 'page',
				'children' => [
					['slug' => 'a'],
					['slug' => 'b'],
					['slug' => 'c']
				]
			]
		]);

		$collection = $pages->find('page')->children()->find('a', 'c');
		$page       = $pages->find('page')->children()->last();

		$this->assertTrue($collection->has($page));
	}

	public function testImages(): void
	{
		$pages = Pages::factory([
			[
				'slug' => 'a',
				'files' => [
					['filename' => 'a.jpg'],
					['filename' => 'a.pdf']
				]
			],
			[
				'slug' => 'b',
				'files' => [
					['filename' => 'b.png']
				]
			],
		]);

		$this->assertSame(['a.jpg', 'b.png'], $pages->images()->pluck('filename'));
	}

	public function testIndex(): void
	{
		$pages = Pages::factory([
			[
				'slug' => 'a',
				'children' => [
					[
						'slug' => 'aa',
						'children' => [
							['slug' => 'aaa'],
							['slug' => 'aab'],
						]
					],
					['slug' => 'ab']
				]
			],
			[
				'slug' => 'b',
				'children' => [
					['slug' => 'ba'],
					['slug' => 'bb']
				]
			]
		]);

		$expected = [
			'a',
			'a/aa',
			'a/aa/aaa',
			'a/aa/aab',
			'a/ab',
			'b',
			'b/ba',
			'b/bb',
		];

		$this->assertSame($expected, $pages->index()->keys());
	}

	public function testIndexWithDrafts(): void
	{
		$pages = Pages::factory([
			[
				'slug' => 'a',
				'children' => [
					[
						'slug' => 'aa',
						'children' => [
							['slug' => 'aaa'],
							['slug' => 'aab'],
						]
					],
					[
						'slug' => 'ab'
					]
				],
				'drafts' => [
					[
						'slug' => 'ac'
					]
				]
			],
			[
				'slug' => 'b',
				'children' => [
					['slug' => 'ba'],
					['slug' => 'bb']
				]
			]
		]);

		$expected = [
			'a',
			'a/aa',
			'a/aa/aaa',
			'a/aa/aab',
			'a/ab',
			'a/ac',
			'b',
			'b/ba',
			'b/bb',
		];

		$this->assertSame($expected, $pages->index(true)->keys());
	}

	public function testIndexCacheMode(): void
	{
		$pages = Pages::factory([
			[
				'slug' => 'a',
				'children' => [
					[
						'slug' => 'aa',
						'children' => [
							['slug' => 'aaa'],
							['slug' => 'aab'],
						]
					],
					[
						'slug' => 'ab'
					]
				],
				'drafts' => [
					[
						'slug' => 'ac'
					]
				]
			],
			[
				'slug' => 'b',
				'children' => [
					['slug' => 'ba'],
					['slug' => 'bb']
				],
				'drafts' => [
					[
						'slug' => 'bc'
					]
				]
			]
		]);

		$expectedIndex = [
			'a',
			'a/aa',
			'a/aa/aaa',
			'a/aa/aab',
			'a/ab',
			'b',
			'b/ba',
			'b/bb',
		];

		$expectedIndexWithDrafts = [
			'a',
			'a/aa',
			'a/aa/aaa',
			'a/aa/aab',
			'a/ab',
			'a/ac',
			'b',
			'b/ba',
			'b/bb',
			'b/bc',
		];

		// first run index method to cache index and with drafts
		$pages->index();
		$pages->index(true);

		$this->assertSame($expectedIndex, $pages->index()->keys());
		$this->assertSame($expectedIndexWithDrafts, $pages->index(true)->keys());
	}

	public function testNotTemplate(): void
	{
		$pages = Pages::factory([
			[
				'slug'     => 'a',
				'template' => 'a'
			],
			[
				'slug'     => 'b',
				'template' => 'b'
			],
			[
				'slug'     => 'c',
				'template' => 'c'
			],
			[
				'slug'     => 'd',
				'template' => 'a'
			],
		]);

		$this->assertSame(['a', 'b', 'c', 'd'], $pages->notTemplate(null)->pluck('slug'));
		$this->assertSame(['b', 'c'], $pages->notTemplate('a')->pluck('slug'));
		$this->assertSame(['c'], $pages->notTemplate(['a', 'b'])->pluck('slug'));
		$this->assertSame(['a', 'b', 'c', 'd'], $pages->notTemplate(['z'])->pluck('slug'));
		$this->assertSame([], $pages->notTemplate(['a', 'b', 'c'])->pluck('slug'));
	}

	public function testNums(): void
	{
		$pages = Pages::factory([
			[
				'slug' => 'a',
				'num'  => 1
			],
			[
				'slug' => 'b',
				'num'  => 2
			],
		]);

		$this->assertSame([1, 2], $pages->nums());
	}

	public function testListed(): void
	{
		$this->assertCount(2, $this->pages()->listed());
	}

	public function testUnlisted(): void
	{
		$this->assertCount(1, $this->pages()->unlisted());
	}

	public function testPublished(): void
	{
		$this->assertCount(3, $this->pages()->published());
	}

	public function testSearch(): void
	{
		$pages = Pages::factory([
			[
				'slug'    => 'mtb',
				'content' => [
					'title' => 'Mountainbike'
				]
			],
			[
				'slug'    => 'mountains',
				'content' => [
					'title' => 'Mountains'
				]
			],
			[
				'slug'    => 'lakes',
				'content' => [
					'title' => 'Lakes'
				]
			]
		]);

		$result = $pages->search('mountain');
		$this->assertCount(2, $result);

		$result = $pages->search('mountain', 'title|text');
		$this->assertCount(2, $result);

		$result = $pages->search('mountain', 'text');
		$this->assertCount(0, $result);
	}

	public function testSearchWords(): void
	{
		$pages = Pages::factory([
			[
				'slug'    => 'mtb',
				'content' => [
					'title' => 'Mountainbike'
				]
			],
			[
				'slug'    => 'mountain',
				'content' => [
					'title' => 'Mountain'
				]
			],
			[
				'slug'    => 'everest-mountain',
				'content' => [
					'title' => 'Everest Mountain'
				]
			],
			[
				'slug'    => 'mount',
				'content' => [
					'title' => 'Mount'
				]
			],
			[
				'slug'    => 'lakes',
				'content' => [
					'title' => 'Lakes'
				]
			]
		]);

		$result = $pages->search('mountain', ['words' => true]);
		$this->assertCount(2, $result);

		$result = $pages->search('mount', ['words' => false]);
		$this->assertCount(4, $result);
	}

	public function testCustomMethods(): void
	{
		Pages::$methods = [
			'test' => function () {
				$slugs = '';
				foreach ($this as $page) {
					$slugs .= $page->slug();
				}
				return $slugs;
			}
		];

		$pages = Pages::factory([
			[
				'slug' => 'page',
				'children' => [
					['slug' => 'a'],
					['slug' => 'b']
				]
			]
		]);

		$pages = $pages->find('page')->children();
		$this->assertSame('ab', $pages->test());

		Pages::$methods = [];
	}

	public function testTemplate(): void
	{
		$pages = Pages::factory([
			[
				'slug'     => 'a',
				'template' => 'a'
			],
			[
				'slug'     => 'b',
				'template' => 'b'
			],
			[
				'slug'     => 'c',
				'template' => 'a'
			],
		]);

		$this->assertSame(['a', 'b', 'c'], $pages->template(null)->pluck('slug'));
		$this->assertSame(['a', 'c'], $pages->template('a')->pluck('slug'));
		$this->assertSame(['a', 'b', 'c'], $pages->template(['a', 'b'])->pluck('slug'));
	}

	public function testVideos(): void
	{
		$pages = Pages::factory([
			[
				'slug' => 'a',
				'files' => [
					['filename' => 'a.mov'],
					['filename' => 'a.pdf']
				]
			],
			[
				'slug' => 'b',
				'files' => [
					['filename' => 'b.mp4']
				]
			],
		]);

		$this->assertSame(['a.mov', 'b.mp4'], $pages->videos()->pluck('filename'));
	}

	public function testFactoryIsDraftProp(): void
	{
		$pages = Pages::factory([
			[
				'slug'    => 'a',
				'isDraft' => true,
			],
			[
				'slug'    => 'b',
				'isDraft' => false,
			],
			[
				'slug'    => 'c',
			]
		]);

		$this->assertSame([true, false, false], $pages->pluck('isDraft'));
	}

	public function testFactoryDraftParameter(): void
	{
		$pages = Pages::factory([
			[
				'slug'    => 'a',
				'isDraft' => true,
			],
			[
				'slug'    => 'b',
				'isDraft' => false,
			],
			[
				'slug'    => 'c',
			],
		], null, true);

		$this->assertSame([true, true, true], $pages->pluck('isDraft'));
	}

	public function testIsReadable(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'blueprints' => [
				'pages/readable-bar' => [
					'options' => ['read' => true]
				],
				'pages/readable-baz' => [
					'options' => ['read' => false]
				]
			],
			'site' => [
				'children' => [
					[
						'slug' => 'foo'
					],
					[
						'slug' => 'bar',
						'template' => 'readable-bar'
					],
					[
						'slug' => 'baz',
						'template' => 'readable-baz'
					]
				]
			],
			'users' => [
				['id' => 'bastian', 'role' => 'admin']
			]
		]);

		$app->impersonate('bastian');

		$page = $app->page('foo');
		$this->assertTrue($page->isReadable());

		$page = $app->page('bar');
		$this->assertTrue($page->isReadable());

		$page = $app->page('baz');
		$this->assertFalse($page->isReadable());
	}

	public function testIsListable(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'blueprints' => [
				'pages/visible-foo' => [
					'options' => ['list' => true]
				],
				'pages/visible-bar' => [
					'options' => ['list' => false]
				],
				'pages/visible-baz' => [
					'options' => ['read' => false]
				]
			],
			'site' => [
				'children' => [
					[
						'slug' => 'default'
					],
					[
						'slug' => 'foo',
						'template' => 'visible-foo'
					],
					[
						'slug' => 'bar',
						'template' => 'visible-bar'
					],
					[
						'slug' => 'baz',
						'template' => 'visible-baz'
					]
				]
			],
			'users' => [
				['id' => 'bastian', 'role' => 'admin']
			]
		]);

		$app->impersonate('bastian');

		$page = $app->page('default');
		$this->assertTrue($page->isListable());

		$page = $app->page('foo');
		$this->assertTrue($page->isListable());

		$page = $app->page('bar');
		$this->assertFalse($page->isListable());

		$page = $app->page('baz');
		$this->assertFalse($page->isListable());
	}

	public function testIsAccessible(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'blueprints' => [
				'pages/accessible-foo' => [
					'options' => [
						'access' => true,
						'list' => false
					]
				],
				'pages/accessible-bar' => [
					'options' => [
						'access' => false,
						'list' => true
					]
				],
				'pages/accessible-baz' => [
					'options' => [
						'access' => true,
						'list' => true,
						'read' => false
					]
				]
			],
			'site' => [
				'children' => [
					[
						'slug' => 'default'
					],
					[
						'slug' => 'foo',
						'template' => 'accessible-foo'
					],
					[
						'slug' => 'bar',
						'template' => 'accessible-bar'
					],
					[
						'slug' => 'baz',
						'template' => 'accessible-baz'
					]
				]
			],
			'users' => [
				['id' => 'bastian', 'role' => 'admin']
			]
		]);

		$app->impersonate('bastian');

		$page = $app->page('default');
		$this->assertTrue($page->isAccessible());

		$page = $app->page('foo');
		$this->assertTrue($page->isAccessible());

		$page = $app->page('bar');
		$this->assertFalse($page->isAccessible());

		$page = $app->page('baz');
		$this->assertFalse($page->isAccessible());
	}
}
