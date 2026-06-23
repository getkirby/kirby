<?php

namespace Kirby\Auth;

use Closure;
use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;
use SensitiveParameter;

/**
 * Validates passwords against the policy that is
 * configured via the `auth.passwords` option
 * The policy can be defined as a set of preset rules,
 * a regular expression or a custom callback.
 *
 * @example
 * // preset rules
 * 'auth' => [
 *   'passwords' => [
 *     'minlength' => 12,
 *     'uppercase' => true,
 *     'lowercase' => true,
 *     'digits'    => 2,
 *     'symbols'   => true
 *   ]
 * ];
 *
 * // regular expression with a custom hint
 * 'auth' => [
 *   'passwords' => [
 *     'rules' => '/^(?=.*\d)(?=.*[!@#$%^&*]).{16,}$/',
 *     'hint'  => 'At least 16 characters, one digit and one symbol'
 *   ]
 * ];
 *
 * // custom callback (return false or throw to reject)
 * 'auth' => [
 *   'passwords' => function (string $password): bool {
 *     return str_contains($password, 'lizard');
 *   }
 * ];
 *
 * // validate manually, e.g. in a frontend registration form
 * $kirby->auth()->passwords()->validate($password);
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class Passwords
{
	/**
	 * Absolute minimum length that is enforced
	 */
	public const MINLENGTH = 8;

	/**
	 * Absolute maximum length that is enforced;
	 * longer passwords can cause DoS attacks and
	 * are therefore blocked in the auth system
	 */
	public const MAXLENGTH = 1000;

	/**
	 * @param $rules Preset rules, a regex or a callback
	 * @param $hint Hint text or per-locale array of hints
	 * @param $error Custom error message or per-locale array
	 */
	public function __construct(
		protected array|string|Closure|null $rules = null,
		protected string|array|null $hint = null,
		protected string|array|null $error = null
	) {
	}

	/**
	 * Normalizes a character rule value to the
	 * minimum number of required occurrences:
	 * `true` requires 1, an integer requires that many,
	 * `false`/`0`/anything else disable the rule
	 */
	protected static function count(mixed $rule): int
	{
		if ($rule === true) {
			return 1;
		}

		if (is_int($rule) === true && $rule > 0) {
			return $rule;
		}

		return 0;
	}

	/**
	 * Creates an instance from the `auth.passwords` option
	 */
	public static function factory(App $kirby): static
	{
		$config = $kirby->option('auth.passwords');

		// explicit form: an array that carries the rules
		// under a `rules` key (next to `hint` and `error`)
		if (
			is_array($config) === true &&
			isset($config['rules']) === true
		) {
			return new static(
				rules: $config['rules'],
				hint:  $config['hint'] ?? null,
				error: $config['error'] ?? null
			);
		}

		// shorthand: a regex string, a preset rules array or a callback
		return new static(rules: $config);
	}

	/**
	 * Throws the configured custom error or a generic
	 * policy error for the regex and callback modes
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	protected function fail(): never
	{
		// a custom error message (string or per-locale array)
		if ($this->error !== null) {
			throw new InvalidArgumentException(
				message: I18n::translate($this->error, $this->error)
			);
		}

		throw new InvalidArgumentException(key: 'user.password.policy');
	}

	/**
	 * Returns the hint text that describes the policy:
	 * the configured hint or one auto-generated from the
	 * preset rules; `null` if there is nothing to show
	 */
	public function hint(): string|null
	{
		// explicitly configured hint (string or per-locale array)
		if ($this->hint !== null) {
			return I18n::translate($this->hint, $this->hint);
		}

		if (is_array($this->rules) === false) {
			return null;
		}

		// auto-generate a hint from the preset rules
		$requirements = [];

		if (isset($this->rules['minlength']) === true) {
			$requirements[] = I18n::template(
				'user.password.hint.minlength',
				['min' => $this->rules['minlength']]
			);
		}

		foreach (['uppercase', 'lowercase', 'digits', 'symbols'] as $rule) {
			if (($count = static::count($this->rules[$rule] ?? false)) > 0) {
				$requirements[] = I18n::template(
					'user.password.hint.' . $rule,
					['count' => $count]
				);
			}
		}

		if ($requirements === []) {
			return null;
		}

		return I18n::template(
			'user.password.hint.intro',
			['requirements' => implode(', ', $requirements)]
		);
	}

	/**
	 * Returns the effective minimum length
	 */
	public function minlength(): int
	{
		if (
			is_array($this->rules) === true &&
			isset($this->rules['minlength']) === true
		) {
			return max(
				(int)$this->rules['minlength'],
				static::MINLENGTH
			);
		}

		return static::MINLENGTH;
	}

	/**
	 * Validates a password against the policy
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public function validate(
		#[SensitiveParameter]
		string $password
	): true {
		// absolute bounds, enforced in every mode
		if (Str::length($password) < static::MINLENGTH) {
			throw new InvalidArgumentException(
				key: 'user.password.invalid'
			);
		}

		if (Str::length($password) > static::MAXLENGTH) {
			throw new InvalidArgumentException(
				key: 'user.password.excessive'
			);
		}

		match (true) {
			is_string($this->rules)         => $this->validateRegex($this->rules, $password),
			$this->rules instanceof Closure => $this->validateCallback($this->rules, $password),
			is_array($this->rules)          => $this->validatePresets($this->rules, $password),
			default                         => null
		};

		return true;
	}

	/**
	 * Runs a custom validation callback. The callback may
	 * throw its own exception, while a `false` return value
	 * triggers the generic policy error
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	protected function validateCallback(
		Closure $rules,
		#[SensitiveParameter]
		string $password
	): void {
		$result = $rules($password);

		if (is_bool($result) === false) {
			throw new LogicException(
				message: 'Passwords rule callback must return boolean'
			);
		}

		if ($rules($password) === false) {
			$this->fail();
		}
	}

	/**
	 * Validates against the preset rules in a fixed order.
	 * The first unmet rule throws its specific error.
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	protected function validatePresets(
		array $rules,
		#[SensitiveParameter]
		string $password
	): void {
		if (
			isset($rules['minlength']) === true &&
			Str::length($password) < $rules['minlength']
		) {
			throw new InvalidArgumentException(
				key:  'user.password.minlength',
				data: ['min' => $rules['minlength']]
			);
		}

		if (
			isset($rules['maxlength']) === true &&
			Str::length($password) > $rules['maxlength']
		) {
			throw new InvalidArgumentException(
				key:  'user.password.maxlength',
				data: ['max' => $rules['maxlength']]
			);
		}

		$patterns = [
			'lowercase' => '/\p{Ll}/u',
			'uppercase' => '/\p{Lu}/u',
			'digits'    => '/\p{Nd}/u',
			'symbols'   => '/[^\p{L}\p{Nd}]/u'
		];

		foreach ($patterns as $rule => $pattern) {
			$count = static::count($rules[$rule] ?? false);

			if (
				$count > 0 &&
				preg_match_all($pattern, $password) < $count
			) {
				throw new InvalidArgumentException(
					key:  'user.password.' . $rule,
					data: ['count' => $count]
				);
			}
		}
	}

	/**
	 * Validates against a regular expression
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	protected function validateRegex(
		string $regex,
		#[SensitiveParameter]
		string $password
	): void {
		$result = @preg_match($regex, $password);

		if ($result === false) {
			throw new InvalidArgumentException(
				message: 'The password policy regex is invalid'
			);
		}

		if ($result !== 1) {
			$this->fail();
		}
	}
}
