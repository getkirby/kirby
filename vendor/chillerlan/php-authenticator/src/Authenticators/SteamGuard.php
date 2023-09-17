<?php
/**
 * Class SteamGuard
 *
 * @created      20.06.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 *
 * @noinspection PhpComposerExtensionStubsInspection
 */

namespace chillerlan\Authenticator\Authenticators;

use chillerlan\Authenticator\Common\Base64;
use RuntimeException;
use function curl_close;
use function curl_exec;
use function curl_getinfo;
use function curl_init;
use function curl_setopt_array;
use function floor;
use function intdiv;
use function json_decode;
use function sprintf;
use function time;
use const CURLOPT_HTTPHEADER;
use const CURLOPT_POST;
use const CURLOPT_POSTFIELDS;
use const CURLOPT_RETURNTRANSFER;

/**
 * /data/data/com.valvesoftware.android.steam.community/f/Steamguard-STEAMID64
 *
 * @see https://help.steampowered.com/en/faqs/view/7EFD-3CAE-64D3-1C31
 * @see https://github.com/SoftCreatR/php-steam-guard
 */
final class SteamGuard extends TOTP{

	private const steamCodeChars = '23456789BCDFGHJKMNPQRTVWXY';
	private const steamTimeURL   = 'https://api.steampowered.com/ITwoFactorService/QueryTime/v0001';

	/**
	 * @inheritDoc
	 */
	public function setSecret(string $encodedSecret):AuthenticatorInterface{
		$this->secret = Base64::decode($this->checkEncodedSecret($encodedSecret));

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getSecret():string{

		if($this->secret === null){
			throw new RuntimeException('No secret set');
		}

		return Base64::encode($this->secret);
	}

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	public function createSecret(int $length = null):string{
		throw new RuntimeException('Not implemented');
	}

	/**
	 * @inheritDoc
	 */
	public function getCounter(int $data = null):int{
		// the period is fixed to 30 seconds for Steam Guard
		$this->options->period = 30;

		return parent::getCounter($data);
	}

	/**
	 * @inheritDoc
	 */
	public function getHMAC(int $counter):string{
		// algorithm is fixed to sha1 for Steam Guard
		$this->options->algorithm = self::ALGO_SHA1;

		return parent::getHMAC($counter);
	}

	/**
	 * @inheritDoc
	 */
	public function getOTP(int $code):string{
		$str = '';
		$len = 26; // strlen($this::steamCodeChars)

		// length is fixed to 5 for Steam
		for($i = 0; $i < 5; $i++){
			$str  .= $this::steamCodeChars[($code % $len)];
			$code  = intdiv($code, $len);
		}

		return $str;
	}

	/**
	 * @inheritDoc
	 * @throws \RuntimeException
	 */
	public function getServerTime():int{

		if($this->options->forceTimeRefresh === false && $this->serverTime !== 0){
			return $this->getAdjustedTime($this->serverTime, $this->lastRequestTime);
		}

		$this->serverTime      = 0;
		$this->lastRequestTime = 0;

		$options = [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST           => true,
			CURLOPT_POSTFIELDS     => 'steamid=0',
			CURLOPT_HTTPHEADER     => [sprintf('User-Agent: %s', $this::userAgent)],
		];

		$ch = curl_init($this::steamTimeURL);

		curl_setopt_array($ch, $options);

		$response = curl_exec($ch);
		$info     = curl_getinfo($ch);

		curl_close($ch);

		if($info['http_code'] !== 200){
			// I'm not going to investigate the error further as this shouldn't happen usually
			throw new RuntimeException(sprintf('Steam API request error: HTTP/%s', $info['http_code'])); // @codeCoverageIgnore
		}

		$json = json_decode($response, true);

		if(empty($json) || !isset($json['response']['server_time'])){
			throw new RuntimeException('Unable to decode Steam API response'); // @codeCoverageIgnore
		}

		$this->serverTime      = (int)$json['response']['server_time'];
		$this->lastRequestTime = (time() - (int)floor($info['total_time']));

		return $this->getAdjustedTime($this->serverTime, $this->lastRequestTime);
	}

}
