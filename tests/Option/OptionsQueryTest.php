<?php

namespace Kirby\Option;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Field\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

class MyPage extends Page
{
	public function myArray(): array
	{
		return [['name' => 'foo'], ['name' => 'bar']];
	}

	public function mySimpleArray(): array
	{
		return ['tag1', 'tag2', 'tag3'];
	}

	public function myHtmlArray(): array
	{
		return [
			['slogan' => 'We are <b>great</b>'],
			['slogan' => 'We are <b>better</b>']
		];
	}

	public function myOptions(): Options
	{
		return Options::factory(['foo', 'bar']);
	}
}

#[CoversClass(OptionsQuery::class)]
class OptionsQueryTest extends TestCase
{
	public function testConstruct(): void
	{
		$options = new OptionsQuery($query = 'site.children', '{{ page.slug }}');
		$this->assertSame($query, $options->query);
		$this->assertSame('{{ page.slug }}', $options->text);
		$this->assertNull($options->value);
	}

	public function testDefaults(): void
	{
		$options = new OptionsQuery($query = 'site.children');
		$this->assertSame($query, $options->query);
		$this->assertNull($options->text);
		$this->assertNull($options->value);
	}

	public function testFactory(): void
	{
		$options = OptionsQuery::factory([
			'query' => $query = 'site.children',
			'text'  => $text = '{{ page.slug }}'
		]);

		$this->assertSame($query, $options->query);
		$this->assertSame($text, $options->text);
		$this->assertNull($options->value);

		$options = OptionsQuery::factory($query);
		$this->assertSame($query, $options->query);
	}

	public function testPolyfill(): void
	{
		$query = 'site.children';
		$this->assertSame(['query' => $query], OptionsQuery::polyfill($query));

		$query = ['query' => 'site.children'];
		$this->assertSame($query, OptionsQuery::polyfill($query));

		$query = ['fetch' => 'site.children'];
		$this->assertSame(['query' => 'site.children'], OptionsQuery::polyfill($query));
	}

	public function testResolveForArray(): void
	{
		$model   = new MyPage(['slug' => 'a']);
		$options = (new OptionsQuery(
			query: 'page.myArray',
			value: '{{ arrayItem.name }}',
		))->render($model);

		$this->assertSame('foo', $options[0]['value']);
		$this->assertSame('bar', $options[1]['value']);

		// since we didn't define a text query template,
		// the default `{{ arrayItem.value }}` is used
		// but our array doesn't have a a value key
		$this->assertSame('', $options[0]['text']);

		// with shorter alias
		$options = (new OptionsQuery(
			query: 'page.myArray',
			value: '{{ item.name }}',
		))->render($model);

		$this->assertSame('foo', $options[0]['value']);
		$this->assertSame('bar', $options[1]['value']);

		// with non-associative array
		$options = (new OptionsQuery(
			query: 'page.mySimpleArray',
			value: '{{ item.value }}',
			text:  '{{ item.value }}',
		))->render($model);

		$this->assertSame('tag1', $options[0]['value']);
		$this->assertSame('tag2', $options[1]['value']);
		$this->assertSame('tag3', $options[2]['value']);
		$this->assertSame('tag3', $options[2]['text']);
	}

	public function testResolveForStructure(): void
	{
		$model = new Page([
			'slug' => 'a',
			'content' => [
				'foo' => '
-
  name: foo
-
  name: bar'
			]
		]);

		$options = (new OptionsQuery(
			query: 'page.foo.toStructure',
			value: '{{ structureItem.name }}',
		))->render($model);

		$this->assertSame('foo', $options[0]['value']);
		$this->assertSame('bar', $options[1]['value']);

		// with shorter alias
		$options = (new OptionsQuery(
			query: 'page.foo.toStructure',
			value: '{{ item.name }}',
		))->render($model);

		$this->assertSame('foo', $options[0]['value']);
		$this->assertSame('bar', $options[1]['value']);
	}

	public function testResolveForBlock(): void
	{
		$model = new Page([
			'slug' => 'a',
			'content' => [
				'foo' => '[
					{ "type":"image", "content": { "headline": "foo" } },
					{ "type":"test", "content": { "headline": "bar" } }
				]'
			]
		]);

