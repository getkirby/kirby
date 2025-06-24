<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Dir;
use Kirby\Plugin\Plugin;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Html::class)]
class HtmlTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';
	public const TMP      = KIRBY_TMP_DIR . '/Cms.Html';

	public function setUp(): void
	{
		Dir::copy(static::FIXTURES, static::TMP);

		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'urls' => [
				'index' => 'https://getkirby.com'
			]
		]);
	}

	public function testCss(): void
	{
		$result   = Html::css('assets/css/index.css');
		$expected = '<link href="https://getkirby.com/assets/css/index.css" rel="stylesheet">';

		$this->assertSame($expected, $result);
	}

	public function testCssWithMediaOption(): void
	{
		$result   = Html::css('assets/css/index.css', 'print');
		$expected = '<link href="https://getkirby.com/assets/css/index.css" media="print" rel="stylesheet">';

		$this->assertSame($expected, $result);
	}

	public function testCssWithAttrs(): void
	{
		$result   = Html::css('assets/css/index.css', ['integrity' => 'nope']);
		$expected = '<link href="https://getkirby.com/assets/css/index.css" integrity="nope" rel="stylesheet">';

		$this->assertSame($expected, $result);
	}

	public function testCssWithValidRelAttr(): void
	{
		$result   = Html::css('assets/css/index.css', ['rel' => 'alternate stylesheet', 'title' => 'High contrast']);
		$expected = '<link href="https://getkirby.com/assets/css/index.css" rel="alternate stylesheet" title="High contrast">';

		$this->assertSame($expected, $result);
	}

	public function testCssWithInvalidRelAttr(): void
	{
		$result   = Html::css('assets/css/index.css', ['rel' => 'alternate', 'title' => 'High contrast']);
		$expected = '<link href="https://getkirby.com/assets/css/index.css" rel="stylesheet" title="High contrast">';

		$this->assertSame($expected, $result);
	}

	public function testCssWithRelAttrButNoTitle(): void
	{
		$result   = Html::css('assets/css/index.css', ['rel' => 'alternate stylesheet']);
		$expected = '<link href="https://getkirby.com/assets/css/index.css" rel="stylesheet">';

		$this->assertSame($expected, $result);
	}

	public function testCssWithArray(): void
	{
		$result = Html::css([
			'assets/css/a.css',
			'assets/css/b.css'
		]);

		$expected  = '<link href="https://getkirby.com/assets/css/a.css" rel="stylesheet">' . PHP_EOL;
		$expected .= '<link href="https://getkirby.com/assets/css/b.css" rel="stylesheet">';

		$this->assertSame($expected, $result);
	}

	public function testCssWithPluginAssets(): void
	{
		$plugin = new Plugin('getkirby/test-plugin', [
			'root' => $root = static::TMP . '/plugin'
		]);
		touch($root . '/assets/styles.css', 1337000000);
		$result = Html::css($plugin);

		$expected  = '<link href="https://getkirby.com/media/plugins/getkirby/test-plugin/2375797551-1337000000/styles.css" rel="stylesheet">';

		$this->assertSame($expected, $result);
	}

	public function testJs(): void
	{
		$result   = Html::js('assets/js/index.js');
		$expected = '<script src="https://getkirby.com/assets/js/index.js"></script>';

		$this->assertSame($expected, $result);
	}

	public function testJsWithAsyncOption(): void
	{
		$result   = Html::js('assets/js/index.js', true);
		$expected = '<script async src="https://getkirby.com/assets/js/index.js"></script>';

		$this->assertSame($expected, $result);
	}

	public function testJsWithAttrs(): void
	{
		$result   = Html::js('assets/js/index.js', ['integrity' => 'nope']);
		$expected = '<script integrity="nope" src="https://getkirby.com/assets/js/index.js"></script>';

		$this->assertSame($expected, $result);
	}

	public function testJsWithArray(): void
	{
		$result = Html::js([
			'assets/js/a.js',
			'assets/js/b.js'
		]);

		$expected  = '<script src="https://getkirby.com/assets/js/a.js"></script>' . PHP_EOL;
		$expected .= '<script src="https://getkirby.com/assets/js/b.js"></script>';

		$this->assertSame($expected, $result);
	}

	public function testJsWithPluginAssets(): void
	{
		$plugin = new Plugin('getkirby/test-plugin', [
			'root' => $root = static::TMP . '/plugin'
		]);
		touch($root . '/assets/scripts.js', 1337000000);
		$result = Html::js($plugin);

		$expected  = '<script src="https://getkirby.com/media/plugins/getkirby/test-plugin/1903622314-1337000000/scripts.js"></script>';

		$this->assertSame($expected, $result);
	}

	public function testSvg(): void
	{
		$result = Html::svg('test.svg');
		$this->assertSame('<svg>test</svg>', trim($result));
	}

	public function testSvgWithAbsolutePath(): void
	{
		$result = Html::svg(static::TMP . '/test.svg');
		$this->assertSame('<svg>test</svg>', trim($result));
	}

	public function testSvgWithInvalidFileType(): void
	{
		$this->assertFalse(Html::svg(123));
	}

	public function testSvgWithMissingFile(): void
	{
		$this->assertFalse(Html::svg('somefile.svg'));
	}

	public function testSvgWithFileObject(): void
	{
		$file = $this->getMockBuilder(File::class)
			->disableOriginalConstructor()
			->onlyMethods(['__call'])
			->addMethods(['extension'])
			->getMock();
		$file->method('__call')->willReturn('test');
		$file->method('extension')->willReturn('svg');

		$this->assertSame('test', Html::svg($file));
	}
}
