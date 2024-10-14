<?php

namespace Kirby\Plugin;

use Kirby\Cms\LicenseStatus as SystemLicenseStatus;
use Stringable;

/**
 * License Status
 *
 * @package   Kirby Plugin
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class LicenseStatus implements Stringable
{
	public function __construct(
		protected string $value,
		protected string $icon,
		protected string $label,
		protected string|null $theme = null
	) {
	}

	/**
	 * Returns the status label
	 */
	public function __toString(): string
	{
		return $this->label();
	}

	/**
	 * Returns predefined statuses
	 */
	public static function defaults(): array
	{
		return [
			'active' => new static(
				value: 'active',
				icon: SystemLicenseStatus::Active->icon(),
				label: SystemLicenseStatus::Active->label(),
				theme: SystemLicenseStatus::Active->theme()
			),
			'demo' => new static(
				value: 'demo',
				icon: SystemLicenseStatus::Demo->icon(),
				label: SystemLicenseStatus::Demo->label(),
				theme: SystemLicenseStatus::Demo->theme()
			),
			'inactive' => new static(
				value: 'inactive',
				icon: SystemLicenseStatus::Inactive->icon(),
				label: SystemLicenseStatus::Inactive->label(),
				theme: SystemLicenseStatus::Inactive->theme()
			),
			'legacy' => new static(
				value: 'legacy',
				icon: SystemLicenseStatus::Legacy->icon(),
				label: SystemLicenseStatus::Legacy->label(),
				theme: SystemLicenseStatus::Legacy->theme()
			),
			'missing' => new static(
				value: 'missing',
				icon: SystemLicenseStatus::Missing->icon(),
				label: SystemLicenseStatus::Missing->label(),
				theme: SystemLicenseStatus::Missing->theme()
			),
			'unknown' => new static(
				value: 'unknown',
				icon: 'question',
				label: 'Unknown license',
				theme: 'passive'
			),
		];
	}

	/**
	 * Returns a status by its name
	 */
	public static function from(LicenseStatus|string|null $status): static
	{
		if ($status instanceof LicenseStatus) {
			return $status;
		}

		if ($status === null) {
			return static::defaults()['unknown'];
		}

		return static::defaults()[$status] ?? static::defaults()['unknown'];
	}

	/**
	 * Returns the status icon
	 */
	public function icon(): string
	{
		return $this->icon;
	}

	/**
	 * Returns the status label
	 */
	public function label(): string
	{
		return $this->label;
	}

	/**
	 * Returns the theme
	 */
	public function theme(): string|null
	{
		return $this->theme;
	}

	/**
	 * Returns the status information as an array
	 */
	public function toArray(): array
	{
		return [
			'icon'  => $this->icon(),
			'label' => $this->label(),
			'theme' => $this->theme(),
			'value' => $this->value(),
		];
	}

	/**
	 * Returns the status value
	 */
	public function value(): string
	{
		return $this->value;
	}
}
