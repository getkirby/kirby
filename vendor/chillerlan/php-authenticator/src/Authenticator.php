<?php
/**
 * Class Authenticator
 *
 * @created      24.11.2015
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\Authenticator;

use chillerlan\Authenticator\Authenticators\AuthenticatorInterface;
use chillerlan\Settings\SettingsContainerInterface;
use InvalidArgumentException;
use function http_build_query;
use function rawurlencode;
use function sprintf;
use function trim;
use const PHP_QUERY_RFC3986;

/**
 * Yet another Google authenticator implementation!
 *
 * @link https://tools.ietf.org/html/rfc4226
 * @link https://tools.ietf.org/html/rfc6238
 * @link https://github.com/google/google-authenticator
 * @link https://openauthentication.org/specifications-technical-resources/
 * @link https://blog.ircmaxell.com/2014/11/its-all-about-time.html
 */
class Authenticator{

	/** @var \chillerlan\Settings\SettingsContainerInterface|\chillerlan\Authenticator\AuthenticatorOptions */
	protected SettingsContainerInterface $options;
	protected AuthenticatorInterface     $authenticator;
	protected string                     $mode = AuthenticatorInterface::TOTP;

	/**
	 * Authenticator constructor
	 */
	public function __construct(SettingsContainerInterface $options = null, string $secret = null){
		// phpcs:ignore
		$this->setOptions($options ?? new AuthenticatorOptions);

		if($secret !== null){
			$this->setSecret($secret);
		}

	}

	/**
	 * Sets an options instance and invokes an authenticator according to the given mode
	 *
	 * Please note that this will reset the secret phrase stored with the authenticator instance
	 * if a different mode than the current is given.
	 */
	public function setOptions(SettingsContainerInterface $options):self{
		$this->options = $options;

		// invoke a new authenticator interface if necessary
		if(!isset($this->authenticator) || $this->options->mode !== $this->mode){
			$class               = AuthenticatorInterface::MODES[$this->options->mode];
			$this->mode          = $this->options->mode;
			$this->authenticator = new $class;
		}

		$this->authenticator->setOptions($this->options);

		return $this;
	}

	/**
	 * Sets a secret phrase from a Base32 representation
	 *
	 * @codeCoverageIgnore
	 */
	public function setSecret(string $encodedSecret):self{
		$this->authenticator->setSecret($encodedSecret);

		return $this;
	}

	/**
	 * Returns a Base32 representation of the current secret phrase
	 *
	 * @codeCoverageIgnore
	 */
	public function getSecret():string{
		return $this->authenticator->getSecret();
	}

	/**
	 * Generates a new (secure random) secret phrase
	 *
	 * @codeCoverageIgnore
	 */
	public function createSecret(int $length = null):string{
		return $this->authenticator->createSecret($length);
	}

	/**
	 * Creates a new OTP code with the given secret
	 *
	 * $data may be
	 *  - a UNIX timestamp (TOTP)
	 *  - a counter value (HOTP)
	 *
	 * @codeCoverageIgnore
	 */
	public function code(int $data = null):string{
		return $this->authenticator->code($data);
	}

	/**
	 * Checks the given $code against the secret
	 *
	 * $data may be
	 *  - a UNIX timestamp (TOTP)
	 *  - a counter value (HOTP)
	 *
	 * @codeCoverageIgnore
	 */
	public function verify(string $otp, int $data = null):bool{
		return $this->authenticator->verify($otp, $data);
	}

	/**
	 * Creates a URI for use in QR codes for example
	 *
	 * @link https://github.com/google/google-authenticator/wiki/Key-Uri-Format#parameters
	 *
	 * @throws \InvalidArgumentException
	 */
	public function getUri(string $label, string $issuer, int $hotpCounter = null, bool $omitSettings = null):string{
		$label  = trim($label);
		$issuer = trim($issuer);

		if(empty($label) || empty($issuer)){
			throw new InvalidArgumentException('$label and $issuer cannot be empty');
		}

		$values = [
			'secret' => $this->authenticator->getSecret(),
			'issuer' => $issuer,
		];

		if($omitSettings !== true){
			$values['digits']    = $this->options->digits;
			$values['algorithm'] = $this->options->algorithm;

			if($this->mode === AuthenticatorInterface::TOTP){
				$values['period'] = $this->options->period;
			}

			if($this->mode === AuthenticatorInterface::HOTP && $hotpCounter !== null){
				$values['counter'] = $hotpCounter;
			}
		}

		$values = http_build_query($values, '', '&', PHP_QUERY_RFC3986);

		return sprintf('otpauth://%s/%s?%s', $this->mode, rawurlencode($label), $values);
	}

}
