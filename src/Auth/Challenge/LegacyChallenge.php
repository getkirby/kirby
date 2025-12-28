<?php

namespace Kirby\Auth\Challenge;

use Kirby\Auth\Challenge;
use Kirby\Cms\User;
use SensitiveParameter;

/**
 * @package   Kirby Cms
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @deprecated
 */
class LegacyChallenge extends Challenge
{
	public function __construct(
		protected string $class,
		User $user,
		string $mode,
		int $timeout
	) {
		parent::__construct($user, $mode, $timeout);
	}

	public function create(array $options): null
	{
		return $this->class::create($this->user, $options);
	}

	public static function isAvailable(User $user, string $mode): bool
	{
		return true;
	}

	public function verify(
		#[SensitiveParameter]
		string $code
	): bool {
		return $this->class::verify($this->user, $code);
	}
}
