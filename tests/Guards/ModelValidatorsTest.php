<?php

namespace Kirby\Guards;

use Kirby\Cms\ModelTestCase;
use Kirby\Cms\Page;
use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;

class ExtendedModelValidators extends ModelValidators
{
	protected function ensureToChangeTitle(string $title): void
	{
		$this->validateTitle($title);
	}

	public function validateTitle(string $title): void
	{
		if ($title === '') {
			$this->error(key: 'page.changeTitle.empty');
		}
	}
}

#[CoversClass(ModelValidators::class)]
class ModelValidatorsTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Guards.ModelValidators';

	protected function validators(): ExtendedModelValidators
	{
		return new ExtendedModelValidators(
			model: new Page(['slug' => 'test']),
			user: new User(['id' => 'test'])
		);
	}

	public function testEnsure(): void
	{
		$this->assertNull($this->validators()->ensure('changeTitle', 'New title'));
	}

	public function testEnsureWithFailingValidator(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.changeTitle.empty');

		$this->validators()->ensure('changeTitle', '');
	}

	public function testEnsureWithUndefinedAction(): void
	{
		// nothing to validate for this action
		$this->assertNull($this->validators()->ensure('does-not-exist'));
	}

	public function testError(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.slug.invalid');

		$this->validators()->error('page.slug.invalid');
	}

	public function testMay(): void
	{
		$this->assertTrue($this->validators()->may('changeTitle', 'New title'));
	}

	public function testMayWithFailingValidator(): void
	{
		$this->assertFalse($this->validators()->may('changeTitle', ''));
	}

	public function testErrorWithData(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Slug length must be less than "10" characters');

		$this->validators()->error(
			key: 'page.slug.maxlength',
			data: ['length' => 10]
		);
	}
}
