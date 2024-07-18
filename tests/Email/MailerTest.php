<?php

namespace Kirby\Email;

use Kirby\Exception\InvalidArgumentException;
use Kirby\TestCase;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * @coversDefaultClass \Kirby\Email\Mailer
 * @covers ::__construct
 */
class MailerTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures/files';

	protected function email(array $props = []): Email
	{
		return new Email(...[
			'from'    => 'no-reply@supercompany.com',
			'to'      => 'someone@gmail.com',
			'subject' => 'Thank you for your contact request',
			'body'    => 'We will never reply',
			...$props
		]);
	}

	/**
	 * @covers ::send
	 */
	public function testSend()
	{
		$email  = $this->email();
		$mailer = new Mailer($email);
		$this->assertFalse($email->isSent());
		$mailer->send(true);
		$this->assertTrue($email->isSent());
	}

	/**
	 * @covers ::send
	 */
	public function testSendSettingAttributes()
	{
		$test     = $this;
		$fixtures = static::FIXTURES;
		$email     = $this->email([
			'attachments' => [$fixtures . '/test.jpg'],
			'bcc'         => ['homer@simpson.com'],
			'cc'          => ['lisa@simpson.com'],
			'replyTo'     => 'bart@simpson.com',
			'beforeSend'  => function (PHPMailer $mailer) use ($test, $fixtures) {
				$test->assertSame('mail', $mailer->Mailer);
				$test->assertSame('no-reply@supercompany.com', $mailer->From);
				$test->assertSame('', $mailer->FromName);
				$test->assertSame([['someone@gmail.com', '']], $mailer->getToAddresses());
				$test->assertSame([
					'bart@simpson.com' => ['bart@simpson.com', '']
				], $mailer->getReplyToAddresses());
				$test->assertSame('text/plain', $mailer->ContentType);
				$test->assertSame('We will never reply', $mailer->Body);
				$test->assertSame([
					['lisa@simpson.com', ''],
				], $mailer->getCcAddresses());
				$test->assertSame([
					['homer@simpson.com', '']
				], $mailer->getBccAddresses());
				$test->assertSame([
					[
						$fixtures . '/test.jpg',
						'test.jpg',
						'test.jpg',
						'base64',
						'image/jpeg',
						false,
						'attachment',
						'test.jpg'
					]
				], $mailer->getAttachments());

				return $mailer;
			}
		]);

		$mailer = new Mailer($email);
		$mailer->send(true);
	}


	/**
	 * @covers ::send
	 */
	public function testSendBeforeSendInvalidReturn()
	{
		$email = $this->email([
			'beforeSend' => fn (PHPMailer $mailer) => 'yay'
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('"beforeSend" option return must be instance of PHPMailer\PHPMailer\PHPMailer class');

		$mailer = new Mailer($email);
		$mailer->send(true);
	}

	/**
	 * @covers ::send
	 */
	public function testSendSmtpTransportDefaults()
	{
		$test  = $this;
		$email = $this->email([
			'transport' => ['type' => 'smtp'],
			'beforeSend' => function (PHPMailer $mailer) use ($test) {
				$test->assertSame('smtp', $mailer->Mailer);
				$test->assertNull($mailer->Host);
				$test->assertFalse($mailer->SMTPAuth);
				$test->assertNull($mailer->Username);
				$test->assertNull($mailer->Password);
				$test->assertSame('ssl', $mailer->SMTPSecure);
				$test->assertNull($mailer->Port);
			}
		]);


		$mailer = new Mailer($email);
		$mailer->send(true);
	}

	/**
	 * @covers ::send
	 */
	public function testSendSmtpTransportSecurity()
	{
		$test  = $this;
		$email = $this->email([
			'transport' => [
				'type'     => 'smtp',
				'security' => true
			],
			'beforeSend' => function (PHPMailer $mailer) use ($test) {
				$test->assertSame('smtp', $mailer->Mailer);
				$test->assertSame('tls', $mailer->SMTPSecure);
				$test->assertSame(587, $mailer->Port);
			}
		]);


		$mailer = new Mailer($email);
		$mailer->send(true);
	}

	/**
	 * @covers ::send
	 */
	public function testSendSmtpTransportSecurity2()
	{
		$test  = $this;
		$email = $this->email([
			'transport' => [
				'type'     => 'smtp',
				'security' => true,
				'port'     => 587
			],
			'beforeSend' => function (PHPMailer $mailer) use ($test) {
				$test->assertSame('smtp', $mailer->Mailer);
				$test->assertSame('tls', $mailer->SMTPSecure);
				$test->assertSame(587, $mailer->Port);
			}
		]);


		$mailer = new Mailer($email);
		$mailer->send(true);
	}

	/**
	 * @covers ::send
	 */
	public function testSendSmtpTransportSecurity3()
	{
		$test  = $this;
		$email = $this->email([
			'transport' => [
				'type'     => 'smtp',
				'security' => true,
				'port'     => 465
			],
			'beforeSend' => function (PHPMailer $mailer) use ($test) {
				$test->assertSame('smtp', $mailer->Mailer);
				$test->assertSame('ssl', $mailer->SMTPSecure);
				$test->assertSame(465, $mailer->Port);
			}
		]);


		$mailer = new Mailer($email);
		$mailer->send(true);
	}

	/**
	 * @covers ::send
	 */
	public function testSendSmtpTransportSecurityInvalid()
	{
		$test  = $this;
		$email = $this->email([
			'transport' => [
				'type'     => 'smtp',
				'security' => true,
				'port'     => 1234
			],
			'beforeSend' => function (PHPMailer $mailer) use ($test) {
				$test->assertSame('smtp', $mailer->Mailer);
				$test->assertSame('ssl', $mailer->SMTPSecure);
				$test->assertSame(465, $mailer->Port);
			}
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Could not automatically detect the "security" protocol from the "port" option, please set it explicitly to "tls" or "ssl".');

		$mailer = new Mailer($email);
		$mailer->send(true);
	}
}
