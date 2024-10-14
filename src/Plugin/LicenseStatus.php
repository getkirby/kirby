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
	 * Returns a status by its name
	 */
	public static function from(LicenseStatus|string|null $status): static
	{
		if ($status instanceof LicenseStatus) {
			return $status;
		}

		$status   = SystemLicenseStatus::from($status ?? 'unknown');
		$status ??= SystemLicenseStatus::Unknown;

		return new static(
			value: $status->value,
			icon: $status->icon(),
			label: $status->label(),
			theme: $status->theme()
		);
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