<?php

namespace Kirby\Content;

use Kirby\Cms\Page;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Content::class)]
class ContentTest extends TestCase
{
	public function testCall(): void
	{
		$content = new Content([
			'a' => 'A',
			'B' => 'B',
			'MiXeD' => 'mixed',
			'mIXeD' => 'MIXED'
		]);

		$this->assertSame('a', $content->a()->key());
		$this->assertSame('A', $content->a()->value());
		$this->assertSame('mixed', $content->mixed()->key());
		$this->assertSame('MIXED', $content->mixed()->value());
		$this->assertSame('mixed', $content->mIXEd()->key());
		$this->assertSame('MIXED', $content->mIXEd()->value());
	}

	public function testData(): void
	{
		$content = new Content($data = [
			'a' => 'A',
			'b' => 'B'
		]);

		$this->assertSame($data, $content->data());
	}

	public function testFields(): void
	{
		$content = new Content([
			'a' => 'A',
			'b' => 'B'
		]);

		$fields = $content->fields();

		$this->assertCount(2, $fields);
		$this->assertSame('A', $fields['a']->value());
		$this->assertSame('B', $fields['b']->value());
	}

	public function testGet(): void
	{
		$content = new Content([
			'a' => 'A',
			'b' => 'B'
		]);

		$this->assertSame('A', $content->get('a')->value());
		$this->assertSame('A', $content->get('A')->value());
		$this->assertSame('B', $content->get('b')->value());
		$this->assertSame('B', $content->get('B')->value());

		$this->assertSame(null, $content->get('C')->value(), 'Non-existing field should have a null value');
	}

	public function testGetWithoutKey(): void
	{
		$content = new Content([
			'a' => 'A',
			'b' => 'B'
		]);

		$fields = $content->get();

		$this->assertCount(2, $fields);
		$this->assertSame('A', $fields['a']->value());
		$this->assertSame('B', $fields['b']->value());
	}

	public function testHas(): void
	{
		$content = new Content([
			'a' => 'A',
			'b' => 'B'
		]);

		$this->assertTrue($content->has('a'));
		$this->assertTrue($content->has('A'));
		$this->assertTrue($content->has('b'));
		$this->assertTrue($content->has('B'));
		$this->assertFalse($content->has('c'));
		$this->assertFalse($content->has('C'));
	}

	public function testKeys(): void
	{
		$content = new Content([
			'a' => 'A',
			'b' => 'B'
		]);

		$this->assertSame(['a', 'b'], $content->keys());
	}

	public function testKeysNormalized(): void
	{
		$content = new Content([
			'a' => 'A',
			'B' => 'B'
		]);

		$this->assertSame(['a', 'b'], $content->keys());
	}

	public function testKeysNotNormalized(): void
	{
		$content = new Content(
			data: [
				'a' => 'A',
				'B' => 'B'
			],
			normalize: false
		);

		$this->assertSame(['a', 'B'], $content->keys());
	}

	public function testNot(): void
	{
		$content = new Content([
			'a' => 'A',
			'B' => 'B'
		]);

		$copy = $content->not('b');

		$this->assertNotSame($content, $copy);

		$this->assertSame(['a', 'b'], $content->keys());
		$this->assertSame(['a'], $copy->keys());
	}

	public function testParent(): void
	{
		$parent  = new Page(['slug' => 'parent']);
		$content = new Content([
			'a' => 'A',
			'b' => 'B'
		], $parent);

		$this->assertSame($parent, $content->parent());
	}

	public function testParentWithoutValue(): void
	{
		$content = new Content([
			'a' => 'A',
			'b' => 'B'
		]);

		$this->assertNull($content->parent());
	}

	public function testSetParent(): void
	{
		$parentA = new Page(['slug' => 'parent-a']);
		$parentB = new Page(['slug' => 'parent-b']);

		$content = new Content([
			'a' => 'A',
			'b' => 'B'
		], $parentA);

		$this->assertSame($parentA, $content->parent());

		$content->setParent($parentB);

		$this->assertSame($parentB, $content->parent());
	}

	public function testToArray(): void
	{
		$content = new Content($data = [
			'a' => 'A',
			'b' => 'B'
		]);

		$this->assertSame($data, $content->toArray());
	}

	public function testUpdate(): void
	{
		$content = new Content([
			'a' => 'A',
			'b' => 'B'
		]);

		$content->update([
			'a' => 'aaa'
		]);

		$this->assertSame('aaa', $content->get('a')->value());

		$content->update([
			'miXED' => 'mixed!'
		]);

		$this->assertSame('mixed!', $content->get('mixed')->value());

		// Field objects should be cleared on update
		$content->update([
			'a' => 'aaaaaa'
		]);

		$this->assertSame('aaaaaa', $content->get('a')->value());

		$content->update($expected = [
			'TEST' => 'TEST'
		], true);

		$this->assertSame(['test' => 'TEST'], $content->data());

		$content->update(null, true);

		$this->assertSame([], $content->data());
	}
}
