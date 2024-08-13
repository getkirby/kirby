<?php

namespace Kirby\Text;

use Kirby\Cms\App;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;

class ImageKirbyTagTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Text.ImageKirbyTag';

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			]
		]);

		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	public function testWithoutFigure()
	{
		$kirby = $this->app->clone([
			'options' => [
				'kirbytext' => [
					'image' => [
						'figure' => false
					]
				]
			]
		]);

		$expected = '<img alt="" src="https://test.com/something.jpg">';

		$this->assertSame($expected, $kirby->kirbytext('(image: https://test.com/something.jpg)'));
	}

	public function testWithCaption()
	{
		$kirby    = $this->app->clone();
		$expected = '<figure><img alt="" src="/myimage.jpg"><figcaption>This is an <em>awesome</em> image and this a <a href="">link</a></figcaption></figure>';

		$this->assertSame($expected, $kirby->kirbytext('(image: myimage.jpg caption: This is an *awesome* image and this a <a href="">link</a>)'));
	}

	public function testWithSrcset()
	{
		$kirby = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'content' => [
							'text' => '(image: image.jpg srcset: 200, 300)'
						],
						'files' => [
							[
								'filename' => 'image.jpg',
							]
						]
					]
				]
			]
		]);

		$page  = $kirby->page('a');
		$image = $page->file('image.jpg');

		$expected = '<figure><img alt="" src="' . $image->url() . '" srcset="' . $image->srcset([200, 300]) . '"></figure>';

		$this->assertSame($expected, $page->text()->kt()->value());
	}

	public function testWithSrcsetFromThumbsOption()
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
						'content' => [
							'text' => '(image: image.jpg srcset: album)'
						],
						'files' => [
							[
								'filename' => 'image.jpg',
							]
						]
					]
				]
			]
		]);

		$page  = $kirby->page('a');
		$image = $page->file('image.jpg');

		$expected = '<figure><img alt="" src="' . $image->url() . '" srcset="' . $image->srcset('album') . '"></figure>';

		$this->assertSame($expected, $page->text()->kt()->value());
	}

	public function testWithSrcsetFromDefaults()
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
						'content' => [
							'text' => '(image: image.jpg)'
						],
						'files' => [
							[
								'filename' => 'image.jpg',
							]
						]
					]
				]
			]
		]);

		$page  = $kirby->page('a');
		$image = $page->file('image.jpg');

		$expected = '<figure><img alt="" src="' . $image->url() . '" srcset="' . $image->srcset([200, 300]) . '"></figure>';

		$this->assertSame($expected, $page->text()->kt()->value());
	}

	public function testWithFileLink()
	{
		$kirby = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'content' => [
							'text' => '(image: image.jpg link: document.pdf)'
						],
						'files' => [
							[
								'filename' => 'image.jpg',
							],
							[
								'filename' => 'document.pdf',
							]
						]
					]
				]
			]
		]);

		$page  = $kirby->page('a');
		$image = $page->file('image.jpg');
		$doc   = $page->file('document.pdf');

		$expected = '<figure><a href="' . $doc->url() . '"><img alt="" src="' . $image->url() . '"></a></figure>';

		$this->assertSame($expected, $page->text()->kt()->value());
	}

	public function testWithFileUUID()
	{
		$kirby = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'content' => [
							'text' => '(image: file://image-uuid)',
							'uuid' => 'page-uuid' // this is just to make sure that the test doesn't try to create a content file for this page with a generated UUID
						],
						'files' => [
							[
								'filename' => 'image.jpg',
								'content' => ['uuid' => 'image-uuid']
							]
						]
					]
				]
			]
		]);

		$page  = $kirby->page('a');
		$image = $page->file('image.jpg');

		$expected = '<figure><img alt="" src="' . $image->url() . '"></figure>';

		$this->assertSame($expected, $page->text()->kt()->value());
	}

	public function testWithParent()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null',
			],
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'files' => [
							[
								'filename' => 'a.jpg'
							],
							[
								'filename' => 'b.jpg'
							],
							[
								'filename' => 'c.jpg'
							]
						]
					]
				]
			]
		]);

		$page = $app->page('a');
		$image = $page->image('b.jpg');
		$expected = '<figure><img alt="" src="/media/pages/a/' . $image->mediaHash() . '/b.jpg"></figure>';

		$this->assertSame($expected, $app->kirbytag('image', 'b.jpg', [], [
			'parent' => $page,
		]));
	}

	public function testWithWidthHeightAuto()
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
						'slug' => 'a',
						'root' => __DIR__ . '/fixtures/kirbytext/image-auto',
						'files' => [
							[
								'filename' => 'cat.jpg',
							]
						]
					]
				]
			]
		]);

		$page  = $app->page('a');
		$image = $page->image('cat.jpg');
		$expected = '<figure><img alt="" height="500" src="/media/pages/a/' . $image->mediaHash() . '/cat.jpg" width="500"></figure>';

		$this->assertSame($expected, $app->kirbytag('image', 'cat.jpg', [], [
			'parent' => $page,
		]));
	}
}
