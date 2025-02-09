<?php

namespace Kirby\Cms;

use Kirby\Exception\NotFoundException;
use PHPMailer\PHPMailer\PHPMailer as Mailer;

class EmailTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures/emails';

	public function testToArray()
	{
		$props = [
			'one' => 'eins',
			'two' => 'zwei',
		];

		$expected = [
			'one'         => 'eins',
			'two'         => 'zwei',
			'transport'   => [],
			'beforeSend'  => null,
			'from'        => null,
			'fromName'    => null,
			'replyTo'     => null,
			'replyToName' => null,
			'to'          => [],
			'cc'          => [],
			'bcc'         => [],
			'attachments' => []
		];

		$email = new Email($props);
		$this->assertSame($expected, $email->toArray());
	}

	public function testPresets()
	{
		$app = new App([
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

		$email = new Email('contact', [
			'to' => $to = 'nobody@web.de'
		]);

		$this->assertSame([$to], $email->toArray()['to']);
		$this->assertSame([$cc], $email->toArray()['cc']);
	}

	public function testInvalidPreset()
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionCode('error.email.preset.notFound');

		$email = new Email('not-a-preset', []);
	}

	public function testTemplate()
	{
		$app = new App([
			'templates' => [
				'emails/contact' => static::FIXTURES . '/contact.php'
			]
		]);
		$email = new Email([
			'template' => 'contact',
			'data' => [
				'name' => 'Alex'
			]
		]);
		$this->assertSame('Cheers, Alex!', $email->toArray()['body']);
	}

	public function testTemplateHtml()
	{
		$app = new App([
			'templates' => [
				'emails/media.html' => static::FIXTURES . '/media.html.php'
			]
		]);
		$email = new Email(['template' => 'media']);
		$this->assertSame([
			'html' => '<b>Image:</b> <img src=""/>'
		], $email->toArray()['body']);
	}

	public function testTemplateHtmlText()
	{
		$app = new App([
			'templates' => [
				'emails/media.html' => static::FIXTURES . '/media.html.php',
				'emails/media.text' => static::FIXTURES . '/media.text.php',
			]
		]);
		$email = new Email(['template' => 'media']);
		$this->assertSame([
			'html' => '<b>Image:</b> <img src=""/>',
			'text' => 'Image: Description'
		], $email->toArray()['body']);
	}

	public function testInvalidTemplate()
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The email template "subscription" cannot be found');

		$email = new Email([
			'template' => 'subscription'
		]);
	}

	public function testTransformSimple()
	{
		$email = new Email([
			'from'     => 'sales@company.com',
			'fromName' => 'Company Sales',
			'to'       => 'ceo@company.com',
			'cc'       => [
				'someone@gmail.com',
				'another@gmail.com' => 'Another Gmail',
			],
			'attachments' => [
				'/amazing/absolute/path.txt'
			]
		]);

		$this->assertSame('sales@company.com', $email->toArray()['from']);
		$this->assertSame('Company Sales', $email->toArray()['fromName']);
		$this->assertSame(['ceo@company.com'], $email->toArray()['to']);
		$this->assertSame([
			'someone@gmail.com',
			'another@gmail.com' => 'Another Gmail'
		], $email->toArray()['cc']);
		$this->assertSame([
			'/amazing/absolute/path.txt'
		], $email->toArray()['attachments']);
	}

	public function testTransformComplex()
	{
		$app = new App([
			'site' => new Site(),
			'roots' => [
				'content' => '/content'
			]
		]);

		$from = new User(['email' => 'sales@company.com', 'name' => 'Company Sales']);
		$to = new User(['email' => 'ceo@company.com', 'name' => 'Company CEO']);

		$file = new File([
			'filename' => 'report.pdf',
			'parent' =>  $app->site()
		]);
		$image = new File([
			'filename' => 'graph.png',
			'parent' =>  $app->site()
		]);

		$email = new Email([
			'from' => $from,
			'fromName' => 'Amazing Sales!',
			'replyTo' => $from,
			'to' => [
				$to,
				'someone@gmail.com',
				'another@gmail.com' => 'Another Gmail'
			],
			'attachments' => [
				$file,
				$image,
				'/amazing/absolute/path.txt'
			]
		]);

		$this->assertSame('sales@company.com', $email->toArray()['from']);
		$this->assertSame('Amazing Sales!', $email->toArray()['fromName']);
		$this->assertSame('sales@company.com', $email->toArray()['replyTo']);
		$this->assertSame('Company Sales', $email->toArray()['replyToName']);
		$this->assertSame([
			'ceo@company.com' => 'Company CEO',
			'someone@gmail.com',
			'another@gmail.com' => 'Another Gmail'
		], $email->toArray()['to']);
		$this->assertSame([
			'/content/report.pdf',
			'/content/graph.png',
			'/amazing/absolute/path.txt'
		], $email->toArray()['attachments']);
	}

	public function testTransformCollection()
	{
		$to = new Users([
			new User(['email' => 'ceo@company.com', 'name' => 'Company CEO']),
			new User(['email' => 'marketing@company.com', 'name' => 'Company Marketing'])
		]);

		$email = new Email(['to' => $to]);

		$this->assertSame([
			'ceo@company.com' => 'Company CEO',
			'marketing@company.com' => 'Company Marketing'
		], $email->toArray()['to']);
	}

	#[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
	#[\PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
	public function testUserData()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'templates' => [
				'emails/user-info' => static::FIXTURES . '/user-info.php'
			]
		]);

		$user = new User([
			'email' => 'ceo@company.com',
			'name' => 'Mario'
		]);

		$email = new Email([
			'to' => $user,
			'template' => 'user-info',
			'data' => [
				'user' => $user
			]
		]);

		$this->assertSame(['ceo@company.com' => 'Mario'], $email->toArray()['to']);
		$this->assertSame('Welcome, Mario!', trim($email->toArray()['body']));
	}

	public function testBeforeSend()
	{
		new App([
			'options' => [
				'email' => [
					'beforeSend' => function ($mailer) {
						$mailer->SMTPOptions = [
							'ssl' => [
								'verify_peer'       => false,
								'verify_peer_name'  => false,
								'allow_self_signed' => true
							]
						];

						return $mailer;
					}
				]
			]
		]);

		$email = new Email([
			'to' => 'ceo@company.com'
		]);
		$beforeSend = $email->toArray()['beforeSend'];

		$this->assertInstanceOf('Closure', $beforeSend);
		$this->assertInstanceOf('PHPMailer\PHPMailer\PHPMailer', $beforeSend(new Mailer()));
	}
}