		$options = (new OptionsQuery(
			query: 'page.foo.toBlocks',
			text: '{{ block.type }}',
			value: '{{ block.headline }}',
		))->render($model);

		$this->assertSame('image', $options[0]['text']);
		$this->assertSame('foo', $options[0]['value']);
		$this->assertSame('test', $options[1]['text']);
		$this->assertSame('bar', $options[1]['value']);
	}

	public function testResolveForPages(): void
	{
		$app = new App([
			'site' => [
				'children' => [
					['slug' => 'a'],
					['slug' => 'b'],
					['slug' => 'c'],
				]
			]
		]);

		$options = new OptionsQuery(
			query: 'site.children',
			text: '{{ page.slug }}'
		);
		$options = $options->render($app->site());


		$this->assertSame('a', $options[0]['text']);
		$this->assertSame('a', $options[0]['value']);
		$this->assertSame('b', $options[1]['text']);
		$this->assertSame('c', $options[2]['text']);
	}

	public function testResolveForFile(): void
	{
		$app = new App([
			'site' => [
				'files' => [
					['filename' => 'a.jpg'],
					['filename' => 'b.pdf']
				]
			]
		]);

		$options = new OptionsQuery(
			query: 'site.files',
		);
		$options = $options->render($app->site());


		$this->assertSame('a.jpg', $options[0]['text']);
		$this->assertSame('b.pdf', $options[1]['text']);
	}

	public function testResolveForUser(): void
	{
		$app = new App([
			'users' => [
				['email' => 'homer@simpson.com', 'name' => 'homer']
			]
		]);

		$options = new OptionsQuery(
			query: 'kirby.users',
		);
		$options = $options->render($app->site());


		$this->assertSame('homer@simpson.com', $options[0]['value']);
		$this->assertSame('homer', $options[0]['text']);
	}

	public function testResolveForOptions(): void
	{
		$model   = new MyPage(['slug' => 'a']);
		$options = new OptionsQuery('page.myOptions');
		$options = $options->render($model);

		$this->assertSame('foo', $options[0]['text']);
		$this->assertSame('foo', $options[0]['value']);
		$this->assertSame('bar', $options[1]['text']);
		$this->assertSame('bar', $options[1]['value']);
	}

	public function testResolveInvalid(): void
	{
		$app = new App([
			'site' => [
				'content' => [
					'foo' => 'a'
				]
			]
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid query result data: Kirby\Content\Field');

		$options = (new OptionsQuery('site.foo'))->render($app->site());
	}

	public function testResolveHtmlEscape(): void
	{
		$model   = new MyPage(['slug' => 'a']);

		// text escaped by default
		$options = (new OptionsQuery(
			query: 'page.myHtmlArray',
			text: '{{ item.slogan }}',
			value: '{{ item.slogan }}',
		))->render($model);

		$this->assertSame('We are &lt;b&gt;great&lt;/b&gt;', $options[0]['text']);
		$this->assertSame('We are <b>great</b>', $options[0]['value']);
		$this->assertSame('We are &lt;b&gt;better&lt;/b&gt;', $options[1]['text']);
		$this->assertSame('We are <b>better</b>', $options[1]['value']);

		// text unescaped via {< >}
		$options = (new OptionsQuery(
			query: 'page.myHtmlArray',
			text: '{< item.slogan >}',
			value: '{{ item.slogan }}'
		))->render($model);
		$this->assertSame('We are <b>great</b>', $options[0]['text']);
		$this->assertSame('We are <b>great</b>', $options[0]['value']);
		$this->assertSame('We are <b>better</b>', $options[1]['text']);
		$this->assertSame('We are <b>better</b>', $options[1]['value']);

		// test unescaped with disabled safe mode
		$options = (new OptionsQuery(
			query: 'page.myHtmlArray',
			text: '{{ item.slogan }}',
			value: '{{ item.slogan }}'
		))->resolve($model, false)->render($model);
		$this->assertSame('We are <b>great</b>', $options[0]['text']);
		$this->assertSame('We are <b>great</b>', $options[0]['value']);
		$this->assertSame('We are <b>better</b>', $options[1]['text']);
		$this->assertSame('We are <b>better</b>', $options[1]['value']);
	}
}
