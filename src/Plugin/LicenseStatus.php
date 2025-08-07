<?php

namespace Kirby\Plugin;

use Kirby\Cms\LicenseStatus as SystemLicenseStatus;
use Stringable;

/**
 * Represents the license status of a plugin.
 * Used to display the status in the Panel system view
 *
 * @package   Kirby Plugin
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 */
class LicenseStatus implements Stringable
{
	public function __construct(
		protected string $value,
		protected string $icon,
		protected string $label,
		protected string|null $link = null,
		protected string|null $dialog = null,
		protected string|null $drawer = null,
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
	 * Returns the status dialog
	 */
	public function dialog(): string|null
	{
		return $this->dialog;
	}

	/**
	 * Returns the status drawer
	 */
	public function drawer(): string|null
	{
		return $this->drawer;
	}

	/**
	 * Returns a status by its name
	 */
	public static function from(LicenseStatus|string|array|null $status): static
	{
		if ($status instanceof LicenseStatus) {
			return $status;
		}

		if (is_array($status) === true) {
			return new static(...$status);
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
	 * Returns the status link
	 */
	public function link(): string|null
	{
		return $this->link;
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
			'dialog' => $this->dialog(),
			'drawer' => $this->drawer(),
			'icon'   => $this->icon(),
			'label'  => $this->label(),
			'link'   => $this->link(),
			'theme'  => $this->theme(),
			'value'  => $this->value(),
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
