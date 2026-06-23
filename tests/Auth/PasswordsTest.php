<?php

namespace Kirby\Auth;

use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Passwords::class)]
class PasswordsTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Auth.Passwords';

	protected function assertValid(
		Passwords $policy,
		string $password
	): void {
		$this->assertTrue($policy->validate($password));
	}

	protected function assertInvalid(
		Passwords $policy,
		string $password,
		string $key
	): void {
		try {
			$policy->validate($password);
			$this->fail('Expected the password to be rejected');
		} catch (InvalidArgumentException $e) {
			$this->assertSame('error.' . $key, $e->getKey());
		}
	}

	public function testFactoryDefault(): void
	{
		$app    = new App(['roots' => ['index' => static::TMP]]);
		$policy = Passwords::factory($app);

		$this->assertValid($policy, '12345678');
		$this->assertInvalid($policy, '1234567', 'user.password.invalid');
	}

	public function testFactoryRegexShorthand(): void
	{
		$app = new App([
			'roots'   => ['index' => static::TMP],
			'options' => ['auth' => ['passwords' => '/^[a-z]{8,}$/']]
		]);

		$policy = Passwords::factory($app);
		$this->assertValid($policy, 'abcdefgh');
		$this->assertInvalid($policy, 'Abcdefgh', 'user.password.policy');
	}

	public function testFactoryPresetShorthand(): void
	{
		$app = new App([
			'roots'   => ['index' => static::TMP],
			'options' => ['auth' => ['passwords' => ['minlength' => 12]]]
		]);

		$policy = Passwords::factory($app);
		$this->assertSame(12, $policy->minlength());
		$this->assertInvalid($policy, '12345678901', 'user.password.minlength');
	}

	public function testFactoryCallableShorthand(): void
	{
		$app = new App([
			'roots'   => ['index' => static::TMP],
			'options' => [
				'auth' => [
					'passwords' => fn ($password) => str_contains($password, 'lizard')
				]
			]
		]);

		$policy = Passwords::factory($app);
		$this->assertValid($policy, 'lizard12');
		$this->assertInvalid($policy, 'abcdefgh', 'user.password.policy');
	}

	public function testFactoryExplicit(): void
	{
		$app = new App([
			'roots'   => ['index' => static::TMP],
			'options' => [
				'auth' => [
					'passwords' => [
						'rules' => '/^x/',
						'hint'  => 'Needs to start with x',
						'error' => 'Wrong start'
					]
				]
			]
		]);

		$policy = Passwords::factory($app);
		$this->assertSame('Needs to start with x', $policy->hint());

		try {
			$policy->validate('abcdefgh');
			$this->fail('Expected the password to be rejected');
		} catch (InvalidArgumentException $e) {
			$this->assertSame('Wrong start', $e->getMessage());
		}
	}

	public function testFail(): void
	{
		// generic policy error when a rule rejects
		// without a custom error message
		$policy = new Passwords(rules: '/^x/');
		$this->assertInvalid($policy, 'abcdefgh', 'user.password.policy');
	}

	public function testFailCustom(): void
	{
		// custom error message
		$policy = new Passwords(
			rules: '/^x/',
			error: 'I bet you did not put "lizard" in there'
		);

		try {
			$policy->validate('abcdefgh');
			$this->fail('Expected the password to be rejected');
		} catch (InvalidArgumentException $e) {
			$this->assertSame('I bet you did not put "lizard" in there', $e->getMessage());
		}

		// per locale
		$policy = new Passwords(
			rules: '/^x/',
			error: ['en' => 'Too weak', 'de' => 'Zu schwach']
		);

		try {
			$policy->validate('abcdefgh');
			$this->fail('Expected the password to be rejected');
		} catch (InvalidArgumentException $e) {
			$this->assertSame('Too weak', $e->getMessage());
		}
	}

	public function testHint(): void
	{
		// default
		$policy = new Passwords();
		$this->assertNull($policy->hint());

		// with preset rules
		$policy = new Passwords(rules: [
			'minlength' => 12,
			'uppercase' => true,
			'digits'    => true
		]);

		$this->assertSame(
			'Your password must contain: at least 12 characters, at least 1 uppercase letter(s), at least 1 digit(s)',
			$policy->hint()
		);

		// with count
		$policy = new Passwords(rules: ['digits' => 3]);

		$this->assertSame(
			'Your password must contain: at least 3 digit(s)',
			$policy->hint()
		);
	}

	public function testHintCustom(): void
	{
		$policy = new Passwords(rules: '/^x/', hint: 'Must start with x');
		$this->assertSame('Must start with x', $policy->hint());

		// per locale
		$policy = new Passwords(
			rules: '/^x/',
			hint: ['en' => 'English hint', 'de' => 'German hint']
		);
		$this->assertSame('English hint', $policy->hint());
	}

	public function testHintNoneForRegex(): void
	{
		$policy = new Passwords(rules: '/^x/');
		$this->assertNull($policy->hint());
	}

	public function testHintNoneForCallback(): void
	{
		$policy = new Passwords(rules: fn () => true);
		$this->assertNull($policy->hint());
	}

	public function testMinlength(): void
	{
		// default falls back to the absolute minimum
		$policy = new Passwords();
		$this->assertSame(8, $policy->minlength());

		// preset minlength
		$policy = new Passwords(rules: ['minlength' => 12]);
		$this->assertSame(12, $policy->minlength());

		// a preset below the absolute minimum is raised to it
		$policy = new Passwords(rules: ['minlength' => 4]);
		$this->assertSame(8, $policy->minlength());

		// preset rules without a minlength
		$policy = new Passwords(rules: ['digits' => true]);
		$this->assertSame(8, $policy->minlength());

		// regex and callback rules fall back to the absolute minimum
		$this->assertSame(8, (new Passwords(rules: '/^x/'))->minlength());
		$this->assertSame(8, (new Passwords(rules: fn () => true))->minlength());
	}

	public function testValidate(): void
	{
		$policy = new Passwords();

		// within the absolute bounds
		$this->assertValid($policy, '12345678');
		$this->assertValid($policy, str_repeat('a', 1000));

		// below the absolute minimum
		$this->assertInvalid($policy, '1234567', 'user.password.invalid');

		// above the absolute maximum
		$this->assertInvalid($policy, str_repeat('a', 1001), 'user.password.excessive');
	}

	public function testValidateCallback(): void
	{
		$policy = new Passwords(rules: fn ($password) => str_contains($password, 'lizard'));

		$this->assertValid($policy, 'lizard12');
		$this->assertInvalid($policy, 'abcdefgh', 'user.password.policy');
	}

	public function testValidateCallbackThatThrows(): void
	{
		$policy = new Passwords(rules: function ($password) {
			if (str_contains($password, 'lizard') === false) {
				throw new InvalidArgumentException(message: 'Needs a lizard');
			}

			return true;
		});

		$this->assertValid($policy, 'lizard12');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Needs a lizard');
		$policy->validate('abcdefgh');
	}

	public function testValidateCallbackReturningNull(): void
	{
		$policy = new Passwords(rules: function ($password): void {
		});

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Passwords rule callback must return boolean');
		$this->assertValid($policy, 'abcdefgh');
	}

	public function testValidatePresetMinlength(): void
	{
		$policy = new Passwords(rules: ['minlength' => 12]);
		$this->assertValid($policy, '123456789012');
		$this->assertInvalid($policy, '12345678901', 'user.password.minlength');

		// the absolute minimum is still enforced
		$policy = new Passwords(rules: ['minlength' => 4]);
		$this->assertInvalid($policy, 'short', 'user.password.invalid');
		$this->assertSame(8, $policy->minlength());
	}

	public function testValidatePresetMinlengthMessage(): void
	{
		$policy = new Passwords(rules: ['minlength' => 12]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('at least 12 characters long');
		$policy->validate('12345678901');
	}

	public function testValidatePresetMaxlength(): void
	{
		$policy = new Passwords(rules: ['maxlength' => 10]);

		$this->assertValid($policy, '1234567890');
		$this->assertInvalid($policy, '12345678901', 'user.password.maxlength');
	}

	public function testValidatePresetLowercase(): void
	{
		$policy = new Passwords(rules: ['lowercase' => true]);

		$this->assertValid($policy, 'ABCDEFGh');
		$this->assertInvalid($policy, 'ABCDEFGH', 'user.password.lowercase');
	}

	public function testValidatePresetUppercase(): void
	{
		$policy = new Passwords(rules: ['uppercase' => true]);

		$this->assertValid($policy, 'Abcdefgh');
		$this->assertInvalid($policy, 'abcdefgh', 'user.password.uppercase');
	}

	public function testValidatePresetDigits(): void
	{
		$policy = new Passwords(rules: ['digits' => true]);

		$this->assertValid($policy, 'abcdefg1');
		$this->assertInvalid($policy, 'abcdefgh', 'user.password.digits');
	}

	public function testValidatePresetSymbols(): void
	{
		$policy = new Passwords(rules: ['symbols' => true]);

		$this->assertValid($policy, 'abcdefg!');
		$this->assertInvalid($policy, 'abcdefgh', 'user.password.symbols');
	}

	public function testValidatePresetCount(): void
	{
		$policy = new Passwords(rules: ['digits' => 2]);

		$this->assertValid($policy, 'abcdef12');
		$this->assertInvalid($policy, 'abcdefg1', 'user.password.digits');
	}

	public function testValidatePresetCountTrueMeansOne(): void
	{
		$policy = new Passwords(rules: ['digits' => true]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('at least 1 digit(s)');
		$policy->validate('abcdefgh');
	}

	public function testValidatePresetCountMessage(): void
	{
		$policy = new Passwords(rules: ['symbols' => 2]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('at least 2 symbol(s)');
		$policy->validate('abcdefg!');
	}

	public function testValidatePresetCountDisabled(): void
	{
		$this->assertValid(new Passwords(rules: ['digits' => 0]), 'abcdefgh');
		$this->assertValid(new Passwords(rules: ['digits' => false]), 'abcdefgh');
	}

	public function testValidatePresetUnicode(): void
	{
		$lowercase = new Passwords(rules: ['lowercase' => true]);
		$this->assertValid($lowercase, 'ÄÖÜÄÖÜäÄ');
		$this->assertInvalid($lowercase, 'ÄÖÜÄÖÜÄÖ', 'user.password.lowercase');

		$uppercase = new Passwords(rules: ['uppercase' => true]);
		$this->assertValid($uppercase, 'äöüäöüÄö');
		$this->assertInvalid($uppercase, 'äöüäöüäö', 'user.password.uppercase');
	}

	public function testValidatePresetFirstFailureWins(): void
	{
		// minlength is checked before the character classes
		$policy = new Passwords(rules: [
			'minlength' => 10,
			'digits'    => true,
			'symbols'   => true
		]);
		$this->assertInvalid($policy, 'abcdefgh', 'user.password.minlength');

		// digits is checked before symbols
		$policy = new Passwords(rules: ['digits' => true, 'symbols' => true]);
		$this->assertInvalid($policy, 'abcdefghij', 'user.password.digits');
	}

	public function testValidatePresetCombined(): void
	{
		$policy = new Passwords(rules: [
			'minlength' => 10,
			'uppercase' => true,
			'lowercase' => true,
			'digits'    => true,
			'symbols'   => true
		]);

		$this->assertValid($policy, 'Abcdefg1!2');
	}

	public function testValidateRegex(): void
	{
		$policy = new Passwords(rules: '/^[a-z]{8,}$/');

		$this->assertValid($policy, 'abcdefgh');
		$this->assertInvalid($policy, 'Abcdefgh', 'user.password.policy');
	}

	public function testValidateRegexAbsoluteBounds(): void
	{
		// the 8-char floor applies even if the regex would allow shorter
		$policy = new Passwords(rules: '/^[a-z]+$/');
		$this->assertInvalid($policy, 'abc', 'user.password.invalid');
	}

	public function testValidateRegexInvalidPattern(): void
	{
		$policy = new Passwords(rules: '/[/');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The password policy regex is invalid');
		$policy->validate('abcdefgh');
	}
}
