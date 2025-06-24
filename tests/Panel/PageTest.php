<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\Page as ModelPage;
use Kirby\Cms\Site as ModelSite;
use Kirby\Cms\User as ModelUser;
use Kirby\Content\Lock;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;
use Kirby\Toolkit\Str;

class PageForceLocked extends ModelPage
{
	public function lock(): Lock
	{
		return new Lock(
			user: new ModelUser(['email' => 'test@getkirby.com']),
			modified: time()
		);
	}
}

/**
 * @coversDefaultClass \Kirby\Panel\Page
 */
class PageTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Page';

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP,
			]
		]);

		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	protected function panel(array $props = [])
	{
		$page = new ModelPage(['slug' => 'test', ...$props]);
		return new Page($page);
	}

	/**
	 * @covers ::breadcrumb
	 */
	public function testBreadcrumb(): void
	{
		$site = new ModelSite([
			'children' => [
				[
					'slug' => 'a',
					'children' => [
						[
							'slug' => 'b',
							'children' => [
								['slug' => 'c'],
							]
						],
					]
				],
			]
		]);

		$page = new Page($site->page('a'));
		$this->assertSame([
			[
				'label' => 'a',
				'link'  => '/pages/a'
			]
		], $page->breadcrumb());

		$page = new Page($site->page('a/b/c'));
		$this->assertSame([
			[
				'label' => 'a',
				'link'  => '/pages/a'
			],
			[
				'label' => 'b',
				'link'  => '/pages/a+b'
			],
			[
				'label' => 'c',
				'link'  => '/pages/a+b+c'
			]
		], $page->breadcrumb());
	}

	/**
	 * @covers ::buttons
	 */
	public function testButtons()
	{
		$this->assertSame([
			'k-open-view-button',
			'k-preview-view-button',
			'k-settings-view-button',
			'k-languages-view-button',
			'k-status-view-button',
		], array_column($this->panel()->buttons(), 'component'));
	}

	/**
	 * @covers ::dragText
	 */
	public function testDragText()
	{
		$page = new ModelPage([
			'slug' => 'test',
			'content' => ['uuid' => 'test-page']
		]);

		$panel = new Page($page);
		$this->assertSame('(link: page://test-page text: test)', $panel->dragText());

		// with title
		$page = new ModelPage([
			'slug' => 'test',
			'content' => [
				'title' => 'Test Title',
				'uuid' => 'test-page'
			]
		]);

		$panel = new Page($page);
		$this->assertSame('(link: page://test-page text: Test Title)', $panel->dragText());
	}

	/**
	 * @covers ::dragText
	 */
	public function testDragTextMarkdown()
	{
		$app = $this->app->clone([
			'options' => [
				'panel' => [
					'kirbytext' => false
				]
			],
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'content' => ['uuid' => 'my-a']
					],
					[
						'slug' => 'b',
						'content' => [
							'title' => 'Test Title',
							'uuid'  => 'my-b'
						]
					]
				]
			]
		]);

		$panel = new Page($app->page('a'));
		$this->assertSame('[a](//@/page/my-a)', $panel->dragText());

		$panel = new Page($app->page('b'));
		$this->assertSame('[Test Title](//@/page/my-b)', $panel->dragText());
	}

	/**
	 * @covers ::dragText
	 */
	public function testDragTextCustomMarkdown()
	{
		$app = $this->app->clone([
			'options' => [
				'panel' => [
					'kirbytext' => false,
					'markdown' => [
						'pageDragText' => function (ModelPage $page) {
							return sprintf('Links sind toll: %s', $page->url());
						},
					]
				]
			],
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'content' => [
							'title' => 'Test Title'
						]
					]
				]
			]
		]);

		$panel = new Page($app->page('test'));
		$this->assertSame('Links sind toll: /test', $panel->dragText());
	}

	/**
	 * @covers ::dragText
	 */
	public function testDragTextCustomKirbytext()
	{
		$app = $this->app->clone([
			'options' => [
				'panel' => [
					'kirbytext' => [
						'pageDragText' => function (ModelPage $page) {
							return sprintf('Links sind toll: %s', $page->url());
						},
					]
				]
			],
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'content' => [
							'title' => 'Test Title'
						]
					]
				]
			]
		]);

		$panel = new Page($app->page('test'));
		$this->assertSame('Links sind toll: /test', $panel->dragText());
	}

	/**
	 * @covers ::dropdownOption
	 */
	public function testDropdownOption()
	{
		$page = new ModelPage([
			'slug'    => 'test',
			'content' => [
				'title' => 'Test page'
			]
		]);

		$panel  = new Page($page);
		$option = $panel->dropdownOption();

		$this->assertSame('page', $option['icon']);
		$this->assertSame('Test page', $option['text']);
		$this->assertSame('/pages/test', $option['link']);
	}

	/**
	 * @covers ::image
	 */
	public function testIconFromBlueprint()
	{
		$page = new ModelPage([
			'slug' => 'test',
			'blueprint' => [
				'name' => 'test',
				'icon' => 'test'
			]
		]);

		$image = (new Page($page))->image();
		$this->assertSame('test', $image['icon']);
	}

	/**
	 * @covers ::id
	 */
	public function testId()
	{
		$parent = new ModelPage(['slug' => 'foo']);
		$page   = new ModelPage([
			'slug'   => 'bar',
			'parent' => $parent
		]);

		$id = (new Page($page))->id();
		$this->assertSame('foo+bar', $id);
	}

	/**
	 * @covers ::imageSource
	 */
	public function testImage()
	{
		$page = new ModelPage([
			'slug'  => 'test',
			'files' => [
				['filename' => 'test.jpg']
			]
		]);

		// fallback to model itself
		$image = (new Page($page))->image();
		$this->assertTrue(Str::endsWith($image['url'], '/test.jpg'));
	}

	/**
	 * @covers ::image
	 * @covers ::imageDefaults
	 */
	public function testImageBlueprintIconWithEmoji()
	{
		$page = new ModelPage([
			'slug' => 'test',
			'blueprint' => [
				'name' => 'test',
				'icon' => $emoji = '❤️'
			]
		]);

		$image = (new Page($page))->image();
		$this->assertSame($emoji, $image['icon']);
	}

	/**
	 * @covers ::imageSource
	 * @covers \Kirby\Panel\Model::imageSrcset
	 */
	public function testImageCover()
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'files' => [
							['filename' => 'test.jpg']
						]
					]
				]
			]
		]);

		$page  = $app->page('test');
		$panel = new Page($page);

		$hash = $page->image()->mediaHash();
		$mediaUrl = $page->mediaUrl() . '/' . $hash;

		// cover disabled as default
		$this->assertSame([
			'back' => 'pattern',
			'color' => 'gray-500',
			'cover' => false,
			'icon' => 'page',
			'url' => $mediaUrl . '/test.jpg',
			'src' => Model::imagePlaceholder(),
			'srcset' => $mediaUrl . '/test-38x.jpg 38w, ' . $mediaUrl . '/test-76x.jpg 76w'
		], $panel->image());

		// cover enabled
		$this->assertSame([
			'back' => 'pattern',
			'color' => 'gray-500',
			'cover' => true,
			'icon' => 'page',
			'url' => $mediaUrl . '/test.jpg',
			'src' => Model::imagePlaceholder(),
			'srcset' => $mediaUrl . '/test-38x38-crop.jpg 1x, ' . $mediaUrl . '/test-76x76-crop.jpg 2x'
		], $panel->image(['cover' => true]));
	}

	/**
	 * @covers \Kirby\Panel\Model::options
	 */
	public function testOptions()
	{
		$page = new ModelPage([
			'slug' => 'test',
		]);

		$page->kirby()->impersonate('kirby');

		$expected = [
			'access'         => true,
			'changeSlug'     => true,
			'changeStatus'   => true,
			'changeTemplate' => false, // no other template available in this scenario
			'changeTitle'    => true,
			'create'         => true,
			'delete'         => true,
			'duplicate'      => true,
			'list'           => true,
			'move'           => true,
			'preview'        => true,
			'read'           => true,
			'sort'           => false, // drafts cannot be sorted
			'update'         => true,
		];

		$panel = new Page($page);
		$this->assertSame($expected, $panel->options());
	}

	/**
	 * @covers \Kirby\Panel\Model::options
	 */
	public function testOptionsWithLockedPage()
	{
		$page = new PageForceLocked([
			'slug' => 'test',
		]);

		$page->kirby()->impersonate('kirby');

		// without override
		$expected = [
			'access'         => false,
			'changeSlug'     => false,
			'changeStatus'   => false,
			'changeTemplate' => false,
			'changeTitle'    => false,
			'create'         => false,
			'delete'         => false,
			'duplicate'      => false,
			'list'           => false,
			'move'           => false,
			'preview'        => false,
			'read'           => false,
			'sort'           => false,
			'update'         => false,
		];

		$panel = new Page($page);
		$this->assertSame($expected, $panel->options());

		// with override
		$expected = [
			'access'         => false,
			'changeSlug'     => false,
			'changeStatus'   => false,
			'changeTemplate' => false,
			'changeTitle'    => false,
			'create'         => false,
			'delete'         => false,
			'duplicate'      => false,
			'list'           => false,
			'move'           => false,
			'preview'        => true,
			'read'           => false,
			'sort'           => false,
			'update'         => false,
		];

		$this->assertSame($expected, $panel->options(['preview']));
	}

	/**
	 * @covers ::path
	 */
	public function testPath()
	{
		$page = new ModelPage([
			'slug'  => 'test'
		]);

		$panel = new Page($page);
		$this->assertSame('pages/test', $panel->path());
	}

	/**
	 * @covers ::pickerData
	 * @covers \Kirby\Panel\Model::pickerData
	 */
	public function testPickerDataDefault()
	{
		$page = new ModelPage([
			'slug' => 'test',
			'content' => [
				'title' => 'Test Title',
				'uuid'  => 'test-page'
			]
		]);

		$panel = new Page($page);
		$data  = $panel->pickerData();

		$this->assertSame('(link: page://test-page text: Test Title)', $data['dragText']);
		$this->assertSame('test', $data['id']);
		$this->assertSame('/pages/test', $data['link']);
		$this->assertSame('Test Title', $data['text']);
	}

	/**
	 * @covers ::position
	 */
	public function testPosition()
	{
		$page = new ModelPage([
			'slug' => 'test',
			'num'  => 3
		]);

		$panel = new Page($page);
		$this->assertSame(3, $panel->position());

		$parent = new ModelPage([
			'slug'     => 'test',
			'children' => [
				['slug' => 'a', 'num' => 1],
				['slug' => 'b', 'num' => 2],
				['slug' => 'c', 'num' => 3],
				['slug' => 'd', 'num' => null]
			]
		]);

		$panel = new Page($parent->find('d'));
		$this->assertSame(4, $panel->position());
	}

	/**
	 * @covers ::props
	 */
	public function testProps()
	{
		$page = new ModelPage([
			'slug'  => 'test'
		]);

		$panel = new Page($page);
		$props = $panel->props();

		$this->assertArrayHasKey('model', $props);
		$this->assertArrayHasKey('id', $props['model']);
		$this->assertArrayHasKey('parent', $props['model']);
		$this->assertArrayHasKey('previewUrl', $props['model']);
		$this->assertArrayHasKey('status', $props['model']);
		$this->assertArrayHasKey('title', $props['model']);

		// inherited props
		$this->assertArrayHasKey('blueprint', $props);
		$this->assertArrayHasKey('lock', $props);
		$this->assertArrayHasKey('permissions', $props);
		$this->assertArrayNotHasKey('tab', $props);
		$this->assertArrayHasKey('tabs', $props);
		$this->assertArrayHasKey('versions', $props);

		$this->assertNull($props['next']());
		$this->assertNull($props['prev']());
	}

	/**
	 * @covers ::props
	 * @covers ::prevNext
	 */
	public function testPropsPrevNext()
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'foo'],
					['slug' => 'bar'],
					['slug' => 'baz']
				]
			],
		]);
		$app->impersonate('kirby');

		$props = (new Page($app->page('foo')))->props();
		$this->assertNull($props['prev']());
		$this->assertSame('/pages/bar', $props['next']()['link']);

		$props = (new Page($app->page('bar')))->props();
		$this->assertSame('/pages/foo', $props['prev']()['link']);
		$this->assertSame('/pages/baz', $props['next']()['link']);

		$props = (new Page($app->page('baz')))->props();
		$this->assertSame('/pages/bar', $props['prev']()['link']);
		$this->assertNull($props['next']());
	}

	/**
	 * @covers ::props
	 * @covers ::prevNext
	 */
	public function testPropsPrevNextWithSameTemplate()
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'foo', 'template' => 'note'],
					['slug' => 'bar', 'template' => 'album'],
					['slug' => 'baz', 'template' => 'note']
				]
			],
		]);
		$app->impersonate('kirby');

		$props = (new Page($app->page('foo')))->props();
		$this->assertSame('/pages/baz', $props['next']()['link']);

		$props = (new Page($app->page('bar')))->props();
		$this->assertNull($props['prev']());
		$this->assertNull($props['next']());

		$props = (new Page($app->page('baz')))->props();
		$this->assertSame('/pages/foo', $props['prev']()['link']);
	}

	/**
	 * @covers ::props
	 * @covers ::prevNext
	 */
	public function testPropsPrevNextWithSameStatus()
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'foo', 'num' => 0],
					['slug' => 'bar', 'num' => null],
					['slug' => 'baz', 'num' => 0]
				]
			],
		]);
		$app->impersonate('kirby');

		$props = (new Page($app->page('foo')))->props();
		$this->assertSame('/pages/baz', $props['next']()['link']);

		$props = (new Page($app->page('bar')))->props();
		$this->assertNull($props['prev']());
		$this->assertNull($props['next']());

		$props = (new Page($app->page('baz')))->props();
		$this->assertSame('/pages/foo', $props['prev']()['link']);
	}

	/**
	 * @covers ::prevNext
	 * @covers ::toPrevNextLink
	 */
	public function testPropsPrevNextWithTab()
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'foo'],
					['slug' => 'bar'],
					['slug' => 'baz']
				]
			],
		]);
		$app->impersonate('kirby');

		$_GET['tab'] = 'test';

		$prevNext = (new Page($app->page('bar')))->prevNext();
		$this->assertSame('/pages/foo?tab=test', $prevNext['prev']()['link']);
		$this->assertSame('/pages/baz?tab=test', $prevNext['next']()['link']);

		$_GET = [];
	}

	/**
	 * @covers ::view
	 */
	public function testView()
	{
		$page = new ModelPage([
			'slug'  => 'test',
		]);

		$panel = new Page($page);
		$view  = $panel->view();

		$this->assertArrayHasKey('props', $view);
		$this->assertSame('k-page-view', $view['component']);
		$this->assertSame('test', $view['title']);
		$this->assertSame('test', $view['breadcrumb'][0]['label']);
	}

	/**
	 * @covers ::url
	 */
	public function testUrl()
	{
		$app = $this->app->clone([
			'urls' => [
				'index' => 'https://getkirby.com'
			],
			'site' => [
				'children' => [
					[
						'slug' => 'mother',
						'children' => [
							[
								'slug' => 'child'
							]
						]
					]
				]
			]
		]);

		$page  = $app->page('mother/child');
		$panel = new Page($page);

		$this->assertSame('https://getkirby.com/panel/pages/mother+child', $panel->url());
		$this->assertSame('/pages/mother+child', $panel->url(true));
	}

	/**
	 * @covers ::prevNext
	 */
	public function testPrevNextOne()
	{
		$app = $this->app->clone([
			'roots' => [
				'index' => static::TMP,
			],
			'blueprints' => [
				'pages/a' => [
					'title' => 'A',
					'navigation' => [
						'status' => 'all',
						'template' => 'all'
					]
				],
				'pages/b' => [
					'title' => 'B',
					'navigation' => [
						'status' => 'all',
						'template' => 'all'
					]
				]
			]
		]);

		$app->impersonate('kirby');

		$parent = ModelPage::create([
			'slug' => 'test'
		]);

		$parent->createChild([
			'slug'     => 'a',
			'template' => 'a'
		]);

		$expectedPrev = $parent->createChild([
			'slug'     => 'b',
			'template' => 'b'
		]);

		$parent->createChild([
			'slug'     => 'c',
			'template' => 'a'
		]);

		$expectedNext = $parent->createChild([
			'slug'     => 'd',
			'template' => 'b'
		]);

		$page  = $app->page('test/c');
		$panel = new Page($page);

		$navigation = $page->blueprint()->navigation();
		$prevNext   = $panel->prevNext();

		$this->assertSame(['status' => 'all', 'template' => 'all'], $navigation);
		$this->assertArrayHasKey('next', $prevNext);
		$this->assertArrayHasKey('prev', $prevNext);
		$this->assertSame($expectedNext->panel()->toLink(), $prevNext['next']());
		$this->assertSame($expectedPrev->panel()->toLink(), $prevNext['prev']());
	}

	/**
	 * @covers ::prevNext
	 */
	public function testPrevNextTwo()
	{
		$app = $this->app->clone([
			'roots' => [
				'index' => static::TMP,
			],
			'blueprints' => [
				'pages/c' => [
					'title' => 'C',
					'navigation' => [
						'status' => ['listed'],
						'template' => ['c']
					]
				],
				'pages/d' => [
					'title' => 'D',
					'navigation' => [
						'status' => ['listed'],
						'template' => ['c']
					]
				]
			]
		]);

		$app->impersonate('kirby');

		$parent = ModelPage::create([
			'slug' => 'test'
		]);

		$expectedPrev = $parent->createChild([
			'slug'     => 'a',
			'template' => 'c'
		])->changeStatus('listed');

		$parent->createChild([
			'slug'     => 'b',
			'template' => 'd'
		])->changeStatus('listed');

		$parent->createChild([
			'slug'     => 'c',
			'template' => 'c'
		]);

		$parent->createChild([
			'slug'     => 'd',
			'template' => 'd'
		])->changeStatus('listed');

		$expectedNext = $parent->createChild([
			'slug'     => 'e',
			'template' => 'c'
		])->changeStatus('listed');

		$parent->createChild([
			'slug'     => 'f',
			'template' => 'd'
		])->changeStatus('listed');

		$page  = $app->page('test/d');
		$panel = new Page($page);

		$navigation = $page->blueprint()->navigation();
		$prevNext   = $panel->prevNext();

		$this->assertSame([
			'status' => ['listed'],
			'template' => ['c']
		], $navigation);
		$this->assertArrayHasKey('next', $prevNext);
		$this->assertArrayHasKey('prev', $prevNext);
		$this->assertSame($expectedNext->panel()->toLink(), $prevNext['next']());
		$this->assertSame($expectedPrev->panel()->toLink(), $prevNext['prev']());
	}

	/**
	 * @covers ::prevNext
	 */
	public function testPrevNextThree()
	{
		$app = $this->app->clone([
			'roots' => [
				'index' => static::TMP,
			],
			'blueprints' => [
				'pages/e' => [
					'title' => 'E',
					'navigation' => [
						'status' => ['listed'],
						'template' => ['e', 'f']
					]
				],
				'pages/f' => [
					'title' => 'F',
					'navigation' => [
						'status' => ['listed'],
						'template' => ['e', 'f']
					]
				]
			]
		]);

		$app->impersonate('kirby');

		$parent = ModelPage::create([
			'slug' => 'test'
		]);

		$expectedPrev = $parent->createChild([
			'slug'     => 'a',
			'template' => 'e'
		])->changeStatus('listed');

		$parent->createChild([
			'slug'     => 'b',
			'template' => 'f'
		])->changeStatus('unlisted');

		$parent->createChild([
			'slug'     => 'c',
			'template' => 'e'
		])->changeStatus('unlisted');

		$parent->createChild([
			'slug'     => 'd',
			'template' => 'f'
		])->changeStatus('listed');

		$parent->createChild([
			'slug'     => 'e',
			'template' => 'e'
		])->changeStatus('unlisted');

		$expectedNext = $parent->createChild([
			'slug'     => 'f',
			'template' => 'f'
		])->changeStatus('listed');

		$page  = $app->page('test/d');
		$panel = new Page($page);

		$navigation = $page->blueprint()->navigation();
		$prevNext   = $panel->prevNext();

		$this->assertSame([
			'status' => ['listed'],
			'template' => ['e', 'f']
		], $navigation);
		$this->assertArrayHasKey('next', $prevNext);
		$this->assertArrayHasKey('prev', $prevNext);
		$this->assertSame($expectedNext->panel()->toLink(), $prevNext['next']());
		$this->assertSame($expectedPrev->panel()->toLink(), $prevNext['prev']());
	}

	/**
	 * @covers ::prevNext
	 */
	public function testPrevNextFour()
	{
		$app = $this->app->clone([
			'roots' => [
				'index' => static::TMP,
			],
			'blueprints' => [
				'pages/g' => [
					'title' => 'A',
					'navigation' => [
						'status' => 'all',
						'template' => 'all',
						'sortBy' => 'slug desc'
					]
				],
				'pages/h' => [
					'title' => 'B',
					'navigation' => [
						'status' => 'all',
						'template' => 'all',
						'sortBy' => 'slug desc'
					]
				]
			]
		]);

		$app->impersonate('kirby');

		$parent = ModelPage::create([
			'slug' => 'test'
		]);

		$parent->createChild([
			'slug'     => 'a',
			'template' => 'g'
		]);

		$expectedNext = $parent->createChild([
			'slug'     => 'b',
			'template' => 'h'
		]);

		$parent->createChild([
			'slug'     => 'c',
			'template' => 'g'
		]);

		$expectedPrev = $parent->createChild([
			'slug'     => 'd',
			'template' => 'h'
		]);

		$page  = $app->page('test/c');
		$panel = new Page($page);

		$navigation = $page->blueprint()->navigation();
		$prevNext   = $panel->prevNext();

		$this->assertSame([
			'status' => 'all',
			'template' => 'all',
			'sortBy' => 'slug desc'
		], $navigation);
		$this->assertArrayHasKey('next', $prevNext);
		$this->assertArrayHasKey('prev', $prevNext);
		$this->assertSame($expectedNext->panel()->toLink(), $prevNext['next']());
		$this->assertSame($expectedPrev->panel()->toLink(), $prevNext['prev']());
	}
}
