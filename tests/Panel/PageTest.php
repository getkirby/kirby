<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\Page as ModelPage;
use Kirby\Cms\User as ModelUser;
use Kirby\Content\Lock;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\TestCase;
use Kirby\Toolkit\Str;
use PHPUnit\Framework\Attributes\CoversClass;

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

#[CoversClass(Page::class)]
#[CoversClass(Model::class)]
class PageTest extends TestCase
{
	public const string FIXTURES = __DIR__ . '/fixtures';
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Page';

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

	public function testDragText(): void
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

	public function testDragTextMarkdown(): void
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

	public function testDragTextCustomMarkdown(): void
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

	public function testDragTextCustomKirbytext(): void
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

	public function testDropdownOption(): void
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

	public function testIconFromBlueprint(): void
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

	public function testId(): void
	{
		$parent = new ModelPage(['slug' => 'foo']);
		$page   = new ModelPage([
			'slug'   => 'bar',
			'parent' => $parent
		]);

		$id = (new Page($page))->id();
		$this->assertSame('foo+bar', $id);
	}

	public function testImage(): void
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

	public function testImageBlueprintIconWithEmoji(): void
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

	public function testImageCover(): void
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

		$testImage = static::FIXTURES . '/image/test.jpg';
		F::copy($testImage, $page->root() . '/test.jpg');

		$panel = new Page($page);

		$hash = $page->image()->mediaHash();
		$mediaUrl = $page->mediaUrl() . '/' . $hash;

		// cover disabled as default
		$this->assertSame([
			'back'   => 'pattern',
			'color'  => 'gray-500',
			'cover'  => false,
			'icon'   => 'page',
			'url'    => $mediaUrl . '/test.jpg',
			'src'    => Model::imagePlaceholder(),
			'srcset' => $mediaUrl . '/test-36x.jpg 36w, ' . $mediaUrl . '/test-96x.jpg 96w'
		], $panel->image());

		// cover enabled
		$this->assertSame([
			'back'   => 'pattern',
			'color'  => 'gray-500',
			'cover'  => true,
			'icon'   => 'page',
			'url'    => $mediaUrl . '/test.jpg',
			'src'    => Model::imagePlaceholder(),
			'srcset' => $mediaUrl . '/test-36x36-crop.jpg 1x, ' . $mediaUrl . '/test-96x96-crop.jpg 2x'
		], $panel->image(['cover' => true]));
	}

	public function testOptions(): void
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

	public function testOptionsWithLockedPage(): void
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

	public function testPath(): void
	{
		$page = new ModelPage([
			'slug'  => 'test'
		]);

		$panel = new Page($page);
		$this->assertSame('pages/test', $panel->path());
	}

	public function testPickerDataDefault(): void
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

	public function testPosition(): void
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

	public function testUrl(): void
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
}
