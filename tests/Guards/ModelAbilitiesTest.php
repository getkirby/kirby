<?php

namespace Kirby\Guards;

use Kirby\Cms\ModelTestCase;
use Kirby\Cms\User;
use Kirby\Exception\AbilityException;
use PHPUnit\Framework\Attributes\CoversClass;

class ExtendedModelAbilities extends ModelAbilities
{
	protected function ensureToDelete(): void
	{
	}

	protected function ensureToUpdate(): void
	{
		$this->error('error.test.update');
	}
}

#[CoversClass(ModelAbilities::class)]
class ModelAbilitiesTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Guards.ModelAbilities';

	protected function abilities(): ExtendedModelAbilities
	{
		return new ExtendedModelAbilities(
			model: $this->app->site(),
			user: $this->user()
		);
	}

	public function testEnsure(): void
	{
		$this->assertNull($this->abilities()->ensure('delete'));
	}

	public function testEnsureWithFailingAbility(): void
	{
		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.test.update');

		$this->abilities()->ensure('update');
	}

	public function testEnsureWithSharedMethod(): void
	{
		// shared methods of the abstract class must never
		// be called as an action
		$this->assertNull($this->abilities()->ensure('error'));
		$this->assertNull($this->abilities()->ensure('has'));
	}

	public function testEnsureWithUndefinedAction(): void
	{
		$this->assertNull($this->abilities()->ensure('does-not-exist'));
	}

	public function testMay(): void
	{
		$this->assertTrue($this->abilities()->may('delete'));
	}

	public function testMayWithFailingAbility(): void
	{
		$this->assertFalse($this->abilities()->may('update'));
	}

	public function testError(): void
	{
		$abilities = $this->abilities();

		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.test');

		$abilities->error('error.test');
	}

	protected function user(): User
	{
		return new User(['id' => 'test']);
	}
}
