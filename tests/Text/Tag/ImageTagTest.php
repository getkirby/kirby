<?php

namespace Kirby\Text\Tag;

use Kirby\Cms\App;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ImageTag::class)]
class ImageTagTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Text.ImageTag';

	protected function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			]
		]);

		Dir::make(static::TMP);
	}

	protected function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	public function testRenderWithoutFigure(): void
	{
		$this->app->clone([
			'options' => [
				'kirbytext' => [
					'image' => [
						'figure' => false
					]
				]
			]
		]);

		$tag = ImageTag::factory('image', 'https://test.com/something.jpg');

		$this->assertSame(
			'<img alt="" src="https://test.com/something.jpg">',
			$tag->render()
		);
	}

	public function testRenderWithCaption(): void
	{
		$this->app->clone();

		$tag = ImageTag::factory('image', 'myimage.jpg', [
			'caption' => 'This is an *awesome* image and this a <a href="">link</a>'
		]);

		$expected = '<figure><img alt="" src="/myimage.jpg"><figcaption>This is an <em>awesome</em> image and this a <a href="">link</a></figcaption></figure>';

		$this->assertSame($expected, $tag->render());
	}

	public function testRenderWithSrcset(): void
	{
		$kirby = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'files' => [
							['filename' => 'image.jpg']
						]
					]
				]
			]
		]);

		$page  = $kirby->page('a');
		$image = $page->file('image.jpg');

		$tag = ImageTag::factory('image', 'image.jpg', ['srcset' => '200, 300'], ['parent' => $page]);

		$expected = '<figure><img alt="" src="' . $image->url() . '" srcset="' . $image->srcset([200, 300]) . '"></figure>';

		$this->assertSame($expected, $tag->render());
	}

	public function testRenderWithSrcsetFromThumbsOption(): void
	{
		$kirby = $this->app->clone([
			'options' => [
				'thumbs' => [
					'srcsets' => [
						'album' => [
							'800w'  => ['width' => 800],
							'1024w' => ['width' => 1024]
						]
					]
				]
			],
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'files' => [
							['filename' => 'image.jpg']
						]
					]
				]
			]
		]);

		$page  = $kirby->page('a');
		$image = $page->file('image.jpg');

		$tag = ImageTag::factory('image', 'image.jpg', ['srcset' => 'album'], ['parent' => $page]);

		$expected = '<figure><img alt="" src="' . $image->url() . '" srcset="' . $image->srcset('album') . '"></figure>';

		$this->assertSame($expected, $tag->render());
	}

	public function testRenderWithSrcsetFromDefaults(): void
	{
		$kirby = $this->app->clone([
			'options' => [
				'kirbytext' => [
					'image' => [
						'srcset' => [200, 300]
					]
				]
			],
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'files' => [
							['filename' => 'image.jpg']
						]
					]
				]
			]
		]);

		$page  = $kirby->page('a');
		$image = $page->file('image.jpg');

		$tag = ImageTag::factory('image', 'image.jpg', [], ['parent' => $page]);

		$expected = '<figure><img alt="" src="' . $image->url() . '" srcset="' . $image->srcset([200, 300]) . '"></figure>';

		$this->assertSame($expected, $tag->render());
	}

	public function testRenderWithFileLink(): void
	{
		$kirby = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'files' => [
							['filename' => 'image.jpg'],
							['filename' => 'document.pdf']
						]
					]
				]
			]
		]);

		$page  = $kirby->page('a');
		$image = $page->file('image.jpg');
		$doc   = $page->file('document.pdf');

		$tag = ImageTag::factory('image', 'image.jpg', ['link' => 'document.pdf'], ['parent' => $page]);

		$expected = '<figure><a href="' . $doc->url() . '"><img alt="" src="' . $image->url() . '"></a></figure>';

		$this->assertSame($expected, $tag->render());
	}

	public function testRenderWithFileUUID(): void
	{
		$kirby = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'    => 'a',
						'content' => ['uuid' => 'page-uuid'],
						'files'   => [
							[
								'filename' => 'image.jpg',
								'content'  => ['uuid' => 'image-uuid']
							]
						]
					]
				]
			]
		]);

		$page  = $kirby->page('a');
		$image = $page->file('image.jpg');

		$tag = ImageTag::factory('image', 'file://image-uuid', [], ['parent' => $page]);

		$expected = '<figure><img alt="" src="' . $image->url() . '"></figure>';

		$this->assertSame($expected, $tag->render());
	}

	public function testRenderWithParent(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null',
			],
			'site' => [
				'children' => [
					[
						'slug'  => 'a',
						'files' => [
							['filename' => 'a.jpg'],
							['filename' => 'b.jpg'],
							['filename' => 'c.jpg']
						]
					]
				]
			]
		]);

		$page  = $app->page('a');
		$image = $page->image('b.jpg');

		$tag = ImageTag::factory('image', 'b.jpg', [], ['parent' => $page]);

		$expected = '<figure><img alt="" src="/media/pages/a/' . $image->mediaHash() . '/b.jpg"></figure>';

		$this->assertSame($expected, $tag->render());
	}

	public function testRenderWithWidthHeightAuto(): void
	{
		$app = $this->app->clone([
			'options' => [
				'kirbytext' => [
					'image' => [
						'width'  => 'auto',
						'height' => 'auto'
					]
				]
			],
			'roots' => [
				'index' => '/dev/null',
			],
			'site' => [
				'children' => [
					[
						'slug'  => 'a',
						'root'  => __DIR__ . '/../fixtures/kirbytext/image-auto',
						'files' => [
							['filename' => 'cat.jpg']
						]
					]
				]
			]
		]);

		$page  = $app->page('a');
		$image = $page->image('cat.jpg');

		$tag = ImageTag::factory('image', 'cat.jpg', [], ['parent' => $page]);

		$expected = '<figure><img alt="" height="500" src="/media/pages/a/' . $image->mediaHash() . '/cat.jpg" width="500"></figure>';

		$this->assertSame($expected, $tag->render());
	}

	public function testRenderWithLinkSelf(): void
	{
		$kirby = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'  => 'a',
						'files' => [
							['filename' => 'image.jpg']
						]
					]
				]
			]
		]);

		$page  = $kirby->page('a');
		$image = $page->file('image.jpg');

		// `link: self` links the image to its own URL
		$tag = ImageTag::factory('image', 'image.jpg', ['link' => 'self'], ['parent' => $page]);

		$expected = '<figure><a href="' . $image->url() . '"><img alt="" src="' . $image->url() . '"></a></figure>';

		$this->assertSame($expected, $tag->render());
	}

	public function testRenderWithExternalLink(): void
	{
		$kirby = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'  => 'a',
						'files' => [
							['filename' => 'image.jpg']
						]
					]
				]
			]
		]);

		$page  = $kirby->page('a');
		$image = $page->file('image.jpg');

		// a link that is neither a file nor `self` is used verbatim
		$tag = ImageTag::factory('image', 'image.jpg', ['link' => 'https://getkirby.com'], ['parent' => $page]);

		$expected = '<figure><a href="https://getkirby.com"><img alt="" src="' . $image->url() . '"></a></figure>';

		$this->assertSame($expected, $tag->render());
	}

	public function testRenderWithSrcsetWithoutFile(): void
	{
		$this->app->clone();

		// without a file, the srcset value is used as-is
		$tag = ImageTag::factory('image', 'https://test.com/image.jpg', ['srcset' => '200, 300']);

		$expected = '<figure><img alt="" src="https://test.com/image.jpg" srcset="200, 300"></figure>';

		$this->assertSame($expected, $tag->render());
	}
}
