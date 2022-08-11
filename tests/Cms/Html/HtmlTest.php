<?php

namespace Kirby\Cms;

/**
 * @coversDefaultClass Kirby\Cms\Html
 */
class HtmlTest extends TestCase
{
	protected $fixtures;
	protected $kirby;

	public function setUp(): void
	{
		$this->kirby = new App([
			'roots' => [
				'index' => $this->fixtures = __DIR__ . '/fixtures/HtmlTest'
			],
			'urls' => [
				'index' => 'https://getkirby.com'
			]
		]);
	}

	/**
	 * @covers ::css
	 */
	public function testCss()
	{
		$result   = Html::css('assets/css/index.css');
		$expected = '<link href="https://getkirby.com/assets/css/index.css" rel="stylesheet">';

		$this->assertSame($expected, $result);
	}

	/**
	 * @covers ::css
	 */
	public function testCssWithMediaOption()
	{
		$result   = Html::css('assets/css/index.css', 'print');
		$expected = '<link href="https://getkirby.com/assets/css/index.css" media="print" rel="stylesheet">';

		$this->assertSame($expected, $result);
	}

	/**
	 * @covers ::css
	 */
	public function testCssWithAttrs()
	{
		$result   = Html::css('assets/css/index.css', ['integrity' => 'nope']);
		$expected = '<link href="https://getkirby.com/assets/css/index.css" integrity="nope" rel="stylesheet">';

		$this->assertSame($expected, $result);
	}

	/**
	 * @covers ::css
	 */
	public function testCssWithValidRelAttr()
	{
		$result   = Html::css('assets/css/index.css', ['rel' => 'alternate stylesheet', 'title' => 'High contrast']);
		$expected = '<link href="https://getkirby.com/assets/css/index.css" rel="alternate stylesheet" title="High contrast">';

		$this->assertSame($expected, $result);
	}

	/**
	 * @covers ::css
	 */
	public function testCssWithInvalidRelAttr()
	{
		$result   = Html::css('assets/css/index.css', ['rel' => 'alternate', 'title' => 'High contrast']);
		$expected = '<link href="https://getkirby.com/assets/css/index.css" rel="stylesheet" title="High contrast">';

		$this->assertSame($expected, $result);
	}

	/**
	 * @covers ::css
	 */
	public function testCssWithRelAttrButNoTitle()
	{
		$result   = Html::css('assets/css/index.css', ['rel' => 'alternate stylesheet']);
		$expected = '<link href="https://getkirby.com/assets/css/index.css" rel="stylesheet">';

		$this->assertSame($expected, $result);
	}

	/**
	 * @covers ::css
	 */
	public function testCssWithArray()
	{
		$result = Html::css([
			'assets/css/a.css',
			'assets/css/b.css'
		]);

		$expected  = '<link href="https://getkirby.com/assets/css/a.css" rel="stylesheet">' . PHP_EOL;
		$expected .= '<link href="https://getkirby.com/assets/css/b.css" rel="stylesheet">';

		$this->assertSame($expected, $result);
	}

	/**
	 * @covers ::js
	 */
	public function testJs()
	{
		$result   = Html::js('assets/js/index.js');
		$expected = '<script src="https://getkirby.com/assets/js/index.js"></script>';

		$this->assertSame($expected, $result);
	}

	/**
	 * @covers ::js
	 */
	public function testJsWithAsyncOption()
	{
		$result   = Html::js('assets/js/index.js', true);
		$expected = '<script async src="https://getkirby.com/assets/js/index.js"></script>';

		$this->assertSame($expected, $result);
	}

	/**
	 * @covers ::js
	 */
	public function testJsWithAttrs()
	{
		$result   = Html::js('assets/js/index.js', ['integrity' => 'nope']);
		$expected = '<script integrity="nope" src="https://getkirby.com/assets/js/index.js"></script>';

		$this->assertSame($expected, $result);
	}

	/**
	 * @covers ::js
	 */
	public function testJsWithArray()
	{
		$result = Html::js([
			'assets/js/a.js',
			'assets/js/b.js'
		]);

		$expected  = '<script src="https://getkirby.com/assets/js/a.js"></script>' . PHP_EOL;
		$expected .= '<script src="https://getkirby.com/assets/js/b.js"></script>';

		$this->assertSame($expected, $result);
	}

	/**
	 * @covers ::svg
	 */
	public function testSvg()
	{
		$result = Html::svg('test.svg');
		$this->assertSame('<svg>test</svg>', trim($result));
	}

	/**
	 * @covers ::svg
	 */
	public function testSvgWithAbsolutePath()
	{
		$result = Html::svg(__DIR__ . '/fixtures/HtmlTest/test.svg');
		$this->assertSame('<svg>test</svg>', trim($result));
	}

	/**
	 * @covers ::svg
	 */
	public function testSvgWithInvalidFileType()
	{
		$this->assertFalse(Html::svg(123));
	}

	/**
	 * @covers ::svg
	 */
	public function testSvgWithMissingFile()
	{
		$this->assertFalse(Html::svg('somefile.svg'));
	}

	/**
	 * @covers ::svg
	 */
	public function testSvgWithFileObject()
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
