<?php
/**
 * Class TOTP
 *
 * @created      15.06.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 */

namespace chillerlan\Authenticator\Authenticators;

use function floor;
use function hash_equals;
use function time;

/**
 * @link https://tools.ietf.org/html/rfc6238
 */
class TOTP extends HOTP{

	/**
	 * @inheritDoc
	 */
	public function getCounter(int $data = null):int{
		$data ??= time();

		if($this->options->useLocalTime === false){
			$data = $this->getServerTime();
		}

		return (int)floor(($data + $this->options->time_offset) / $this->options->period);
	}

	/**
	 * @inheritDoc
	 */
	public function verify(string $otp, int $data = null):bool{
		$limit = $this->options->adjacent;

		if($limit === 0){
			return parent::verify($otp, $data); // @codeCoverageIgnore
		}

		$timeslice = $this->getCounter($data);
		// phpcs:ignore
		for($i = -$limit; $i <= $limit; $i++){
			$hash = $this->getHMAC($timeslice + $i);
			$code = $this->getOTP($this->getCode($hash));

			if(hash_equals($code, $otp)){
				return true;
			}
		}

		return false;
	}

}
