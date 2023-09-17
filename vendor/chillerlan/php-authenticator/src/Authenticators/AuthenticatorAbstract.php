<?php
/**
 * Class AuthenticatorAbstract
 *
 * @created      25.06.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 */

namespace chillerlan\Authenticator\Authenticators;

use chillerlan\Authenticator\AuthenticatorOptions;
use chillerlan\Authenticator\Common\Base32;
use chillerlan\Settings\SettingsContainerInterface;
use InvalidArgumentException;
use RuntimeException;
use function random_bytes;
use function time;
use function trim;

/**
 *
 */
abstract class AuthenticatorAbstract implements AuthenticatorInterface{

	protected const userAgent = 'chillerlanAuthenticator/5.0 +https://github.com/chillerlan/php-authenticator';

	/** @var \chillerlan\Settings\SettingsContainerInterface|\chillerlan\Authenticator\AuthenticatorOptions */
	protected SettingsContainerInterface $options;
	protected ?string                    $secret          = null;
	protected int                        $serverTime      = 0;
	protected int                        $lastRequestTime = 0;

	/**
	 * AuthenticatorInterface constructor
	 */
	public function __construct(SettingsContainerInterface $options = null){
		// phpcs:ignore
		$this->setOptions($options ?? new AuthenticatorOptions);
	}

	/**
	 * @inheritDoc
	 */
	public function setOptions(SettingsContainerInterface $options):AuthenticatorInterface{
		$this->options = $options;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function setSecret(string $encodedSecret):AuthenticatorInterface{
		$this->secret = Base32::decode($this->checkEncodedSecret($encodedSecret));

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getSecret():string{

		if($this->secret === null){
			throw new RuntimeException('No secret set');
		}

		return Base32::encode($this->secret);
	}

	/**
	 * @inheritDoc
	 */
	public function createSecret(int $length = null):string{
		$length ??= $this->options->secret_length;

		if($length < 16){
			throw new InvalidArgumentException('Invalid secret length: '.$length);
		}

		$this->secret = random_bytes($length);

		return $this->getSecret();
	}

	/**
	 * @inheritDoc
	 */
	public function getServertime():int{
		return time();
	}

	/**
	 * Get an adjusted time stamp for the given server time
	 */
	protected function getAdjustedTime(int $serverTime, int $lastRequestTime):int{
		$diff = (time() - $lastRequestTime);

		return ($serverTime + $diff);
	}

	/**
	 * Checks if the encoded secret is non-empty, returns the trimmed string on success
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function checkEncodedSecret(string $encodedSecret):string{
		$encodedSecret = trim($encodedSecret);

		if($encodedSecret === ''){
			throw new InvalidArgumentException('The given secret string is empty');
		}

		return $encodedSecret;
	}

}
