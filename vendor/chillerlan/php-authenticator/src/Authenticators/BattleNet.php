<?php
/**
 * Class BattleNet
 *
 * @created      28.06.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 *
 * @noinspection PhpComposerExtensionStubsInspection
 */

namespace chillerlan\Authenticator\Authenticators;

use chillerlan\Authenticator\Common\Hex;
use InvalidArgumentException;
use RuntimeException;
use function array_reverse;
use function array_unshift;
use function curl_close;
use function curl_exec;
use function curl_getinfo;
use function curl_init;
use function curl_setopt_array;
use function floor;
use function gmp_cmp;
use function gmp_div;
use function gmp_import;
use function gmp_init;
use function gmp_intval;
use function gmp_mod;
use function gmp_powm;
use function hash_hmac;
use function hexdec;
use function implode;
use function in_array;
use function pack;
use function preg_match;
use function random_bytes;
use function sha1;
use function sprintf;
use function str_pad;
use function str_replace;
use function str_split;
use function strlen;
use function strtoupper;
use function substr;
use function time;
use function trim;
use function unpack;
use const CURLOPT_HTTP_VERSION;
use const CURLOPT_HTTPHEADER;
use const CURLOPT_POST;
use const CURLOPT_POSTFIELDS;
use const CURLOPT_RETURNTRANSFER;
use const STR_PAD_LEFT;

/**
 * @see https://github.com/winauth/winauth/blob/master/Authenticator/BattleNetAuthenticator.cs
 * @see https://github.com/krtek4/php-bma
 */
final class BattleNet extends TOTP{

	/**
	 * @var array
	 */
	private const regions = ['EU', 'KR', 'US']; // 'CN',

	/**
	 * HTTPS requests with HTTP version 1.1 only!
	 *
	 * @var array
	 */
	private const servers = [
#		'CN' => 'https://mobile-service.battlenet.com.cn', // ???
		'EU' => 'https://eu.mobile-service.blizzard.com',
		'KR' => 'https://kr.mobile-service.blizzard.com',
		'US' => 'https://us.mobile-service.blizzard.com',
	];

	/**
	 * @var array
	 */
	private const endpoints = [
		'public_key' => '/enrollment/initiatePaperRestore.htm',
		'validate'   => '/enrollment/validatePaperRestore.htm',
		'create'     => '/enrollment/enroll.htm',
		'servertime' => '/enrollment/time.htm',
	];

	private const rsa_exp_base10 = '257';
	private const rsa_mod_base10 = '1048900188079865568740077109142054431570301596680341971861256789'.
	                               '6028747089429083053061828494311840511089632283544909943323209315'.
	                               '1168250152146023319326491587651685252774820340995950744075665455'.
	                               '6817606521365764930287339148921667008991098362911808810630974611'.
	                               '75643998356321993663868233366705340758102567742483097';

#	private const rsa_exp_base16 = '0101';
#	private const rsa_mod_base16 = '955e4bd989f3917d2f15544a7e0504eb9d7bb66b6f8a2fe470e453c779200e5e'.
#	                               '3ad2e43a02d06c4adbd8d328f1a426b83658e88bfd949b2af4eaf30054673a14'.
#	                               '19a250fa4cc1278d12855b5b25818d162c6e6ee2ab4a350d401d78f6ddb99711'.
#	                               'e72626b48bd8b5b0b7f3acf9ea3c9e0005fee59e19136cdb7c83f2ab8b0a2a99';

	private array $curlInfo = [];

