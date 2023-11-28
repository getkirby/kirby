<?php

namespace Kirby\Cms;

use IntlDateFormatter;
use Kirby\Data\Json;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Filesystem\F;
use Kirby\Http\Remote;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;
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
	protected const HISTORY = [
		'3' => '2019-02-05',
		'4' => '2023-11-28'
	];

	protected const SALT = 'kwAHMLyLPBnHEskzH9pPbJsBxQhKXZnX';

	// cache
	protected LicenseStatus $status;
	protected LicenseType $type;

	public function __construct(
		protected string|null $activation = null,
		protected string|null $code = null,
		protected string|null $domain = null,
		protected string|null $email = null,
		protected string|null $order = null,
		protected string|null $date = null,
		protected string|null $signature = null,
	) {
		// normalize the email address
		$this->email = $this->email === null ? null : $this->normalizeEmail($this->email);
	}

	/**
	 * Returns the activation date if available
	 */
	public function activation(string|IntlDateFormatter|null $format = null): int|string|null
	{
		return $this->activation !== null ? Str::date(strtotime($this->activation), $format) : null;
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
	 * Content for the license file
	 */
	public function content(): array
	{
		return [
			'activation' => $this->activation,
			'code'       => $this->code,
			'date'       => $this->date,
			'domain'     => $this->domain,
			'email'      => $this->email,
			'order'      => $this->order,
			'signature'  => $this->signature,
		];
	}

	/**
	 * Returns the purchase date if available
	 */
	public function date(string|IntlDateFormatter|null $format = null): int|string|null
	{
		return $this->date !== null ? Str::date(strtotime($this->date), $format) : null;
	}

	/**
	 * Returns the activation domain if available
	 */
	public function domain(): string|null
	{
		return $this->domain;
	}

	/**
	 * Returns the activation email if available
	 */
	public function email(): string|null
	{
		return $this->email;
	}

	/**
	 * Validates the email address of the license
	 */
	public function hasValidEmailAddress(): bool
	{
		return V::email($this->email) === true;
	}

	/**
	 * Hub address
	 */
	public static function hub(): string
	{
		return App::instance()->option('hub', 'https://hub.getkirby.com');
	}

	/**
	 * Checks for all required components of a valid license
	 */
	public function isComplete(): bool
	{
		if (
			$this->code !== null &&
			$this->date !== null &&
			$this->domain !== null &&
			$this->email !== null &&
			$this->order !== null &&
			$this->signature !== null &&
			$this->hasValidEmailAddress() === true &&
			$this->type() !== LicenseType::Invalid
		) {
			return true;
		}

		return false;
	}

	/**
	 * The license is still valid for the currently
	 * installed version, but it passed the 3 year period.
	 */
	public function isInactive(): bool
	{
		return $this->renewal() < time();
	}

	/**
	 * Checks for licenses beyond their 3 year period
	 */
	public function isLegacy(): bool
	{
		if ($this->type() === LicenseType::Legacy) {
			return true;
		}

		// without an activation date, the license
		// renewal cannot be evaluated and the license
		// has to be marked as expired
		if ($this->activation === null) {
			return true;
		}

		// get release date of current major version
		$major   = Str::before(App::instance()->version(), '.');
		$release = strtotime(static::HISTORY[$major] ?? '');

		// if there's no matching version in the history
		// rather throw an exception to avoid further issues
		// @codeCoverageIgnoreStart
		if ($release === false) {
			throw new InvalidArgumentException('The version for your license could not be found');
		}
		// @codeCoverageIgnoreEnd

		// If the renewal date is older than the version launch
		// date, the license is expired
		return $this->renewal() < $release;
	}

	/**
	 * Runs multiple checks to find out if the license is
	 * installed and verifiable
	 */
	public function isMissing(): bool
	{
		return
			$this->isComplete() === false ||
			$this->isOnCorrectDomain() === false ||
			$this->isSigned() === false;
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
		if ($this->normalizeDomain(App::instance()->system()->indexUrl()) !== $this->normalizeDomain($this->domain)) {
			return false;
		}

		return true;
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

		// verify the license signature
		$data      = json_encode($this->signatureData());
		$signature = hex2bin($this->signature);

		return openssl_verify($data, $signature, $pubKey, 'RSA-SHA256') === 1;
	}

	/**
	 * Returns a reliable label for the license type
	 */
	public function label(): string
	{
		if ($this->status() === LicenseStatus::Missing) {
			return LicenseType::Invalid->label();
		}

		return $this->type()->label();
	}

	/**
	 * Prepares the email address to be make sure it
	 * does not have trailing spaces and is lowercase.
	 */
	protected function normalizeEmail(string $email): string
	{
		return Str::lower(trim($email));
	}

	/**
	 * Prepares the domain to be comparable
	 */
	protected function normalizeDomain(string $domain): string
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
			'activation' => $license['activation'] ?? null,
			'code'       => $license['code']       ?? $license['license'] ?? null,
			'date'       => $license['date']  	   ?? null,
			'domain'     => $license['domain']     ?? null,
			'email'      => $license['email']      ?? null,
			'order'      => $license['order']      ?? null,
			'signature'  => $license['signature']  ?? null,
		];
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
			return new static();
		}

		return new static(...static::polyfill($license));
	}

	/**
	 * Sends a request to the hub to register the license
	 */
	public function register(): static
	{
		if ($this->type() === LicenseType::Invalid) {
			throw new InvalidArgumentException(['key' => 'license.format']);
		}

		if ($this->hasValidEmailAddress() === false) {
			throw new InvalidArgumentException(['key' => 'license.email']);
		}

		if ($this->domain === null) {
			throw new InvalidArgumentException(['key' => 'license.domain']);
		}

		// @codeCoverageIgnoreStart
		$response = $this->request('register', [
			'license' => $this->code,
			'email'   => $this->email,
			'domain'  => $this->domain
		]);

		return $this->update($response);
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Returns the renewal date
	 */
	public function renewal(string|IntlDateFormatter|null $format = null): int|string|null
	{
		if ($this->activation === null) {
			return null;
		}

		$time = strtotime('+3 years', $this->activation());
		return Str::date($time, $format);
	}

	/**
	 * Sends a hub request
	 */
	public function request(string $path, array $data): array
	{
		// @codeCoverageIgnoreStart
		$response = Remote::get(static::hub() . '/' . $path, [
			'data' => $data
		]);

		// handle request errors
		if ($response->code() !== 200) {
			$message = $response->json()['message'] ?? 'The request failed';

			throw new LogicException($message, $response->code());
		}

		return $response->json();
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Saves the license in the config folder
	 */
	public function save(): bool
	{
		if ($this->status() !== LicenseStatus::Active) {
			throw new InvalidArgumentException([
				'key' => 'license.verification'
			]);
		}

		// where to store the license file
		$file = App::instance()->root('license');

		// save the license information
		return Json::write($file, $this->content());
	}

	/**
	 * Returns the signature if available
	 */
	public function signature(): string|null
	{
		return $this->signature;
	}

	/**
	 * Creates the signature data array to compare
	 * with the signature in ::isSigned
	 */
	public function signatureData(): array
	{
		if ($this->type() === LicenseType::Legacy) {
			return [
				'license'    => $this->code,
				'order'      => $this->order,
				'email'      => hash('sha256', $this->email . static::SALT),
				'domain'     => $this->domain,
				'date'       => $this->date,
			];
		}

		return [
			'activation' => $this->activation,
			'code'       => $this->code,
			'date'       => $this->date,
			'domain'     => $this->domain,
			'email'      => hash('sha256', $this->email . static::SALT),
			'order'      => $this->order,
		];
	}

	/**
	 * Returns the license status as string
	 * This is used to build the proper UI elements
	 * for the license activation
	 */
	public function status(): LicenseStatus
	{
		return $this->status ??= match (true) {
			$this->isMissing()  === true => LicenseStatus::Missing,
			$this->isLegacy()   === true => LicenseStatus::Legacy,
			$this->isInactive() === true => LicenseStatus::Inactive,
			default                      => LicenseStatus::Active
		};
	}

	/**
	 * Detects the license type if the license key is available
	 */
	public function type(): LicenseType
	{
		return $this->type ??= LicenseType::detect($this->code);
	}

	/**
	 * Updates the license file
	 */
	public function update(array $data): static
	{
		// decode the response
		$data = static::polyfill($data);

		$this->activation = $data['activation'];
		$this->code       = $data['code'];
		$this->date       = $data['date'];
		$this->order      = $data['order'];
		$this->signature  = $data['signature'];

		// clear the caches
		unset($this->status, $this->type);

		// save the new state of the license
		$this->save();

		return $this;
	}

	/**
	 * Sends an upgrade request to the hub in order
	 * to either redirect to the upgrade form or
	 * sync the new license state
	 *
	 * @codeCoverageIgnore
	 */
	public function upgrade(): array
	{
		$response = $this->request('upgrade', [
			'domain'  => $this->domain,
			'email'   => $this->email,
			'license' => $this->code,
		]);

		// the license still needs an upgrade
		if (empty($response['url']) === false) {
			// validate the redirect URL
			if (Str::startsWith($response['url'], static::hub()) === false) {
				throw new Exception('We couldn’t redirect you to the Hub');
			}

			return [
				'status' => 'upgrade',
				'url'    => $response['url']
			];
		}

		// the license has already been upgraded
		// and can now be replaced
		$this->update($response);

		return [
			'status' => 'complete',
		];
	}
}
