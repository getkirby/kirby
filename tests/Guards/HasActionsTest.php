<?php

namespace Kirby\Guards;

use Kirby\Cms\ModelTestCase;
use Kirby\Cms\Page;
use Kirby\Cms\User;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\DataProvider;

class HasActionsAbilities extends ModelAbilities
{
	protected function ensureToArchive(): void
	{
	}

	private function ensureToPrivateAction(): void
	{
	}
}

class HasActionsCustomAbilities extends PageAbilities
{
}

class HasActionsGuards extends PageGuards
{
	protected function ensureToArchive(): void
	{
	}

	private function ensureToPrivateAction(): void
	{
	}
}

class HasActionsPermissions extends ModelPermissions
{
	public function category(): string
	{
		return 'pages';
	}

	protected function ensureToArchive(): void
	{
	}

	private function ensureToPrivateAction(): void
	{
	}
}

class HasActionsValidators extends PageValidators
{
	protected function ensureToArchive(): void
	{
	}

	private function ensureToPrivateAction(): void
	{
	}
}

#[CoversTrait(HasActions::class)]
class HasActionsTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Guards.HasActions';

	public static function classProvider(): array
	{
		return [
			'abilities'   => [HasActionsAbilities::class],
			'guards'      => [HasActionsGuards::class],
			'permissions' => [HasActionsPermissions::class],
			'validators'  => [HasActionsValidators::class]
		];
	}

	protected function object(string $class): object
	{
		return new $class(
			model: new Page(['slug' => 'test']),
			user: new User(['id' => 'test'])
		);
	}

	#[DataProvider('classProvider')]
	public function testHas(string $class): void
	{
		$this->assertTrue($this->object($class)->has('archive'));
	}

	#[DataProvider('classProvider')]
	public function testHasWithDifferentCase(string $class): void
	{
		$object = $this->object($class);

		// permission rules are matched case-sensitively and a case
		// variant must therefore never resolve to an action method
		$this->assertTrue($object->has('archive'));
		$this->assertFalse($object->has('Archive'));
		$this->assertFalse($object->has('ARCHIVE'));
	}

	#[DataProvider('classProvider')]
	public function testHasWithPrivateMethod(string $class): void
	{
		// a private method cannot be called from the base class
		// and must therefore never be treated as an action
		$this->assertFalse($this->object($class)->has('privateAction'));
	}

	#[DataProvider('classProvider')]
	public function testHasWithSharedMethod(string $class): void
	{
		$object = $this->object($class);

		// methods of the abstract class that uses the trait
		// must never be treated as actions
		$this->assertFalse($object->has('check'));
		$this->assertFalse($object->has('has'));
	}

	#[DataProvider('classProvider')]
	public function testHasWithUndefinedAction(string $class): void
	{
		$this->assertFalse($this->object($class)->has('does-not-exist'));
	}

	public function testHasWithInheritedAction(): void
	{
		// actions of a core class are still found in a
		// custom child class that does not redeclare them
		$abilities = $this->object(HasActionsCustomAbilities::class);

		$this->assertTrue($abilities->has('changeSlug'));
		$this->assertFalse($abilities->has('changeslug'));
		$this->assertFalse($abilities->has('ChangeSlug'));
		$this->assertFalse($abilities->has('error'));
	}
}
