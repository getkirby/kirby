<?php

namespace Kirby\Field;

use Kirby\Cms\Page;
use Kirby\Option\Options;
use Kirby\Option\OptionsApi;
use Kirby\Option\OptionsQuery;

/**
 * @coversDefaultClass \Kirby\Field\FieldOptions
 */
class FieldOptionsTest extends TestCase
{
	/**
	 * @covers ::factory
	 */
	public function testFactory()
	{
		$options = FieldOptions::factory([
			'type' => 'api',
			'url' => $url = 'https://api.getkirby.com'
		]);
		$this->assertInstanceOf(OptionsApi::class, $options->options);
		$this->assertSame($url, $options->options->url);

		$options = FieldOptions::factory([
			'type'  => 'query',
			'query' => $query = 'site.children'
		]);
		$this->assertInstanceOf(OptionsQuery::class, $options->options);
		$this->assertSame($query, $options->options->query);

		$options = FieldOptions::factory(['type'  => 'array', 'options' => ['a', 'b']]);
		$this->assertInstanceOf(Options::class, $options->options);
		$this->assertSame(2, $options->options->count());
	}

	/**
	 * @covers ::polyfill
	 */
	public function testPolyfill()
	{
		$props = FieldOptions::polyfill(['options' => 'site.children']);
		$this->assertSame(['type' => 'query', 'query' => 'site.children'], $props['options']);

		$props = FieldOptions::polyfill(['options' => 'api', 'api' => 'https://api.getkirby.com']);
		$this->assertSame(['options' => ['type' => 'api', 'url' => 'https://api.getkirby.com']], $props);

		$props = FieldOptions::polyfill(['options' => 'query', 'query' => 'site.children']);
		$this->assertSame(['options' => ['type' => 'query', 'query' => 'site.children']], $props);

		$props = FieldOptions::polyfill(['options' => 'query', 'query' => ['fetch' => 'site.children']]);
		$this->assertSame(['options' => ['type' => 'query', 'query' => 'site.children']], $props);

		$props = FieldOptions::polyfill($expected = ['options' => ['type' => 'array', 'options' => ['a', 'b', 'c']]]);
		$this->assertSame($expected, $props);

		$props = FieldOptions::polyfill(['options' => ['a', 'b', 'c']]);
		$this->assertSame($expected, $props);

		$props = FieldOptions::polyfill(['options' => $options = ['a' => 'Option A', 'b' => 'Option B']]);
		$this->assertSame(['options' => ['type' => 'array', 'options' => $options]], $props);
	}

	/**
	 * @covers ::resolve
	 * @covers ::render
	 */
	public function testResolveRender()
	{
		$model = new Page(['slug' => 'test']);
		$options = FieldOptions::factory(['type'  => 'array', 'options' => ['a', 'b']]);
		$this->assertInstanceOf(Options::class, $options->resolve($model));
		$this->assertSame('a', $options->render($model)[0]['value']);

		$options = FieldOptions::factory([
			'type'  => 'api',
			'url'   =>  __DIR__ . '/../Option/fixtures/data.json',
			'query' => 'Directory.Companies'
		]);
		$this->assertInstanceOf(Options::class, $options->resolve($model));
		$this->assertSame('a', $options->render($model)[0]['value']);

		$options = FieldOptions::factory([
			'type'  => 'query',
			'query' => 'site.children'
		]);
		$this->assertInstanceOf(Options::class, $options->resolve($model));
		$this->assertSame([], $options->render($model));
	}
}
