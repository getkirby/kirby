<?php

namespace Kirby\Cms;

use IntlDateFormatter;
use Kirby\Data\Json;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\F;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;
use Throwable;

/**
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class License
{
	public const ACTIVE   = 'active';
	public const EXPIRED  = 'expired';
	public const INVALID  = 'invalid';
	public const OUTDATED = 'outdated';

	protected const HISTORY = [
		'3' => '2019-02-05',
		'4' => '2023-12-01'
	];

	// cache
	protected string|null $status = null;

	public function __construct(
		protected string|null $activated = null,
		protected string|null $code = null,
		protected string|null $domain = null,
		protected string|null $email = null,
		protected string|null $order = null,
		protected string|null $purchased = null,
		protected string|null $signature = null,
	) {
	}

	/**
	 * Returns the activation date if available
	 */
	public function activated(string|IntlDateFormatter|null $format = null): int|string|null
	{
		return $this->activated !== null ? Str::date(strtotime($this->activated), $format) : null;
	}

	/**
	 * Returns the license code if available
	 */
	public function code(bool $obfuscated = false): string|null
	{
		if ($this->code !== null && $obfuscated === true) {
 			return Str::substr($this->code, 0, 10) . str_repeat('X', 22);
		}

		return $this->code;
	}

	/**
	 * Returns the dialog according to the status
	 */
	public function dialog(): string
	{
		return match ($this->status()) {
			'invalid' => 'registration',
			default   => 'license'
		};
	}

	/**
	 * Returns the activated domain if available
	 */
	public function domain(): string|null
	{
		return $this->domain;
	}

	/**
	 * Returns the activated email if available
	 */
	public function email(): string|null
	{
		return $this->email;
	}

	/**
	 * Returns the icon according to the status
	 */
	public function icon(): string
	{
		return match ($this->status()) {
			static::INVALID  => 'key',
			static::EXPIRED  => 'alert',
			static::OUTDATED => 'clock',
			static::ACTIVE   => 'check',
		};
	}

	public function info(): string
	{
		return I18n::translate('license.status.' . $this->status() . '.info');
	}

	/**
	 * Checks for all required components of a valid license
	 */
	public function isComplete(): bool
	{
		if (
			$this->code === null ||
			$this->domain === null ||
			$this->email === null ||
			$this->order === null ||
			$this->purchased === null ||
			$this->signature === null
		) {
			return false;
		}

		return true;
	}

	/**
	 * The license is no longer valid for the currently
	 * installed version and needs to be renewed
	 */
	public function isExpired(): bool
	{
		// without an activation date, the license
		// renewal cannot be evaluated and the license
		// has to be marked as expired
		if ($this->activated === null) {
			return true;
		}

		// get the major version number
		$versionNumber = Str::before(App::instance()->version(), '.');
		$versionDate   = strtotime(static::HISTORY[$versionNumber] ?? '');

		// if there's no matching version in the history
		// rather throw an exception to avoid further issues
		if ($versionDate === false) {
			throw new InvalidArgumentException('The version for your license could not be found');
		}

		// If the renewal date is older than the version launch
		// date, the license is expired
		return $this->renewal() < $versionDate;
	}

	/**
	 * Checks for Kirby 3 licenses
	 */
	public function isLegacy(): bool
	{
		return $this->type() === 'Kirby 3';
	}

	/**
	 * Checks if the license is on the correct domain
	 */
	public function isOnCorrectDomain(): bool
	{
		if ($this->domain === null) {
			return false;
		}

		// compare domains
		if ($this->sanitizeDomain(App::instance()->system()->indexUrl()) !== $this->sanitizeDomain($this->domain)) {
			return false;
		}

		return true;
	}

	/**
	 * The license is still valid for the currently
	 * installed version, but it passed the 3 year period.
	 */
	public function isOutdated(): bool
	{
		return $this->renewal() < time();
	}

	/**
	 * Compares the signature with all ingredients
	 */
	public function isSigned(): bool
	{
		if ($this->signature === null) {
			return false;
		}

		// get the public key
		$pubKey = F::read(App::instance()->root('kirby') . '/kirby.pub');

		// build the license verification data
		$data = [
			'activated' => $this->activated,
			'code'      => $this->code,
			'domain'    => $this->domain,
			'email'     => hash('sha256', $this->email . 'kwAHMLyLPBnHEskzH9pPbJsBxQhKXZnX'),
			'order'     => $this->order,
			'purchased' => $this->purchased,
		];

		// legacy licenses need a different payload for their signature
		if ($this->isLegacy() === true) {
			$data = [
				'license' => $data['code'],
				'order'   => $data['order'],
				'email'   => $data['email'],
				'domain'  => $data['domain'],
				'date'    => $data['purchased'],
			];
		}

		// verify the license signature
		$data      = json_encode($data);
		$signature = hex2bin($this->signature);

		if (openssl_verify($data, $signature, $pubKey, 'RSA-SHA256') !== 1) {
			return false;
		}

		return true;
	}

	/**
	 * Runs multiple checks to find out if the license is valid
	 */
	public function isValid(): bool
	{
		return
			$this->isComplete() === true &&
			$this->isOnCorrectDomain() === true &&
			$this->isSigned() === true;
	}

	public function label(): string
	{
		return I18n::translate('license.status.' . $this->status() . '.label');
	}

	/**
	 * Returns the order id if available
	 */
	public function order(): string|null
	{
		return $this->order;
	}

	/**
	 * Support the old license file dataset
	 * from older licenses
	 */
	public static function polyfill(array $license): array
	{
		return [
			'activated' => $license['activated'] ?? null,
			'code'      => $license['code']      ?? $license['license'] ?? null,
			'domain'    => $license['domain']    ?? null,
			'email'     => $license['email']     ?? null,
			'order'     => $license['order']     ?? null,
			'purchased' => $license['purchased'] ?? $license['date'] ?? null,
			'signature' => $license['signature'] ?? null,
		];
	}

	/**
	 * Returns the purchase date if available
	 */
	public function purchased(string|IntlDateFormatter|null $format = null): int|string|null
	{
		return $this->purchased !== null ? Str::date(strtotime($this->purchased), $format) : null;
	}

	/**
	 * Reads the license file in the config folder
	 * and creates a new license instance for it.
	 */
	public static function read(): static
	{
		try {
			$license = Json::read(App::instance()->root('license'));
		} catch (Throwable) {
			return new static;
		}

		return new static(...static::polyfill($license));
	}

	/**
	 * Returns the renewal date
	 */
	public function renewal(string|IntlDateFormatter|null $format = null): int|string|null
	{
		if ($this->activated === null) {
			return null;
		}

		$time = strtotime('+3 years', $this->activated());
		return Str::date($time, $format);
	}

	/**
	 * Prepares the domain to be comparable
	 */
	protected function sanitizeDomain(string $domain): string
	{
		// remove common "testing" subdomains as well as www.
		// to ensure that installations of the same site have
		// the same license URL; only for installations at /,
		// subdirectory installations are difficult to normalize
		if (Str::contains($domain, '/') === false) {
			if (Str::startsWith($domain, 'www.')) {
				return substr($domain, 4);
			}

			if (Str::startsWith($domain, 'dev.')) {
				return substr($domain, 4);
			}

			if (Str::startsWith($domain, 'test.')) {
				return substr($domain, 5);
			}

			if (Str::startsWith($domain, 'staging.')) {
				return substr($domain, 8);
			}
		}

		return $domain;
	}

	/**
	 * Returns the signature if available
	 */
	public function signature(): string|null
	{
		return $this->signature;
	}

	/**
	 * Returns the license status as string
     * This is used to build the proper UI elements
     * for the license activation
	 */
	public function status(): string
	{
		return $this->status ??= match (true) {
			$this->isValid() === false
				=> static::INVALID,
			$this->isExpired() === true
				=> static::EXPIRED,
			$this->isOutdated() === true
				=> static::OUTDATED,
			default
				=> static::ACTIVE
		};

		// $status = match (true) {
		// 	$this->isExpired() => [
		// 		'icon'    => 'alert',
		// 		'label'   => I18n::translate('license.status.expired.short'),
		// 		'message' => I18n::translate('license.status.expired'),
		// 		'renew'   => true,
		// 		'theme'   => 'negative'
		// 	],
		// 	$this->isOutdated() => [
		// 		'icon'    => 'clock',
		// 		'label'   => I18n::translate('license.status.outdated.short'),
		// 		'message' => I18n::translate('license.status.outdated'),
		// 		'renew'   => true,
		// 		'theme'   => 'notice'
		// 	],
		// 	default => [
		// 		'icon'    => 'check',
		// 		'label'   => I18n::translate('license.status.valid.short'),
		// 		'message' => I18n::translate('license.status.valid'),
		// 		'renew'   => false,
		// 		'theme'   => 'positive',
		// 	]
		// };

		// $status['date'] = $this->renewal('Y-m-d');

		// return $status;
	}

	/**
	 * Returns the theme according to the status
	 */
	public function theme(): string
	{
		return match ($this->status()) {
			static::INVALID  => 'negative',
			static::EXPIRED  => 'negative',
			static::OUTDATED => 'notice',
			static::ACTIVE   => 'positive',
		};
	}

	/**
	 * Detects the license type if the license key is available
	 */
	public function type(): string|null
	{
		return match (true) {
			$this->code === null
				=> I18n::translate('license.unregistered.label'),

			Str::startsWith($this->code, 'K3-')
				=> 'Kirby 3',

			Str::startsWith($this->code, 'K-ENT')
				=> 'Kirby Enterprise',

			Str::startsWith($this->code, 'K-BAS')
				=> 'Kirby Basic',
		};
	}

}
