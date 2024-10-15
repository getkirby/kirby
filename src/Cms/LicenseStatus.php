<?php

namespace Kirby\Cms;

use Kirby\Toolkit\I18n;

/**
 * @package   Kirby Cms
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @codeCoverageIgnore
 */
enum LicenseStatus: string
{
	/**
	 * The license is valid and active
	 */
	case Active = 'active';

	/**
	 * Only used for the demo instance
	 */
	case Demo = 'demo';

	/**
	 * The included updates period of
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
	 * The license status is unknown
	 */
	case Unknown = 'unknown';

	/**
	 * Checks if the license can be saved when it
	 * was entered in the activation dialog;
	 * renewable licenses are accepted as well
	 * to allow renewal from the Panel
	 */
	public function activatable(): bool
	{
		return match ($this) {
			static::Active,
			static::Inactive,
			static::Legacy    => true,
			default           => false
		};
	}

	/**
	 * Returns the dialog according to the status
	 */
	public function dialog(): string|null
	{
		return match ($this) {
			static::Demo    => null,
			static::Missing => 'registration',
			default         => 'license'
		};
	}

	/**
	 * Returns the icon according to the status.
	 * The icon is used for the system view and
	 * in the license dialog.
	 */
	public function icon(): string
	{
		return match ($this) {
			static::Active   => 'check',
			static::Demo     => 'preview',
			static::Inactive => 'clock',
			static::Legacy   => 'alert',
			static::Missing  => 'key',
			static::Unknown  => 'question',
		};
	}

	/**
	 * The info text is shown in the license dialog
	 * in the status row.
	 */
	public function info(string|null $end = null): string
	{
		return I18n::template('license.status.' . $this->value . '.info', ['date' => $end]);
	}

	/**
	 * Label for the system view
	 */
	public function label(): string
	{
		return I18n::translate('license.status.' . $this->value . '.label');
	}

	/**
	 * Checks if the license can be renewed
	 * The license dialog will show the renew
	 * button in this case and redirect to the hub
	 */
	public function renewable(): bool
	{
		return match ($this) {
			static::Active,
			static::Demo => false,
			default      => true
		};
	}

	/**
	 * Returns the theme according to the status
	 * The theme is used for the label in the system
	 * view and the status icon in the license dialog.
	 */
	public function theme(): string
	{
		return match ($this) {
			static::Active   => 'positive',
			static::Demo     => 'notice',
			static::Inactive => 'notice',
			static::Legacy   => 'negative',
			static::Missing  => 'love',
			static::Unknown  => 'passive',
		};
	}

	/**
	 * Returns the status as string value
	 */
	public function value(): string
	{
		return $this->value;
	}
}
