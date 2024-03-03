<?php

namespace Kirby\Option;

use Kirby\Cms\Page;
use Kirby\Exception\NotFoundException;
use Kirby\Field\TestCase;

/**
 * @coversDefaultClass \Kirby\Option\OptionsApi
 */
class OptionsApiTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';

	/**
	 * @covers ::__construct
	 */
	public function testConstruct()
	{
		$options = new OptionsApi($url = 'https://api.example.com');
		$this->assertSame($url, $options->url);
		$this->assertNull($options->query);
		$this->assertNull($options->text);
		$this->assertNull($options->value);
	}

	/**
	 * @covers ::defaults
	 */
	public function testDefaults()
	{
		$options = new OptionsApi($url = 'https://api.example.com');
		$this->assertSame($url, $options->url);
		$this->assertNull($options->text);
		$this->assertNull($options->value);

		$options->defaults();

		$this->assertSame($url, $options->url);
		$this->assertSame('{{ item.value }}', $options->text);
		$this->assertSame('{{ item.key }}', $options->value);
	}

	/**
	 * @covers ::factory
	 */
	public function testFactory()
	{
		$options = OptionsApi::factory([
			'url'   => $url = 'https://api.example.com',
			'query' => $query = 'Companies',
			'text'  => $text = '{{ item.name }}',
			'value' => $value =  '{{ item.id }}',
		]);

		$this->assertSame($url, $options->url);
		$this->assertSame($query, $options->query);
		$this->assertSame($text, $options->text);
		$this->assertSame($value, $options->value);

		$options = OptionsApi::factory($url = 'https://api.example.com');
		$this->assertSame($url, $options->url);
		$this->assertNull($options->query);
		$this->assertNull($options->text);
		$this->assertNull($options->value);
	}

	/**
	 * @covers ::load
	 */
	public function testLoadNoJson()
	{
		$model   = new Page(['slug' => 'test']);
		$options = new OptionsApi(url: 'https://example.com');
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('Options could not be loaded from API: https://example.com');
		$options->resolve($model);
	}

	/**
	 * @covers ::polyfill
	 */
	public function testPolyfill()
	{
		$api = 'https://api.example.com';
		$this->assertSame(['url' => $api], OptionsApi::polyfill($api));

		$api = ['url' => 'https://api.example.com'];
		$this->assertSame($api, OptionsApi::polyfill($api));

		$api = ['fetch' => 'Companies'];
		$this->assertSame(['query' => 'Companies'], OptionsApi::polyfill($api));
	}

	/**
	 * @covers ::load
	 * @covers ::render
	 * @covers ::resolve
	 */
	public function testResolve()
	{
		$model   = new Page(['slug' => 'test']);
		$options = new OptionsApi(static::FIXTURES . '/data.json');
		$result  = $options->render($model);

		$this->assertSame('A', $result[0]['text']);
		$this->assertSame('a', $result[0]['value']);
		$this->assertSame('B', $result[1]['text']);
		$this->assertSame('b', $result[1]['value']);
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveSimple()
	{
		$model   = new Page(['slug' => 'test']);
		$options = new OptionsApi(static::FIXTURES . '/data-simple.json');
		$result  = $options->render($model);

		$this->assertSame('A', $result[0]['text']);
		$this->assertSame('a', $result[0]['value']);
		$this->assertSame('B', $result[1]['text']);
		$this->assertSame('b', $result[1]['value']);
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveCustomKeys()
	{
		$model   = new Page(['slug' => 'test']);
		$options = new OptionsApi(
			url: static::FIXTURES . '/data.json',
			text: '{{ item.name }}',
			value: '{{ item.email }}',
		);
		$result  = $options->render($model);

		$this->assertSame('Company A', $result[0]['text']);
		$this->assertSame('info@company-a.com', $result[0]['value']);
		$this->assertSame('Company B', $result[1]['text']);
		$this->assertSame('info@company-b.com', $result[1]['value']);
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveQuery()
	{
		$model   = new Page(['slug' => 'test']);
		$options = new OptionsApi(
			url: static::FIXTURES . '/data-nested.json',
			query: 'Directory.Companies'
		);
		$result  = $options->render($model);

		$this->assertSame('A', $result[0]['text']);
		$this->assertSame('a', $result[0]['value']);
		$this->assertSame('B', $result[1]['text']);
		$this->assertSame('b', $result[1]['value']);
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveCustomKeysAndQuery()
	{
		$model   = new Page(['slug' => 'test']);
		$options = new OptionsApi(
			url: static::FIXTURES . '/data-nested.json',
			query: 'Directory.Companies',
			text: '{{ item.name }}',
			value: '{{ item.email }}'
		);
		$result  = $options->render($model);

		$this->assertSame('Company A', $result[0]['text']);
		$this->assertSame('info@company-a.com', $result[0]['value']);
		$this->assertSame('Company B', $result[1]['text']);
		$this->assertSame('info@company-b.com', $result[1]['value']);
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveHtmlEscape()
	{
		$model = new Page(['slug' => 'test']);

		// text escaped by default
		$options = new OptionsApi(
			url: static::FIXTURES . '/data.json',
			text: '{{ item.slogan }}',
			value: '{{ item.slogan }}'
		);
		$result = $options->render($model);

		$this->assertSame('We are &lt;b&gt;great&lt;/b&gt;', $result[0]['text']);
		$this->assertSame('We are <b>great</b>', $result[0]['value']);
		$this->assertSame('We are &lt;b&gt;better&lt;/b&gt;', $result[1]['text']);
		$this->assertSame('We are <b>better</b>', $result[1]['value']);

		// with simple array
		$options = new OptionsApi(static::FIXTURES . '/data-simple-html.json');
		$result = $options->render($model);

		$this->assertSame('We are &lt;b&gt;great&lt;/b&gt;', $result[0]['text']);
		$this->assertSame('a', $result[0]['value']);
		$this->assertSame('We are &lt;b&gt;better&lt;/b&gt;', $result[1]['text']);
		$this->assertSame('b', $result[1]['value']);

		// with query
		$options = new OptionsApi(
			url: static::FIXTURES . '/data-nested.json',
			query: 'Directory.Companies',
			text: '{{ item.slogan }}',
			value: '{{ item.slogan }}'
		);
		$result = $options->render($model);

		$this->assertSame('We are &lt;b&gt;great&lt;/b&gt;', $result[0]['text']);
		$this->assertSame('We are <b>great</b>', $result[0]['value']);
		$this->assertSame('We are &lt;b&gt;better&lt;/b&gt;', $result[1]['text']);
		$this->assertSame('We are <b>better</b>', $result[1]['value']);

		// text unescaped using {< >}
		$options = new OptionsApi(
			url: static::FIXTURES . '/data.json',
			text: '{< item.slogan >}',
			value: '{{ item.slogan }}'
		);
		$result = $options->render($model);
		$this->assertSame('We are <b>great</b>', $result[0]['text']);
		$this->assertSame('We are <b>great</b>', $result[0]['value']);
		$this->assertSame('We are <b>better</b>', $result[1]['text']);
		$this->assertSame('We are <b>better</b>', $result[1]['value']);

		// text unescaped using {< >} (simple array)
		$options = new OptionsApi(
			url: static::FIXTURES . '/data-simple-html.json',
			text: '{< item.value >}',
			value: '{{ item.value }}'
		);
		$result = $options->render($model);
		$this->assertSame('We are <b>great</b>', $result[0]['text']);
		$this->assertSame('We are <b>great</b>', $result[0]['value']);
		$this->assertSame('We are <b>better</b>', $result[1]['text']);
		$this->assertSame('We are <b>better</b>', $result[1]['value']);

		// test unescaped with disabled safe mode
		$options = new OptionsApi(
			url: static::FIXTURES . '/data.json',
			text: '{{ item.slogan }}',
			value: '{{ item.slogan }}'
		);
		$result = $options->resolve($model, false)->render($model);
		$this->assertSame('We are <b>great</b>', $result[0]['text']);
		$this->assertSame('We are <b>great</b>', $result[0]['value']);
		$this->assertSame('We are <b>better</b>', $result[1]['text']);
		$this->assertSame('We are <b>better</b>', $result[1]['value']);
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveApplyFieldMethods()
	{
		$model   = new Page(['slug' => 'test']);
		$options = new OptionsApi(
			url: static::FIXTURES . '/data.json',
			text: '{{ item.name }}',
			value: '{{ item.name.slug }}'
		);
		$result  = $options->render($model);

		$this->assertSame('Company A', $result[0]['text']);
		$this->assertSame('company-a', $result[0]['value']);
		$this->assertSame('Company B', $result[1]['text']);
		$this->assertSame('company-b', $result[1]['value']);
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveSimpleArrays()
	{
		$model   = new Page(['slug' => 'test']);
		$options = new OptionsApi(
			url: static::FIXTURES . '/data-nested.json',
			query: 'simple',
			text: '{{ item }}',
			value: '{{ item.slug }}'
		);
		$result  = $options->render($model);

		$this->assertSame('Company A', $result[0]['text']);
		$this->assertSame('company-a', $result[0]['value']);
	}
}
