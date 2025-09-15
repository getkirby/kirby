<?php

namespace Kirby\Field;

use Kirby\Cms\Page;
use Kirby\Option\Options;
use Kirby\Option\OptionsApi;
use Kirby\Option\OptionsQuery;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FieldOptions::class)]
class FieldOptionsTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/../Option/fixtures';

	public function testFactory(): void
	{
		$options = FieldOptions::factory([
			'type' => 'api',
			'url' => $url = 'https://api.getkirby.com'
		]);
		$this->assertInstanceOf(OptionsApi::class, $options->options);
		$this->assertTrue($options->safeMode);
		$this->assertSame($url, $options->options->url);

		$options = FieldOptions::factory([
			'type'  => 'query',
			'query' => $query = 'site.children'
		]);
		$this->assertInstanceOf(OptionsQuery::class, $options->options);
		$this->assertTrue($options->safeMode);
		$this->assertSame($query, $options->options->query);

		$options = FieldOptions::factory(['type' => 'array', 'options' => ['a', 'b']]);
		$this->assertInstanceOf(Options::class, $options->options);
		$this->assertTrue($options->safeMode);
		$this->assertCount(2, $options->options);

		$options = FieldOptions::factory(
			[
				'type'  => 'query',
				'query' => $query = 'site.children'
			],
			false
		);
		$this->assertInstanceOf(OptionsQuery::class, $options->options);
		$this->assertFalse($options->safeMode);
		$this->assertSame($query, $options->options->query);
	}

	public function testPolyfill(): void
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

	public function testResolveRender(): void
	{
		$model = new Page(['slug' => 'test']);
		$options = FieldOptions::factory(['type'  => 'array', 'options' => ['a', 'b']]);
		$this->assertInstanceOf(Options::class, $options->resolve($model));
		$this->assertSame('a', $options->render($model)[0]['value']);

		$options = FieldOptions::factory([
			'type'  => 'api',
			'url'   =>  static::FIXTURES . '/data-nested.json',
			'query' => 'Directory.Companies',
			'text'  => '{{ item.slogan }}'
		]);
		$this->assertInstanceOf(Options::class, $options->resolve($model));
		$this->assertSame('We are &lt;b&gt;great&lt;/b&gt;', $options->render($model)[0]['text']);

		// without safe mode
		$options = FieldOptions::factory(
			[
				'type'  => 'api',
				'url'   =>  static::FIXTURES . '/data-nested.json',
				'query' => 'Directory.Companies',
				'text'  => '{{ item.slogan }}'
			],
			false
		);
		$this->assertInstanceOf(Options::class, $options->resolve($model));
		$this->assertSame('We are <b>great</b>', $options->render($model)[0]['text']);

		$options = FieldOptions::factory([
			'type'  => 'query',
			'query' => 'site.children'
		]);
		$this->assertInstanceOf(Options::class, $options->resolve($model));
		$this->assertSame([], $options->render($model));
	}
}
