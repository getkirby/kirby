<?php

namespace Kirby\Blueprint;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FieldsRegistry::class)]
class FieldsRegistryTest extends TestCase
{
	public function testConstruct(): void
	{
		$fields = new FieldsRegistry([
			'alpha' => ['type' => 'text'],
			'beta'  => false
		]);

		// the props are claimed as they are and only normalized later
		$this->assertSame(
			['alpha' => ['type' => 'text'], 'beta' => false],
			$fields->toArray()
		);
	}

	public function testAdd(): void
	{
		$fields = new FieldsRegistry();

		$added = $fields->add([
			'alpha' => ['type' => 'text'],
			'beta'  => ['type' => 'date']
		]);

		$this->assertSame(['alpha', 'beta'], array_keys($added));
		$this->assertSame(['type' => 'text'], $fields->get('alpha'));
		$this->assertSame(['type' => 'date'], $fields->get('beta'));
	}

	public function testAddWithDuplicateName(): void
	{
		$fields = new FieldsRegistry();

		$fields->add(['alpha' => ['type' => 'text']]);
		$added = $fields->add(['alpha' => ['type' => 'textarea']]);

		// the first field keeps the name it claimed
		$this->assertSame(['alpha-duplicate-1'], array_keys($added));
		$this->assertSame('text', $fields->get('alpha')['type']);

		$error = $fields->get('alpha-duplicate-1');

		$this->assertSame('info', $error['type']);
		$this->assertSame('negative', $error['theme']);
		$this->assertSame(
			'The field <strong>"alpha"</strong> already exists in your blueprint',
			$error['text']
		);
	}

	public function testAddWithDuplicateNameInDifferentCase(): void
	{
		$fields = new FieldsRegistry();

		$fields->add(['Alpha' => ['type' => 'text']]);

		// names are lowercased further down the line, so `Alpha`
		// and `alpha` are the same field
		$added = $fields->add(['alpha' => ['type' => 'textarea']]);

		$this->assertSame(['alpha-duplicate-1'], array_keys($added));
		$this->assertSame('text', $fields->get('Alpha')['type']);
	}

	public function testAddWithTakenErrorName(): void
	{
		$fields = new FieldsRegistry();

		$fields->add([
			'alpha'             => ['type' => 'text'],
			'alpha-duplicate-1' => ['type' => 'date']
		]);

		// the error skips the name that is already taken by a real field
		$added = $fields->add(['alpha' => ['type' => 'textarea']]);

		$this->assertSame(['alpha-duplicate-2'], array_keys($added));
		$this->assertSame('date', $fields->get('alpha-duplicate-1')['type']);
	}

	public function testGet(): void
	{
		$fields = new FieldsRegistry(['Alpha' => ['type' => 'text']]);

		$this->assertSame(['type' => 'text'], $fields->get('Alpha'));
		$this->assertSame(['type' => 'text'], $fields->get('alpha'));
		$this->assertNull($fields->get('beta'));
	}

	public function testHas(): void
	{
		$fields = new FieldsRegistry(['Alpha' => ['type' => 'text']]);

		$this->assertTrue($fields->has('Alpha'));
		$this->assertTrue($fields->has('alpha'));
		$this->assertFalse($fields->has('beta'));
	}

	public function testToArray(): void
	{
		$fields = new FieldsRegistry();

		$this->assertSame([], $fields->toArray());

		$fields->add(['alpha' => ['type' => 'text']]);

		$this->assertSame(['alpha' => ['type' => 'text']], $fields->toArray());
	}
}
