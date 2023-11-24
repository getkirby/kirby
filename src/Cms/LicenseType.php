<?php

namespace Kirby\Cms;

use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

/**
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @codeCoverageIgnore
 */
enum LicenseType: string
{
	/**
	 * New basic licenses
	 */
	case Basic = 'basic';

	/**
	 * New enterprise licenses
	 */
	case Enterprise = 'enterprise';

	/**
	 * Invalid license codes
	 */
	case Invalid = 'invalid';

	/**
	 * Old Kirby 3 licenses
	 */
	case Legacy = 'legacy';

	/**
	 * Detects the correct LicenseType based on the code
	 */
	public static function detect(string|null $code): static
	{
		return match (true) {
			static::Basic->isValidCode($code)      => static::Basic,
			static::Enterprise->isValidCode($code) => static::Enterprise,
			static::Legacy->isValidCode($code)     => static::Legacy,
			default                                => static::Invalid
		};
	}

	/**
	 * Checks for a valid license code
	 * by prefix and length. This is just a
	 * rough validation.
	 */
	public function isValidCode(string|null $code): bool
	{
		return
			$code !== null &&
			Str::length($code) === $this->length() &&
			Str::startsWith($code, $this->prefix()) === true;
	}

	/**
	 * The expected lengths of the license code
	 */
	public function length(): int
	{
		return match ($this) {
			static::Basic      => 38,
			static::Enterprise => 38,
			static::Legacy     => 39,
			static::Invalid    => 0,
		};
	}

	/**
	 * A human-readable license type label
	 */
	public function label(): string
	{
		return match ($this) {
			static::Basic      => 'Kirby Basic',
			static::Enterprise => 'Kirby Enterprise',
			static::Legacy     => 'Kirby 3',
			static::Invalid    => I18n::translate('license.unregistered.label'),
		};
	}

	/**
	 * The expected prefix for the license code
	 */
	public function prefix(): string|null
	{
		return match ($this) {
			static::Basic      => 'K-BAS-',
			static::Enterprise => 'K-ENT-',
			static::Legacy     => 'K3-PRO-',
			static::Invalid    => null,
		};
	}

	/**
	 * Returns the enum value
	 */
	public function value(): string
	{
		return $this->value;
	}
}
