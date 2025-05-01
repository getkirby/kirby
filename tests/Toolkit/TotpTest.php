<?php

namespace Kirby\Toolkit;

use Base32\Base32;
use Kirby\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Totp::class)]
class TotpTest extends TestCase
{
	public function tearDown(): void
	{
		MockTime::$time = 1337000000;
	}

	public function testGenerate()
	{
		// test cases taken from the appendix of RFC6238:
		// https://datatracker.ietf.org/doc/html/rfc6238#appendix-B
		$totp = new Totp(Base32::encode('12345678901234567890'));

		MockTime::$time = 59;
		$this->assertSame('287082', $totp->generate());

		MockTime::$time = 1111111109;
		$this->assertSame('081804', $totp->generate());
		$this->assertSame('050471', $totp->generate(1));

		MockTime::$time = 1111111111;
		$this->assertSame('050471', $totp->generate());
		$this->assertSame('081804', $totp->generate(-1));

		MockTime::$time = 1234567890;
		$this->assertSame('005924', $totp->generate());

		MockTime::$time = 2000000000;
		$this->assertSame('279037', $totp->generate());

		MockTime::$time = 20000000000;
		$this->assertSame('353130', $totp->generate());
	}

	public function testSecret()
	{
		// randomly generated secret
		$totp1 = new Totp();
		$this->assertSame(32, strlen($totp1->secret()));

		$totp2 = new Totp();
		$this->assertSame(32, strlen($totp1->secret()));
		$this->assertNotSame($totp1->secret(), $totp2->secret());

		// predefined secret
		$totp3 = new Totp($secret = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567');
		$this->assertSame($secret, $totp3->secret());

		// force mode (third-party services)
		$totp4 = new Totp($secret = 'TOOSHORT', true);
		$this->assertSame($secret, $totp4->secret());
	}

	public function testSecretInvalid1()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('TOTP secrets should be 32 Base32 digits (= 20 bytes)');

		new Totp('');
	}

	public function testSecretInvalid2()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('TOTP secrets should be 32 Base32 digits (= 20 bytes)');

		new Totp('TOOSHORT');
	}

	public function testSecretInvalid3()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('TOTP secrets should be 32 Base32 digits (= 20 bytes)');

		new Totp('ABcDEfGHiJKlMNoPQRStuVWXYZ012345'); // invalid Base32 digits
	}

	public function testUri()
	{
		$totp = new Totp('ABCDEFGHIJKLMNOPQRSTUVWXYZ234567');

		$this->assertSame(
			'otpauth://totp/A%20nice%20Kirby%20site:test%40getkirby.com%20with%20spaces' .
			'?secret=ABCDEFGHIJKLMNOPQRSTUVWXYZ234567&issuer=A%20nice%20Kirby%20site',
			$totp->uri('A nice Kirby site', 'test@getkirby.com with spaces')
		);
	}

	public function testVerify()
	{
		MockTime::$time = 1111111111;
		$totp = new Totp(Base32::encode('12345678901234567890'));

		$this->assertFalse($totp->verify('731029')); // offset -2
		$this->assertTrue($totp->verify('081804'));  // offset -1
		$this->assertTrue($totp->verify('050471'));  // offset  0
		$this->assertTrue($totp->verify('266759'));  // offset +1
		$this->assertFalse($totp->verify('306183')); // offset +2

		$this->assertFalse($totp->verify(''));
		$this->assertFalse($totp->verify('a beer'));
		$this->assertFalse($totp->verify('lizard'));

		$this->assertTrue($totp->verify('050 471'));
		$this->assertFalse($totp->verify('306 183'));
		$this->assertTrue($totp->verify('05 04 71'));
		$this->assertFalse($totp->verify('30 61 83'));
		$this->assertTrue($totp->verify('My one-time code is 050471!'));
		$this->assertFalse($totp->verify('My one-time code is 306183!'));
	}
}
