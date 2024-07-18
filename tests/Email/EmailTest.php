<?php

namespace Kirby\Email;

use Closure;
use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Exception\NotFoundException;
use Kirby\TestCase;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * @coversDefaultClass \Kirby\Email\Email
 */
class EmailTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';

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
	 * @covers ::attachments
	 */
	public function testAttachments(): void
	{
		$email = $this->email([
			'attachments' => [
				$a = '/some/file.jpg',
				new File([
					'filename' => 'foo.mp4',
					'parent'   => new Page(['slug' => 'test'])
				])
			]
		]);

		$this->assertSame($a, $email->attachments()[0]->root());
		$this->assertSame('/dev/null/content/test/foo.mp4', $email->attachments()[1]->root());
	}

	/**
	 * @covers ::bcc
	 */
	public function testBcc(): void
	{
		$email = $this->email([
			'bcc' => $bcc = 'homer@simpson.com'
		]);

		$this->assertSame($bcc, $email->bcc()[0]->email());

		$email = $this->email([
			'bcc' => [$bcc = 'homer@simpson.com']
		]);

		$this->assertSame($bcc, $email->bcc()[0]->email());
	}

	/**
	 * @covers ::beforeSend
	 */
	public function testBeforeSend(): void
	{
		$test = $this;
		$smtp = [
			'ssl' => [
				'verify_peer'       => false,
				'verify_peer_name'  => false,
				'allow_self_signed' => true
			]
		];

		$transport = [
			'type'     => 'smtp',
			'host'     => 'mail.getkirby.com',
			'port'     => 465,
			'security' => true,
			'auth'     => true,
			'username' => 'test@test.com',
			'password' => 'randomString',
		];

		$email = $this->email([
			'transport'  => $transport,
			'beforeSend' => $beforeSend = function ($mailer) use ($test, $smtp) {
				$test->assertInstanceOf(PHPMailer::class, $mailer);
				$mailer->SMTPOptions = $smtp;
				return $mailer;
			}
		]);

		$this->assertSame($transport, $email->transport());
		$this->assertSame($beforeSend, $email->beforeSend());
		$this->assertInstanceOf(Closure::class, $email->beforeSend());

		$mailer     = new PHPMailer();
		$newMailer = $email->beforeSend()->call($this, $mailer);
		$test->assertInstanceOf(PHPMailer::class, $mailer);
		$this->assertSame($newMailer, $mailer);
		$this->assertSame($smtp, $mailer->SMTPOptions);
	}

	/**
	 * @covers ::body
	 */
	public function testBody(): void
	{
		$email = $this->email([
			'body' => $body = 'Hello'
		]);

		$this->assertSame($body, $email->body()->text());
	}

	/**
	 * @covers ::body
	 */
	public function testBodyTemplate(): void
	{
		new App([
			'templates' => [
				'emails/contact' => static::FIXTURES . '/contact.php'
			]
		]);

		$email = $this->email([
			'body'     => null,
			'template' => 'contact',
			'data'     => ['name' => 'Alex']
		]);

		$this->assertSame('Cheers, Alex!', $email->body()->text());
	}

	/**
	 * @covers ::cc
	 */
	public function testCc(): void
	{
		$email = $this->email([
			'cc' => $cc = 'homer@simpson.com'
		]);

		$this->assertSame($cc, $email->cc()[0]->email());

		$email = $this->email([
			'cc' => [$cc = 'homer@simpson.com']
		]);

		$this->assertSame($cc, $email->cc()[0]->email());
	}

	/**
	 * @covers ::factory
	 */
	public function testFactory(): void
	{
		$email = Email::factory([
			'from'    => $from = 'no-reply@supercompany.com',
			'to'      => $to = 'someone@gmail.com',
			'subject' => $subject = 'Thank you for your contact request',
			'body'    => $body = 'We will never reply',
		]);

		$this->assertSame($from, $email->from()->email());
		$this->assertSame($to, $email->to()[0]->email());
		$this->assertSame($subject, $email->subject());
		$this->assertSame($body, $email->body()->text());
	}

	/**
	 * @covers ::factory
	 */
	public function testFactoryPreset(): void
	{
		new App([
			'options' => [
				'email' => [
					'presets' => [
						'contact' => [
							'cc' => $cc = 'marketing@supercompany.com',
						]
					]
				]
			]
		]);

		$email = Email::factory('contact', [
			'from'    => 'no-reply@supercompany.com',
			'to'      => $to = 'nobody@web.de',
			'subject' => 'Thank you for your contact request',
			'body'    => 'We will never reply',
		]);

		$this->assertSame($to, $email->to()[0]->email());
		$this->assertSame($cc, $email->cc()[0]->email());
	}

	/**
	 * @covers ::factory
	 */
	public function testFactoryPresetInvalid(): void
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionCode('error.email.preset.notFound');
		Email::factory('not-a-preset');
	}

	/**
	 * @covers ::from
	 */
	public function testFrom(): void
	{
		$email = $this->email([
			'from' => $from = 'homer@simpson.com'
		]);

		$this->assertSame($from, $email->from()->email());

		$email = $this->email([
			'from' => [$from = 'homer@simpson.com' => $name = 'Homer Simpson']
		]);

		$this->assertSame($from, $email->from()->email());
		$this->assertSame($name, $email->from()->name());
	}

	/**
	 * @covers ::isHtml
	 */
	public function testIsHtml(): void
	{
		$email = $this->email();
		$this->assertFalse($email->isHtml());
	}

	/**
	 * @covers ::isSent
	 */
	public function testIsSent(): void
	{
		$email = $this->email();
		$this->assertFalse($email->isSent());
		$email->isSent = true;
		$this->assertTrue($email->isSent());
	}

	/**
	 * @covers ::replyTo
	 */
	public function testReplyTo(): void
	{
		$email = $this->email();
		$this->assertNull($email->replyTo());

		$email = $this->email([
			'replyTo' => $replyTo = 'homer@simpson.com'
		]);

		$this->assertSame($replyTo, $email->replyTo()->email());
	}

	/**
	 * @covers ::subject
	 */
	public function testSubject(): void
	{
		$email = $this->email();
		$this->assertSame('Thank you for your contact request', $email->subject());
	}

	/**
	 * @covers ::to
	 */
	public function testTo(): void
	{
		$email = $this->email([
			'to' => $to = 'homer@simpson.com'
		]);

		$this->assertSame($to, $email->to()[0]->email());

		$email = $this->email([
			'to' => [$to = 'homer@simpson.com']
		]);

		$this->assertSame($to, $email->to()[0]->email());
	}

	/**
	 * @covers ::transport
	 */
	public function testTransport(): void
	{
		$email = $this->email();
		$this->assertSame(['type' => 'mail'], $email->transport());

		$email = $this->email(['transport' => ['type' => 'snail']]);
		$this->assertSame(['type' => 'snail'], $email->transport());
	}
}
