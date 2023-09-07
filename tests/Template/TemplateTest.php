<?php

namespace Kirby\Template;

use Kirby\Cms\App;

/**
 * @coversDefaultClass Kirby\Template\Template
 */
class TemplateTest extends TestCase
{
	/**
	 * @covers ::__construct
	 * @covers ::name
	 * @covers ::type
	 * @covers ::defaultType
	 * @covers ::extension
	 * @covers ::__toString
	 */
	public function testTemplate()
	{
		$template = new Template('Test', 'foo', 'bar');
		$this->assertSame('test', $template->name());
		$this->assertSame('test', $template->__toString());
		$this->assertSame('foo', $template->type());
		$this->assertSame('bar', $template->defaultType());

		$this->assertSame('php', $template->extension());
	}

	/**
	 * @covers ::exists
	 */
	public function testExists()
	{
		new App([
			'roots' => [
				'templates' => __DIR__ . '/fixtures'
			]
		]);

		$template = new Template('test');
		$this->assertFalse($template->exists());

		$template = new Template('simple');
		$this->assertTrue($template->exists());

		$template = new Template('simple', 'rss');
		$this->assertTrue($template->exists());

		$template = new Template('simple', 'foo');
		$this->assertFalse($template->exists());
	}

	/**
	 * @covers ::file
	 */
	public function testFile()
	{
		App::plugin('test/c', [
			'templates' => [
				'plugin' => 'plugin.php'
			]
		]);

		new App([
			'roots' => [
				'templates' => __DIR__ . '/fixtures'
			]
		]);

		$template = new Template('test');
		$this->assertNull($template->file());

		$template = new Template('simple');
		$this->assertSame(__DIR__ . '/fixtures/simple.php', $template->file());

		$template = new Template('simple', 'rss');
		$this->assertSame(__DIR__ . '/fixtures/simple.rss.php', $template->file());

		$template = new Template('plugin');
		$this->assertSame('plugin.php', $template->file());
	}

	/**
	 * @covers ::hasDefaultType
	 */
	public function testHasDefaultType()
	{
		$template = new Template('test');
		$this->assertTrue($template->hasDefaultType());

		$template = new Template('test', 'foo', 'foo');
		$this->assertTrue($template->hasDefaultType());

		$template = new Template('test', 'foo', 'bar');
		$this->assertFalse($template->hasDefaultType());
	}

	/**
	 * @covers ::store
	 * @covers ::root
	 */
	public function testRoot()
	{
		new App([
			'roots' => [
				'templates' => $root = __DIR__ . '/fixtures'
			]
		]);

		$template = new Template('test');
		$this->assertSame('templates', $template->store());
		$this->assertSame($root, $template->root());
	}

	/**
	 * @covers ::render
	 */
	public function testRender()
	{
		new App([
			'roots' => [
				'templates' => $root = __DIR__ . '/fixtures'
			]
		]);

		$template = new Template('simple');
		$this->assertSame('Test', $template->render(['slot' => 'Test']));
	}

	/**
	 * @covers ::render
	 */
	public function testRenderOpenLayoutSnippet()
	{
		new App([
			'roots' => [
				'snippets'  => __DIR__ . '/fixtures',
				'templates' => __DIR__ . '/fixtures'
			]
		]);

		$template = new Template('with-layout');
		$this->assertSame("<h1>Layout</h1>\nMy content\n<footer>with other stuff</footer>\n", $template->render());
	}

	/**
	 * @covers ::render
	 */
	public function testRenderOpenParentSnippet1()
	{
		$app = new App([
			'roots' => [
				'snippets'  => __DIR__ . '/fixtures',
				'templates' => __DIR__ . '/fixtures'
			]
		]);

		$this->assertSame(
			"Before rendering\n" .
			"Simple output\n" .
			"After rendering\n",
			$app->snippet('render')
		);
	}

	/**
	 * @covers ::render
	 */
	public function testRenderOpenParentSnippet2()
	{
		$app = new App([
			'roots' => [
				'snippets'  => __DIR__ . '/fixtures',
				'templates' => __DIR__ . '/fixtures'
			]
		]);

		$template = new Template('render-in-slot');
		$this->assertSame(
			"Before snippet\n" .
			"Before rendering\n" .
			"Simple output\n" .
			"After rendering\n" .
			"After snippet\n",
			$template->render()
		);
	}

	/**
	 * @covers ::render
	 */
	public function testRenderOpenParentSnippet3()
	{
		$app = new App([
			'roots' => [
				'snippets'  => __DIR__ . '/fixtures',
				'templates' => __DIR__ . '/fixtures'
			]
		]);

		$template = new Template('render-in-slot-layout');
		$this->assertSame(
			"Before snippet\n" .
			"Before rendering\n" .
			"<h1>Layout</h1>\nMy content\n<footer>with other stuff</footer>\n" .
			"After rendering\n" .
			"After snippet\n",
			$template->render()
		);
	}
}
