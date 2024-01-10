<?php

namespace Kirby\Text;

use Exception;
use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Text\KirbyTags
 */
class KirbyTagsTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';
	public const TMP      = KIRBY_TMP_DIR . '/Text.KirbyTags';

	protected $app;

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

	public static function dataProvider()
	{
		$tests = [];

		foreach (Dir::read($root = static::FIXTURES . '/kirbytext') as $dir) {
			$kirbytext = F::read($root . '/' . $dir . '/test.txt');
			$expected  = F::read($root . '/' . $dir . '/expected.html');

			$tests[] = [trim($kirbytext), trim($expected)];
		}

		return $tests;
	}

	/**
	 * @covers ::parse
	 */
	public function testParse()
	{
		KirbyTag::$types = [
			'test' => [
				'html' => fn () => 'test'
			]
		];

		$this->assertSame('test', KirbyTags::parse('(test: foo)'));
		$this->assertSame('test', KirbyTags::parse('(Test: foo)'));
		$this->assertSame('test', KirbyTags::parse('(TEST: foo)'));
		$this->assertSame('test', KirbyTags::parse('(tEsT: foo)'));
	}

	/**
	 * @covers ::parse
	 */
	public function testParseWithValue()
	{
		KirbyTag::$types = [
			'test' => [
				'html' => fn ($tag) => $tag->value
			]
		];

		$this->assertSame('foo', KirbyTags::parse('(test: foo)'));
		$this->assertSame('foo', KirbyTags::parse('(Test: foo)'));
		$this->assertSame('foo', KirbyTags::parse('(TEST: foo)'));
	}

	/**
	 * @covers ::parse
	 */
	public function testParseWithAttribute()
	{
		KirbyTag::$types = [
			'test' => [
				'attr' => ['a'],
				'html' => fn ($tag) => $tag->value . '|' . $tag->a
			]
		];

		$this->assertSame('foo|bar', KirbyTags::parse('(test: foo a: bar)'));
		$this->assertSame('foo|bar', KirbyTags::parse('(Test: foo A: bar)'));
		$this->assertSame('foo|bar', KirbyTags::parse('(TEST: foo a: bar)'));
	}

	/**
	 * @covers ::parse
	 */
	public function testParseWithException()
	{
		KirbyTag::$types = [
			'test' => [
				'html' => fn () => throw new Exception('Just for fun')
			],
			'invalidargument' => [
				'html' => fn () => throw new InvalidArgumentException('Just for fun')
			],
			'undefined' => [
				'html' => fn () => throw new InvalidArgumentException('Undefined tag type: undefined')
			]
		];

		$this->assertSame('(test: foo)', KirbyTags::parse('(test: foo)'));
		$this->assertSame('(invalidargument: foo)', KirbyTags::parse('(invalidargument: foo)'));
		$this->assertSame('(undefined: foo)', KirbyTags::parse('(undefined: foo)'));
	}

	/**
	 * @covers ::parse
	 */
	public function testParseWithExceptionDebug1()
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Just for fun');

		KirbyTag::$types = [
			'test' => [
				'html' => fn () => throw new Exception('Just for fun')
			]
		];

		KirbyTags::parse('(test: foo)', [], ['debug' => true]);
	}

	/**
	 * @covers ::parse
	 */
	public function testParseWithExceptionDebug2()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Just for fun');

		KirbyTag::$types = [
			'invalidargument' => [
				'html' => fn () => throw new InvalidArgumentException('Just for fun')
			]
		];

		KirbyTags::parse('(invalidargument: foo)', [], ['debug' => true]);
	}

	/**
	 * @covers ::parse
	 */
	public function testParseWithExceptionDebug3()
	{
		KirbyTag::$types = [
			'undefined' => [
				'html' => fn () => throw new InvalidArgumentException('Undefined tag type: undefined')
			]
		];

		$this->assertSame('(undefined: foo)', KirbyTags::parse('(undefined: foo)', [], ['debug' => true]));
	}

	/**
	 * @covers ::parse
	 */
	public function testParseWithBrackets()
	{
		KirbyTag::$types = [
			'test' => [
				'attr' => ['a'],
				'html' => function ($tag) {
					$value = $tag->value;

					if (empty($tag->a) === false) {
						$value .= ' - ' . $tag->a;
					}

					return $value;
				}
			]
		];

		$this->assertSame('foo(bar)', KirbyTags::parse('(test: foo(bar))'));
		$this->assertSame('foo(bar) - hello(world)', KirbyTags::parse('(test: foo(bar) a: hello(world))'));
		$this->assertSame('foo(bar) hello', KirbyTags::parse('(test: foo(bar) hello)'));
		$this->assertSame('foo(bar hello(world))', KirbyTags::parse('(test: foo(bar hello(world)))'));
		$this->assertSame('foo - (bar)', KirbyTags::parse('(test: foo a: (bar))'));
		$this->assertSame('(bar)', KirbyTags::parse('(test: (bar))'));
		// will not parse if brackets don't match
		$this->assertSame('(test: foo (bar)', KirbyTags::parse('(test: foo (bar)'));
	}

	/**
	 * @covers ::parse
	 * @dataProvider dataProvider
	 */
	public function testWithMarkdown($kirbytext, $expected)
	{
		$kirby = $this->app->clone([
			'options' => [
				'markdown' => [
					'extra' => false
				]
			]
		]);

		$this->assertSame($expected, $kirby->kirbytext($kirbytext));
	}

	/**
	 * @covers ::parse
	 * @dataProvider dataProvider
	 */
	public function testWithMarkdownExtra($kirbytext, $expected)
	{
		$kirby = $this->app->clone([
			'options' => [
				'markdown' => [
					'extra' => true
				]
			]
		]);

		$this->assertSame($expected, $kirby->kirbytext($kirbytext));
	}

	public function testImageWithoutFigure()
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

	public function testImageWithCaption()
	{
		$kirby    = $this->app->clone();
		$expected = '<figure><img alt="" src="/myimage.jpg"><figcaption>This is an <em>awesome</em> image and this a <a href="">link</a></figcaption></figure>';

		$this->assertSame($expected, $kirby->kirbytext('(image: myimage.jpg caption: This is an *awesome* image and this a <a href="">link</a>)'));
	}

	public function testImageWithSrcset()
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

	public function testImageWithSrcsetFromThumbsOption()
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

	public function testImageWithSrcsetFromDefaults()
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

	public function testImageWithFileLink()
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

	public function testImageWithFileUUID()
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

	public function testFile()
	{
		$kirby = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'content' => [
							'text' => '(file: a.jpg)'
						],
						'files' => [
							[
								'filename' => 'a.jpg',
							]
						]
					]
				]
			]
		]);

		$page = $kirby->page('a');
		$file = $page->file('a.jpg');

		$expected = '<p><a download href="' . $file->url() . '">a.jpg</a></p>';

		$this->assertSame($expected, $page->text()->kt()->value());
	}

	public function testFileWithUUID()
	{
		$kirby = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'content' => [
							'text' => '(file: file://file-a)',
							'uuid' => 'page-uuid' // this is just to make sure that the test doesn't try to create a content file for this page with a generated UUID
						],
						'files' => [
							[
								'filename' => 'a.jpg',
								'content'  => ['uuid' => 'file-a']
							]
						]
					]
				]
			]
		]);

		$page = $kirby->page('a');
		$file = $page->file('a.jpg');

		$expected = '<p><a download href="' . $file->url() . '">a.jpg</a></p>';

		$this->assertSame($expected, $page->text()->kt()->value());
	}

	public function testFileWithDisabledDownloadOption()
	{
		$kirby = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'content' => [
							'text' => '(file: a.jpg download: false)'
						],
						'files' => [
							[
								'filename' => 'a.jpg',
							]
						]
					]
				]
			]
		]);

		$page = $kirby->page('a');
		$file = $page->file('a.jpg');

		$expected = '<p><a href="' . $file->url() . '">a.jpg</a></p>';

		$this->assertSame($expected, $page->text()->kt()->value());
	}

	public function testFileWithinFile()
	{
		$kirby = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'files' => [
							[
								'filename' => 'a.jpg',
								'content' => [
									'caption' => '(file: b.jpg)'
								]
							],
							[
								'filename' => 'b.jpg'
							]
						]
					]
				]
			]
		]);

		$a = $kirby->file('a/a.jpg');
		$b = $kirby->file('a/b.jpg');
		$expected = '<p><a download href="' . $b->url() . '">b.jpg</a></p>';

		$this->assertSame($expected, $a->caption()->kt()->value());
	}

	public function testLinkWithLangAttribute()
	{
		$app = $this->app->clone([
			'urls' => [
				'index' => 'https://getkirby.com'
			],
			'languages' => [
				'en' => [
					'code' => 'en'
				],
				'de' => [
					'code' => 'de'
				]
			],
			'site' => [
				'children' => [
					['slug' => 'a']
				]
			]
		]);

		$this->assertSame('<a href="https://getkirby.com/en/a">getkirby.com/en/a</a>', $app->kirbytags('(link: a lang: en)'));
		$this->assertSame('<a href="https://getkirby.com/de/a">getkirby.com/de/a</a>', $app->kirbytags('(link: a lang: de)'));
	}

	public function testLinkWithHash()
	{
		$app = $this->app->clone([
			'urls' => [
				'index' => 'https://getkirby.com'
			],
			'languages' => [
				'en' => [
					'code' => 'en'
				],
				'de' => [
					'code' => 'de'
				]
			],
			'site' => [
				'children' => [
					['slug' => 'a']
				]
			]
		]);

		$this->assertSame('<a href="https://getkirby.com/en/a">getkirby.com/en/a</a>', $app->kirbytags('(link: a)'));
		$this->assertSame('<a href="https://getkirby.com/de/a">getkirby.com/de/a</a>', $app->kirbytags('(link: a lang: de)'));
		$this->assertSame('<a href="https://getkirby.com/en/a#anchor">getkirby.com/en/a</a>', $app->kirbytags('(link: a#anchor lang: en)'));
		$this->assertSame('<a href="https://getkirby.com/de/a#anchor">getkirby.com/de/a</a>', $app->kirbytags('(link: a#anchor lang: de)'));
	}

	public function testLinkWithUuid()
	{
		$app = $this->app->clone([
			'urls' => [
				'index' => 'https://getkirby.com'
			],
			'site' => [
				'children' => [
					[
						'slug'    => 'a',
						'content' => ['uuid' => 'page-uuid'],
						'files'   => [
							[
								'filename' => 'foo.jpg',
								'content' => ['uuid' => 'file-uuid'],
							]
						]
					]
				]
			]
		]);

		$result = $app->kirbytags('(link: page://page-uuid)');
		$this->assertSame('<a href="https://getkirby.com/a">getkirby.com/a</a>', $result);

		$result = $app->kirbytags('(link: file://file-uuid text: file)');
		$this->assertSame('<a href="' . $app->file('a/foo.jpg')->url() . '">file</a>', $result);
	}

	public function testLinkWithUuidAndLang()
	{
		$app = $this->app->clone([
			'urls' => [
				'index' => 'https://getkirby.com'
			],
			'languages' => [
				[
					'code'    => 'en',
					'name'    => 'English',
					'default' => true,
					'locale'  => 'en_US',
					'url'     => '/',
				],
				[
					'code'    => 'de',
					'name'    => 'Deutsch',
					'locale'  => 'de_DE',
					'url'     => '/de',
				],
			],
			'site' => [
				'children' => [
					[
						'slug'  => 'a',
						'files' => [
							[
								'filename'     => 'foo.jpg',
								'translations' => [
									[
										'code' => 'en',
										'content' => ['uuid' => 'file-uuid']
									],
									[
										'code' => 'de',
										'content' => []
									]
								]
							]
						],
						'translations' => [
							[
								'code' => 'en',
								'content' => ['uuid' => 'page-uuid']
							],
							[
								'code' => 'de',
								'content' => ['slug' => 'ae']
							]
						]
					]
				]
			]
		]);

		$result = $app->kirbytags('(link: page://page-uuid lang: de)');
		$this->assertSame('<a href="https://getkirby.com/de/ae">getkirby.com/de/ae</a>', $result);

		$result = $app->kirbytags('(link: file://file-uuid text: file lang: de)');
		$this->assertSame('<a href="' . $app->file('a/foo.jpg')->url() . '">file</a>', $result);
	}

	public function testHooks()
	{
		$app = $this->app->clone([
			'hooks' => [
				'kirbytags:before' => function ($text, $data, $options) {
					return 'before';
				},
			]
		]);

		$this->assertSame('before', $app->kirbytags('test'));

		$app = $app->clone([
			'hooks' => [
				'kirbytags:after' => function ($text, $data, $options) {
					return 'after';
				},
			]
		]);

		$this->assertSame('after', $app->kirbytags('test'));
	}

	public function testVideoLocal()
	{
		$kirby = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'content' => [
							'text' => '(video: sample.mp4)'
						],
						'files' => [
							['filename' => 'sample.mp4']
						]
					]
				]
			]
		]);

		$page  = $kirby->page('test');
		$image = $page->file('sample.mp4');

		$expected = '<figure class="video"><video controls><source src="' . $image->url() . '" type="video/mp4"></video></figure>';

		$this->assertSame($expected, $page->text()->kt()->value());
	}

	public function testVideoInlineAttrs()
	{
		$kirby = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'content' => [
							'text' => '(video: sample.mp4
                                autoplay: true
                                caption: Lorem ipsum
                                controls: false
                                class: video-class
                                height: 350
                                loop: true
                                muted: true
                                playsinline: true
                                poster: sample.jpg
                                preload: auto
                                style: border: none
                                width: 500)'
						],
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

		$expected = '<figure class="video-class" style="border: none"><video autoplay height="350" loop muted playsinline poster="' . $image->url() . '" preload="auto" width="500"><source src="' . $video->url() . '" type="video/mp4"></video><figcaption>Lorem ipsum</figcaption></figure>';
		$this->assertSame($expected, $page->text()->kt()->value());
	}

	public function testVideoPredefinedAttrs()
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
						'style'       => 'border: none',
						'width'       => 500
					]
				]
			],
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'content' => [
							'text' => '(video: sample.mp4)'
						],
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

		$expected = '<figure class="video-class" style="border: none"><video autoplay height="350" loop muted playsinline poster="' . $image->url() . '" preload="auto" width="500"><source src="' . $video->url() . '" type="video/mp4"></video><figcaption>Lorem ipsum</figcaption></figure>';
		$this->assertSame($expected, $page->text()->kt()->value());
	}

	public function testVideoAutoplayRelatedAttrs()
	{
		$kirby = new App([
			'roots' => [
				'index' => '/dev/null',
			],
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'content' => [
							'text' => '(video: sample.mp4 autoplay: true)'
						],
						'files' => [
							['filename' => 'sample.mp4']
						]
					]
				]
			]
		]);

		$page  = $kirby->page('test');
		$video = $page->file('sample.mp4');

		$expected = '<figure class="video"><video autoplay controls muted playsinline><source src="' . $video->url() . '" type="video/mp4"></video></figure>';

		$this->assertSame($expected, $page->text()->kt()->value());
	}

	public function testVideoAutoplayAttrsOverride()
	{
		$kirby = new App([
			'roots' => [
				'index' => '/dev/null',
			],
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'content' => [
							'text' => '(video: sample.mp4 autoplay: true muted: false playsinline: false)'
						],
						'files' => [
							['filename' => 'sample.mp4']
						]
					]
				]
			]
		]);

		$page  = $kirby->page('test');
		$image = $page->file('sample.mp4');

		$expected = '<figure class="video"><video autoplay controls><source src="' . $image->url() . '" type="video/mp4"></video></figure>';

		$this->assertSame($expected, $page->text()->kt()->value());
	}

	public function testVideoOptions()
	{
		$kirby = $this->app->clone([
			'options' => [
				'kirbytext' => [
					'video' => [
						'options'  => [
							'youtube' => [
								'controls' => 0
							]
						]
					]
				]
			],
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'content' => [
							'text' => '(video: https://www.youtube.com/watch?v=VhP7ZzZysQg)'
						]
					]
				]
			]
		]);

		$page  = $kirby->page('test');

		$expected = '<figure class="video"><iframe allow="fullscreen" allowfullscreen src="https://www.youtube.com/embed/VhP7ZzZysQg?controls=0"></iframe></figure>';
		$this->assertSame($expected, $page->text()->kt()->value());
	}

	public function testVideoRemote()
	{
		$kirby = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'content' => [
							'text' => '(video: https://getkirby.com/sample.mp4)'
						]
					]
				]
			]
		]);

		$page  = $kirby->page('test');

		$expected = '<figure class="video"><video controls><source src="https://getkirby.com/sample.mp4" type="video/mp4"></video></figure>';
		$this->assertSame($expected, $page->text()->kt()->value());
	}

	public static function globalOptionsProvider(): array
	{
		return [
			[
				'(image: image.jpg link: https://getkirby.com/)',
				'<figure><a href="https://getkirby.com/" rel="nofollow"><img alt="" class="image-class" src="/image.jpg"></a></figure>'
			],
			[
				'(link: http://wikipedia.org text: Wikipedia)',
				'<p><a class="link-class" href="http://wikipedia.org" rel="noreferrer" target="_blank">Wikipedia</a></p>'
			],
			[
				'(tel: +49123456789)',
				'<p><a class="phone" href="tel:+49123456789">+49123456789</a></p>'
			],
			[
				'(video: https://www.youtube.com/watch?v=VhP7ZzZysQg)',
				'<figure class="video-class"><iframe allow="fullscreen" allowfullscreen src="https://www.youtube.com/embed/VhP7ZzZysQg"></iframe></figure>'
			]
		];
	}

	/**
	 * @dataProvider globalOptionsProvider
	 */
	public function testGlobalOptions($kirbytext, $expected)
	{
		$kirby = $this->app->clone([
			'options' => [
				'kirbytext' => [
					'image' => [
						'rel' => 'nofollow',
						'imgclass' => 'image-class'
					],
					'link' => [
						'class' => 'link-class',
						'target' => '_blank'
					],
					'tel' => [
						'class' => 'phone'
					],
					'video' => [
						'class' => 'video-class',
					]
				]
			]
		]);

		$this->assertSame($expected, $kirby->kirbytext($kirbytext));
	}
}
