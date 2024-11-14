<?php

namespace Kirby\Plugin;

use Closure;
use Stringable;

/**
 * License
 *
 * @package   Kirby Plugin
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class License implements Stringable
{
	protected LicenseStatus $status;

	public function __construct(
		protected Plugin $plugin,
		protected string $name,
		protected string|null $link = null,
		LicenseStatus|null $status = null
	) {
		$this->status = $status ?? LicenseStatus::from('unknown');
	}

	/**
	 * Returns the string representation of the license
	 */
	public function __toString(): string
	{
		return $this->name();
	}

	/**
	 * Creates a license instance from a given value
	 */
	public static function from(
		Plugin $plugin,
		Closure|array|string|null $license
	): static {
		if ($license instanceof Closure) {
			return $license($plugin);
		}

		if (is_array($license)) {
			return new static(
				plugin: $plugin,
				name: $license['name'] ?? '',
				link: $license['link'] ?? null,
				status: LicenseStatus::from($license['status'] ?? 'active')
			);
		}

		if ($license === null || $license === '-') {
			return new static(
				plugin: $plugin,
				name: '-',
				status: LicenseStatus::from('unknown')
			);
		}

		return new static(
			plugin: $plugin,
			name: $license,
			status: LicenseStatus::from('active')
		);
	}

	/**
	 * Get the license link. This can be the
	 * license terms or a link to a shop to
	 * purchase a license.
	 */
	public function link(): string|null
	{
		return $this->link;
	}

	/**
	 * Get the license name
	 */
	public function name(): string
	{
		return $this->name;
	}

	/**
	 * Get the license status
	 */
	public function status(): LicenseStatus
	{
		return $this->status;
	}

	/**
	 * Returns the license information as an array
	 */
	public function toArray(): array
	{
		return [
			'link'   => $this->link(),
			'name'   => $this->name(),
			'status' => $this->status()->toArray()
		];
	}
}
