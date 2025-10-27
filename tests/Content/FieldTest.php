<?php

namespace Kirby\Content;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Field::class)]
class FieldTest extends TestCase
{
	public const string FIXTURES = __DIR__ . '/fixtures';
	public const string TMP      = KIRBY_TMP_DIR . '/Content.Field';

	public function setUp(): void
	{
		parent::setUp();

		new App([
			'roots' => [
				'index'   => static::TMP,
				'content' => static::FIXTURES
			]
		]);

		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		parent::tearDown();
		Dir::remove(static::TMP);
		App::destroy();
	}

	protected function field($value = '', $parent = null): Field
	{
		return new Field(
			key:   'test',
			value:  $value,
			parent: $parent
		);
	}

	public function test__debugInfo(): void
	{
		$field = $this->field('Title');
		$this->assertSame(['test' => 'Title'], $field->__debugInfo());
	}

	public function testExists(): void
	{
		$parent = new Page([
			'slug' => 'test',
			'content' => [
				'a' => 'Value A'
			]
		]);

		$this->assertTrue($parent->a()->exists());
		$this->assertFalse($parent->b()->exists());
	}

	public function testKey(): void
	{
		$field = $this->field('Foo');
		$this->assertSame('test', $field->key());
	}

	public function testModel(): void
	{
		$model = new Page(['slug' => 'test']);
		$field = $this->field(parent: $model);

		$this->assertSame($model, $field->model());
	}

	public function testParent(): void
	{
		$parent = new Page(['slug' => 'test']);
		$field  = $this->field(parent: $parent);

		$this->assertSame($parent, $field->parent());
	}

	public function testToArray(): void
	{
		$field = $this->field('Title');
		$this->assertSame(['test' => 'Title'], $field->toArray());
	}

	public function testValue(): void
	{
		$field = $this->field('Title');
		$this->assertSame('Title', $field->value());
	}

	public function testValueSetter(): void
	{
		$field = $this->field('Title');
		$this->assertSame('Title', $field->value());
		$field = $field->value('Modified');
		$this->assertSame('Modified', $field->value());
	}

	public function testValueCallbackSetter(): void
	{
		$field = $this->field('Title');
		$this->assertSame('Title', $field->value());
		$field = $field->value(fn ($value) => 'Modified');
		$this->assertSame('Modified', $field->value());
	}
}
