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
	/**
	 * The license is valid and active
	 */
	case Active   = 'active';

	/**
	 * The free feature period of
     * the license is over.
	 */
	case Inactive = 'inactive';

	/**
	 * The installation has an old
     * license (v1, v2, v3)
	 */
	case Legacy = 'legacy';

	/**
     * The installation has no license or
     * the license cannot be validated
	 */
	case Missing = 'missing';

	/**
	 * Returns the dialog according to the status
	 */
	public function dialog(): string
	{
		return match($this) {
			static::Missing => 'registration',
			default         => 'license'
		};
	}

	/**
	 * Returns the icon according to the status
	 */
	public function icon(): string
	{
		return match ($this) {
			static::Missing  => 'key',
			static::Legacy   => 'alert',
			static::Inactive => 'clock',
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
			static::Missing,
			static::Legacy   => 'love',
			static::Inactive => 'love',
			static::Active   => 'positive',
		};
	}

	public function value(): string
	{
		return $this->value;
	}

}
