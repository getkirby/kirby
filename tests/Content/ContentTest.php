<?php

namespace Kirby\Content;

use Kirby\Cms\Page;
use Kirby\Exception\Exception;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Content::class)]
class ContentTest extends TestCase
{
	public function testCall()
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

	public function testData()
	{
		$content = new Content($data = [
			'a' => 'A',
			'b' => 'B'
		]);

		$this->assertSame($data, $content->data());
	}

	public function testFields()
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

	public function testGet()
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

	public function testGetWithoutKey()
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

	public function testHas()
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

	public function testKeys()
	{
		$content = new Content([
			'a' => 'A',
			'b' => 'B'
		]);

		$this->assertSame(['a', 'b'], $content->keys());
	}

	public function testKeysNormalized()
	{
		$content = new Content([
			'a' => 'A',
			'B' => 'B'
		]);

		$this->assertSame(['a', 'b'], $content->keys());
	}

	public function testKeysNotNormalized()
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

	public function testNot()
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

	public function testParent()
	{
		$parent  = new Page(['slug' => 'parent']);
		$content = new Content([
			'a' => 'A',
			'b' => 'B'
		], $parent);

		$this->assertSame($parent, $content->parent());
	}

	public function testParentWithoutValue()
	{
		$content = new Content([
			'a' => 'A',
			'b' => 'B'
		]);

		$this->assertNull($content->parent());
	}

	public function testSetParent()
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

	public function testToArray()
	{
		$content = new Content($data = [
			'a' => 'A',
			'b' => 'B'
		]);

		$this->assertSame($data, $content->toArray());
	}

	public function testUpdate()
	{
		$content = new Content([
			'a' => 'A',
			'b' => 'B'
		]);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('This is no longer functional. Please use `$model->version()->update()` instead');

		$content->update();
	}
}
