<?php

namespace Kirby\Cms;

use Kirby\Toolkit\I18n;

/**
 * @package   Kirby Cms
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
enum LicenseStatus: string
{
	case Active   = 'active';
	case Expired  = 'expired';
	case Invalid  = 'invalid';
	case Outdated = 'outdated';

	/**
	 * Returns the dialog according to the status
	 */
	public function dialog(): string
	{
		return match($this) {
			static::Invalid => 'registration',
			default         => 'license'
		};
	}

	/**
	 * Returns the icon according to the status
	 */
	public function icon(): string
	{
		return match ($this) {
			static::Invalid  => 'key',
			static::Expired  => 'alert',
			static::Outdated => 'clock',
			static::Active   => 'check',
		};
	}

	public function info(): string
	{
		return I18n::translate('license.status.' . $this->value . '.info');
	}

	public function label(): string
	{
		return I18n::translate('license.status.' . $this->value . '.label');
	}

	/**
	 * Returns the theme according to the status
	 */
	public function theme(): string
	{
		return match ($this) {
			static::Invalid,
			static::Expired  => 'negative',
			static::Outdated => 'notice',
			static::Active   => 'positive',
		};
	}

	public function value(): string
	{
		return $this->value;
	}

}
