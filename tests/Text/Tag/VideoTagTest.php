<?php

namespace Kirby\Text\Tag;

use Kirby\Cms\App;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(VideoTag::class)]
class VideoTagTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Text.VideoTag';

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

	public function testRenderLocal(): void
	{
		$kirby = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'  => 'test',
						'files' => [
							['filename' => 'sample.mp4']
						]
					]
				]
			]
		]);

		$page  = $kirby->page('test');
		$video = $page->file('sample.mp4');

		$tag = VideoTag::factory('video', 'sample.mp4', [], ['parent' => $page]);

		$expected = '<figure class="video"><video controls><source src="' . $video->url() . '" type="video/mp4"></video></figure>';

		$this->assertSame($expected, $tag->render());
	}

	public function testRenderInlineAttrs(): void
	{
		$kirby = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'  => 'test',
						'files' => [
							['filename' => 'sample.mp4'],
							['filename' => 'sample.jpg']
						]
					]
				]
			]
		]);

		$page  = $kirby->page('test');
		$image = $page->file('sample.jpg');
		$video = $page->file('sample.mp4');

		$tag = VideoTag::factory('video', 'sample.mp4', [
			'autoplay'                => 'true',
			'caption'                 => 'Lorem ipsum',
			'controls'                => 'false',
			'class'                   => 'video-class',
			'disablepictureinpicture' => 'true',
			'height'                  => '350',
			'loop'                    => 'true',
			'muted'                   => 'true',
			'playsinline'             => 'true',
			'poster'                  => 'sample.jpg',
			'preload'                 => 'auto',
			'width'                   => '500'
		], ['parent' => $page]);

		$expected = '<figure class="video-class"><video autoplay disablepictureinpicture height="350" loop muted playsinline poster="' . $image->url() . '" preload="auto" width="500"><source src="' . $video->url() . '" type="video/mp4"></video><figcaption>Lorem ipsum</figcaption></figure>';

		$this->assertSame($expected, $tag->render());
	}

	public function testRenderPredefinedAttrs(): void
	{
		$kirby = $this->app->clone([
			'options' => [
				'kirbytext' => [
					'video' => [
						'autoplay'    => true,
						'caption'     => 'Lorem ipsum',
						'controls'    => false,
						'class'       => 'video-class',
						'height'      => 350,
						'loop'        => true,
						'muted'       => true,
						'playsinline' => true,
						'poster'      => 'sample.jpg',
						'preload'     => 'auto',
						'width'       => 500
					]
				]
			],
			'site' => [
				'children' => [
					[
						'slug'  => 'test',
						'files' => [
							['filename' => 'sample.mp4'],
							['filename' => 'sample.jpg']
						]
					]
				]
			]
		]);

		$page  = $kirby->page('test');
		$image = $page->file('sample.jpg');
		$video = $page->file('sample.mp4');

		$tag = VideoTag::factory('video', 'sample.mp4', [], ['parent' => $page]);

		$expected = '<figure class="video-class"><video autoplay height="350" loop muted playsinline poster="' . $image->url() . '" preload="auto" width="500"><source src="' . $video->url() . '" type="video/mp4"></video><figcaption>Lorem ipsum</figcaption></figure>';

		$this->assertSame($expected, $tag->render());
	}

	public function testRenderAutoplayRelatedAttrs(): void
	{
		$kirby = new App([
			'roots' => [
				'index' => '/dev/null',
			],
			'site' => [
				'children' => [
					[
						'slug'  => 'test',
						'files' => [
							['filename' => 'sample.mp4']
						]
					]
				]
			]
		]);

		$page  = $kirby->page('test');
		$video = $page->file('sample.mp4');

		$tag = VideoTag::factory('video', 'sample.mp4', ['autoplay' => 'true'], ['parent' => $page]);

		$expected = '<figure class="video"><video autoplay controls muted playsinline><source src="' . $video->url() . '" type="video/mp4"></video></figure>';

		$this->assertSame($expected, $tag->render());
	}

	public function testRenderAutoplayAttrsOverride(): void
	{
		$kirby = new App([
			'roots' => [
				'index' => '/dev/null',
			],
			'site' => [
				'children' => [
					[
						'slug'  => 'test',
						'files' => [
							['filename' => 'sample.mp4']
						]
					]
				]
			]
		]);

		$page  = $kirby->page('test');
		$video = $page->file('sample.mp4');

		$tag = VideoTag::factory('video', 'sample.mp4', [
			'autoplay'    => 'true',
			'muted'       => 'false',
			'playsinline' => 'false'
		], ['parent' => $page]);

		$expected = '<figure class="video"><video autoplay controls><source src="' . $video->url() . '" type="video/mp4"></video></figure>';

		$this->assertSame($expected, $tag->render());
	}

	public function testRenderOptions(): void
	{
		$this->app->clone([
			'options' => [
				'kirbytext' => [
					'video' => [
						'options' => [
							'youtube' => [
								'controls' => 0
							]
						]
					]
				]
			]
		]);

		$tag = VideoTag::factory('video', 'https://www.youtube.com/watch?v=VhP7ZzZysQg');

		$expected = '<figure class="video"><iframe allow="fullscreen" allowfullscreen src="https://www.youtube.com/embed/VhP7ZzZysQg?controls=0"></iframe></figure>';

		$this->assertSame($expected, $tag->render());
	}

	public function testRenderRemote(): void
	{
		$this->app->clone();

		$tag = VideoTag::factory('video', 'https://getkirby.com/sample.mp4');

		$expected = '<figure class="video"><video controls><source src="https://getkirby.com/sample.mp4" type="video/mp4"></video></figure>';

		$this->assertSame($expected, $tag->render());
	}

	public function testRenderLocalFileMissing(): void
	{
		$this->app->clone();

		// a local video whose file cannot be found renders an empty figure
		$tag = VideoTag::factory('video', 'missing.mp4');

		$this->assertSame('<figure class="video"></figure>', $tag->render());
	}
}
