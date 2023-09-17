<?php
/**
 * Trait AuthenticatorOptionsTrait
 *
 * @created      07.03.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\Authenticator;

use chillerlan\Authenticator\Authenticators\AuthenticatorInterface;
use InvalidArgumentException;
use function in_array;
use function strtolower;
use function strtoupper;

trait AuthenticatorOptionsTrait{

	/**
	 * Code length: either 6 or 8
	 */
	protected int $digits = 6;

	/**
	 * Validation period (seconds): 15 - 60
	 */
	protected int $period = 30;

	/**
	 * Length of the secret phrase (bytes, unencoded binary)
	 *
	 * @see \random_bytes()
	 */
	protected int $secret_length = 20;

	/**
	 * Hash algorithm:
	 *
	 *   - `AuthenticatorInterface::ALGO_SHA1`
	 *   - `AuthenticatorInterface::ALGO_SHA256`
	 *   - `AuthenticatorInterface::ALGO_SHA512`
	 */
	protected string $algorithm = AuthenticatorInterface::ALGO_SHA1;

	/**
	 * Authenticator mode:
	 *
	 *   - `AuthenticatorInterface::HOTP`  = counter based
	 *   - `AuthenticatorInterface::TOTP`  = time based
	 *   - `AuthenticatorInterface::STEAM` = time based (Steam Guard)
	 */
	protected string $mode = AuthenticatorInterface::TOTP;

	/**
	 * Number of allowed adjacent codes
	 */
	protected int $adjacent = 1;

	/**
	 * A fixed time offset that will be added to the current time value
	 *
	 * @see \chillerlan\Authenticator\Authenticators\AuthenticatorInterface::getCounter()
	 */
	protected int $time_offset = 0;

	/**
	 * Whether to use local time or request server time from the API
	 *
	 * This can be useful when the device time sync is unreliable.
	 *
	 * note: API requests needs ext-curl installed
	 */
	protected bool $useLocalTime = true;

	/**
	 * Whether to force refreshing server time on each call or use the time returned from the last request
	 */
	protected bool $forceTimeRefresh = false;

	/**
	 * Sets the code length to either 6 or 8
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function set_digits(int $digits):void{

		if(!in_array($digits, [6, 8], true)){
			throw new InvalidArgumentException('Invalid code length: '.$digits);
		}

		$this->digits = $digits;
	}

	/**
	 * Sets the period to a value between 15 and 60 seconds
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function set_period(int $period):void{

		if($period < 15 || $period > 60){
			throw new InvalidArgumentException('Invalid period: '.$period);
		}

		$this->period = $period;
	}

	/**
	 * Sets the hash algorithm
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function set_algorithm(string $algorithm):void{
		$algorithm = strtoupper($algorithm);

		if(!in_array($algorithm, AuthenticatorInterface::HASH_ALGOS, true)){
			throw new InvalidArgumentException('Invalid algorithm: '.$algorithm);
		}

		$this->algorithm = $algorithm;
	}

	/**
	 * Sets the authenticator mode
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function set_mode(string $mode):void{
		$mode = strtolower($mode);

		if(!isset(AuthenticatorInterface::MODES[$mode])){
			throw new InvalidArgumentException('Invalid mode: '.$mode);
		}

		$this->mode = $mode;
	}

	/**
	 * Sets the adjacent amount
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function set_adjacent(int $adjacent):void{

		if($adjacent < 0){
			throw new InvalidArgumentException('Invalid adjacent: '.$adjacent);
		}

		$this->adjacent = $adjacent;
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	protected function set_secret_length(int $secret_length):void{
		// ~ 80 to 640 bits
		if($secret_length < 16 || $secret_length > 1024){
			throw new InvalidArgumentException('Invalid secret length: '.$secret_length);
		}

		$this->secret_length = $secret_length;
	}

}