	/**
	 * @inheritDoc
	 */
	public function setSecret(string $encodedSecret):AuthenticatorInterface{
		$this->secret = Hex::decode($this->checkEncodedSecret($encodedSecret));

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getSecret():string{

		if($this->secret === null){
			throw new RuntimeException('No secret set');
		}

		return Hex::encode($this->secret);
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
		// the period is fixed to 30 seconds for Battle.net
		$this->options->period = 30;

		return parent::getCounter($data);
	}

	/**
	 * @inheritDoc
	 */
	public function getHMAC(int $counter):string{
		// algorithm is fixed to sha1 for Battle.net
		$this->options->algorithm = self::ALGO_SHA1;

		return parent::getHMAC($counter);
	}

	/**
	 * @inheritDoc
	 */
	public function getOTP(int $code):string{
		$code %= 100000000;

		// length is fixed to 8 for Battle.net
		return str_pad((string)$code, 8, '0', STR_PAD_LEFT);
	}

	/**
	 * @inheritDoc
	 */
	public function getServerTime():int{

		if($this->options->forceTimeRefresh === false && $this->serverTime !== 0){
			return $this->getAdjustedTime($this->serverTime, $this->lastRequestTime);
		}

		$servertime = $this->request('servertime', 'US');

		$this->setServertime($servertime);

		return $this->getAdjustedTime($this->serverTime, $this->lastRequestTime);
	}

	/**
	 * Retrieves the secret from Battle.net using the given serial and restore code.
	 * If the public key for the serial is given (from a previous retrieval), it saves a server request.
	 */
	public function restoreSecret(string $serial, string $restore_code, string $public_key = null):array{
		$serial = $this->cleanSerial($serial);
		$region = $this->getRegion($serial);

		// fetch public key if none is given
		$pubkey = ($public_key !== null)
			? Hex::decode($public_key)
			: $this->request('public_key', $region, $serial);

		// create HMAC hash from serial and restore code
		$hmac_key         = $this->convertRestoreCodeToByte($restore_code);
		$hmac             = hash_hmac('sha1', $serial.$pubkey, $hmac_key, true);
		// encrypt and send validation request
		$nonce            = random_bytes(20);
		$encrypted_secret = $this->request('validate', $region, $serial.$this->encrypt($hmac.$nonce));
		$secret           = $this->decrypt($encrypted_secret, $nonce);

		return [
			'region'       => $region,
			'serial'       => $this->formatSerial($serial),
			'restore_code' => $restore_code,
			'public_key'   => Hex::encode($pubkey),
			'secret'       => Hex::encode($secret),
		];
	}

	/**
	 * Creates a new authenticator that can be linked to an existing Battle.net account
	 */
	public function createAuthenticator(string $region, string $device = null):array{
		$region       = $this->getRegion($region);
		$device       = str_pad(($device ?? 'BlackBerry Pearl'), 16, "\x00");
		$nonce        = random_bytes(37);
		$response     = $this->request('create', $region, $this->encrypt("\x01".$nonce.$region.$device));
		// timestamp, first 8 bytes of the response
		$this->setServertime(substr($response, 0, 8));
		// decrypt rest of the response (37 bytes)
		$data         = $this->decrypt(substr($response, 8), $nonce);
		// secret, first 20 bytes
		$secret       = substr($data, 0, 20);
		// serial, last 17 bytes
		$serial       = $this->cleanSerial(substr($data, 20));
		// the restore code is taken from the last 10 bytes of a SHA1 hashed serial and (binary) secret
		$restore_code = substr(sha1($serial.$secret, true), -10);

		// feed the result into the restore function to verify the restore code and fetch the public key
		return $this->restoreSecret($serial, $this->convertRestoreCodeToChar($restore_code));
	}

	/**
	 *
	 */
	private function setServertime(string $encodedTimestamp):void{
		$this->serverTime      = (int)floor(hexdec(Hex::encode($encodedTimestamp)) / 1000);
		$this->lastRequestTime = (time() - (int)floor($this->curlInfo['total_time']));
	}

	/**
	 * @throws \RuntimeException
	 */
	private function getRegion(string $serial):string{
		$region = substr(strtoupper($serial), 0, 2);

		if(!in_array($region, self::regions)){
			throw new RuntimeException('invalid region in serial number detected');
		}

		return $region;
	}

	/**
	 * cleans the given serial in (EU-1111-2222-3333) and strips hyphens (EU111122223333) for use in API requests
	 *
	 * @throws \InvalidArgumentException
	 */
	private function cleanSerial(string $serial):string{
		$serial = str_replace('-', '', strtoupper(trim($serial)));

		if(!preg_match('/^[CNEUSKR]{2}\d{12}$/', $serial)){
			throw new InvalidArgumentException('invalid serial');
		}

		return $serial;
	}

	/**
	 *
	 */
	private function formatSerial(string $serial):string{
		$serial = $this->cleanSerial($serial);
		// split the numeric part into 3x 4 numbers
		$blocks = str_split(substr($serial, 2), 4);
		// prepend the region
		array_unshift($blocks, substr($serial, 0, 2));

		return implode('-', $blocks);
	}

	/**
	 * @throws \RuntimeException
	 */
	private function request(string $endpoint, string $region, string $data = null):string{

		$options = [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTP_VERSION   => '1.1', // we need to force http 1.1, h2 will return a HTTP/600 error (???) from Battle.net
			CURLOPT_HTTPHEADER     => [sprintf('User-Agent: %s', $this::userAgent)],
		];

		if($data !== null){
			$options[CURLOPT_POST]         = true;
			$options[CURLOPT_POSTFIELDS]   = $data;
			$options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/octet-stream';
		}

		$ch = curl_init(self::servers[$region].self::endpoints[$endpoint]);

		curl_setopt_array($ch, $options);

		$response       = curl_exec($ch);
		$this->curlInfo = curl_getinfo($ch);

		curl_close($ch);

		if($this->curlInfo['http_code'] !== 200){
			// I'm not going to investigate the error further as this shouldn't happen usually
			throw new RuntimeException(sprintf('Battle.net API request error: HTTP/%s', $this->curlInfo['http_code'])); // @codeCoverageIgnore
		}

		return $response;
	}

	/**
	 * Convert restore code char to byte but with appropriate mapping to exclude I,L,O and S.
	 * e.g. A=10 but J=18 not 19 (as I is missing)
	 */
	private function convertRestoreCodeToByte(string $restore_code):string{
		$chars = unpack('C*', $restore_code);

		foreach($chars as &$c){
			if($c > 47 && $c < 58){
				$c -= 48;
			}
			else{
				// S
				if($c > 82){
					$c--;
				}
				// O
				if($c > 78){
					$c--;
				}
				// L
				if($c > 75){
					$c--;
				}
				// I
				if($c > 72){
					$c--;
				}

				$c -= 55;
			}

		}

		return pack('C*', ...$chars);
	}

	/**
	 * Convert restore code byte to char but with appropriate mapping to exclude I,L,O and S.
	 */
	private function convertRestoreCodeToChar(string $data):string{
		$chars = unpack('C*', $data);

		foreach($chars as &$c){
			$c &= 0x1F;

			if($c < 10){
				$c += 48;
			}
			else{
				$c += 55;
				// I
				if($c > 72){
					$c++;
				}
				// L
				if($c > 75){
					$c++;
				}
				// O
				if($c > 78){
					$c++;
				}
				// S
				if($c > 82){
					$c++;
				}
			}
		}

		return pack('C*', ...$chars);
	}

	/**
	 *
	 */
	private function encrypt(string $data):string{
		$num  = gmp_powm(gmp_import($data), self::rsa_exp_base10, self::rsa_mod_base10); // gmp_init(self::rsa_mod_base16, 16)
		$zero = gmp_init('0', 10);
		$ret  = [];

		while(gmp_cmp($num, $zero) > 0){
			$ret[] = gmp_intval(gmp_mod($num, 256));
			$num   = gmp_div($num, 256);
		}

		return pack('C*', ...array_reverse($ret));
	}

	/**
	 * @throws \RuntimeException
	 */
	private function decrypt(string $data, string $key):string{

		if(strlen($data) !== strlen($key)){
			throw new RuntimeException('The decryption key size and data size doesn\'t match');
		}

		$data = unpack('C*', $data);
		$key  = unpack('C*', $key);

		foreach($data as $i => &$c){
			$c ^= $key[$i];
		}

		return pack('C*', ...$data);
	}

}
