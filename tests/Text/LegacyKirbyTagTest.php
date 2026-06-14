<?php

namespace Kirby\Text;

use Kirby\Cms\App;
use Kirby\Exception\BadMethodCallException;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LegacyKirbyTag::class)]
class LegacyKirbyTagTest extends TestCase
{
	protected function setUp(): void
	{
		KirbyTag::$types = [
			'test' => [
				'attr' => ['a', 'b'],
				'html' => fn ($tag) => 'test: ' . $tag->value . '-' . $tag->a . '-' . $tag->b
			],
			'noHtml' => [
				'attr' => ['a', 'b']
			],
			'invalidHtml' => [
				'attr' => ['a', 'b'],
				'html' => 'some string'
			],
		];
	}

	protected function tearDown(): void
	{
		KirbyTag::$aliases = [];
		KirbyTag::$types   = [];
		App::destroy();
	}

	public function testFactory(): void
	{
		$tag = KirbyTag::factory('test', 'test value', [
			'a' => 'attrA',
			'b' => 'attrB'
		]);

		$this->assertInstanceOf(LegacyKirbyTag::class, $tag);
		$this->assertSame('test', $tag->type);
		$this->assertSame('test value', $tag->value);
		$this->assertSame(['a' => 'attrA', 'b' => 'attrB'], $tag->attrs);
	}

	public function testValueAvailableUnderTypeName(): void
	{
		// the tag value is also exposed under the type name (e.g. $tag->test)
		$tag = KirbyTag::factory('test', 'test value');
		$this->assertSame('test value', $tag->test);
	}

	public function testDefinedAttrs(): void
	{
		// only attributes declared in the definition are applied as props
		$tag = KirbyTag::factory('test', 'value', [
			'a' => 'attrA',
			'b' => 'attrB',
			'c' => 'attrC' // not in the definition -> ignored
		]);

		$this->assertSame('attrA', $tag->a);
		$this->assertSame('attrB', $tag->b);
		$this->assertNull($tag->c);

		// but all passed attributes are still available in $attrs
		$this->assertSame(
			['a' => 'attrA', 'b' => 'attrB', 'c' => 'attrC'],
			$tag->attrs
		);
	}

	public function test__get(): void
	{
		$tag = KirbyTag::factory('test', 'test value', ['a' => 'attrA']);

		// known attribute
		$this->assertSame('attrA', $tag->a);

		// case-insensitive lookup (attribute name gets lowercased)
		$this->assertSame('attrA', $tag->A);

		// unknown attribute returns null
		$this->assertNull($tag->unknown);
	}

	public function test__set(): void
	{
		$tag = KirbyTag::factory('test', 'test value');

		$tag->text = 'My Text';
		$this->assertSame('My Text', $tag->text);

		// overwriting should also work
		$tag->text = 'Other Text';
		$this->assertSame('Other Text', $tag->text);

		// case-insensitive properties
		$tag->Caption = 'My Caption';
		$this->assertSame('My Caption', $tag->caption);
		$this->assertSame('My Caption', $tag->Caption);
		$this->assertSame('My Caption', $tag->attr('Caption'));
		$this->assertSame('My Caption', $tag->attr('caption'));
	}

	public function test__isset(): void
	{
		$tag = KirbyTag::factory('test', 'test value', ['a' => 'attrA']);

		$this->assertTrue(isset($tag->a));
		// case-insensitive
		$this->assertTrue(isset($tag->A));
		$this->assertFalse(isset($tag->unknown));
	}

	public function test__unset(): void
	{
		$tag = KirbyTag::factory('test', 'test value', ['a' => 'attrA']);

		$this->assertTrue(isset($tag->a));

		unset($tag->a);
		$this->assertFalse(isset($tag->a));
		$this->assertNull($tag->a);
	}

	public function test__call(): void
	{
		$attr = [
			'a' => 'attrA',
			'b' => 'attrB'
		];

		$data = [
			'a' => 'dataA',
			'c' => 'dataC'
		];

		$tag = KirbyTag::factory('test', 'test value', $attr, $data);

		// data takes precedence over the property
		$this->assertSame('dataA', $tag->a());
		// falls back to the magic property
		$this->assertSame('attrB', $tag->b());
		// data-only value
		$this->assertSame('dataC', $tag->c());
	}

	public function testAttr(): void
	{
		$tag = KirbyTag::factory('test', 'test value', ['a' => 'attrA']);

		$this->assertSame('attrA', $tag->attr('a'));
		$this->assertSame('attrA', $tag->attr('A'));
		$this->assertSame('fallback', $tag->attr('b', 'fallback'));
	}

	public function testRender(): void
	{
		$tag = KirbyTag::factory('test', 'test value', [
			'a' => 'attrA',
			'b' => 'attrB'
		]);
		$this->assertSame('test: test value-attrA-attrB', $tag->render());

		$tag = KirbyTag::factory('test', '', ['a' => 'attrA']);
		$this->assertSame('test: -attrA-', $tag->render());
	}

	public function testRenderMissingHtml(): void
	{
		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('Invalid tag render function in tag: noHtml');

		$tag = KirbyTag::factory('noHtml', 'test value', [
			'a' => 'attrA',
			'b' => 'attrB'
		]);
		$tag->render();
	}

	public function testRenderInvalidHtml(): void
	{
		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('Invalid tag render function in tag: invalidHtml');

		$tag = KirbyTag::factory('invalidHtml', 'test value', [
			'a' => 'attrA',
			'b' => 'attrB'
		]);
		$tag->render();
	}
}
