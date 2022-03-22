<?php

namespace Kirby\Email;

use PHPMailer\PHPMailer\PHPMailer as Mailer;

class EmailTest extends TestCase
{
    protected function _email($props = [], $mailer = Email::class)
    {
        return parent::_email($props, $mailer);
    }

    public function testProperties()
    {
        $email = $this->_email([
            'from' => $from = 'no-reply@supercompany.com',
            'fromName' => $fromName = 'Super Company NoReply',
            'to' => $to = 'someone@gmail.com',
            'replyTo' => $replyTo = 'reply@supercompany.com',
            'replyToName' => $replyToName = 'Super Company Reply',
            'subject' => $subject = 'Thank you for your contact request',
            'body' => $body = 'We will never reply',
            'cc' => $cc = [
                'marketing@supercompany.com',
                'sales@supercompany.com' => 'Super Company Sales'
            ],
            'bcc' => $cc
        ]);

        $expectedCc = [
            'marketing@supercompany.com' => null,
            'sales@supercompany.com'     => 'Super Company Sales'
        ];

        $this->assertSame($from, $email->from());
        $this->assertSame($fromName, $email->fromName());
        $this->assertSame([$to => null], $email->to());
        $this->assertSame($replyTo, $email->replyTo());
        $this->assertSame($replyToName, $email->replyToName());
        $this->assertSame($subject, $email->subject());
        $this->assertSame($expectedCc, $email->cc());
        $this->assertSame($expectedCc, $email->bcc());

        $this->assertInstanceOf(Body::class, $email->body());
        $this->assertSame($body, $email->body()->text());
        $this->assertSame('', $email->body()->html());
        $this->assertFalse($email->isHtml());

        $this->assertSame(['type' => 'mail'], $email->transport());
    }

    public function testRequiredProperty()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('The property "from" is required');

        $email = $this->_email([
            'from' => null
        ]);
    }

    public function testOptionalAddresses()
    {
        $email = $this->_email([
            'replyTo' => null,
            'cc' => null,
            'bcc' => null,
        ]);

        $this->assertSame('', $email->replyTo());
        $this->assertSame([], $email->cc());
        $this->assertSame([], $email->bcc());
    }

    public function testInvalidAddress()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('"not-valid" is not a valid email address');

        $email = $this->_email([
            'to' => [
                'valid@company.com',
                'not-valid'
            ]
        ]);
    }

    public function testIsSent()
    {
        $email = $this->_email([]);
        $this->assertFalse($email->isSent());
        $email->send();
        $this->assertTrue($email->isSent());
    }

    public function testBody()
    {
        $email = $this->_email([
            'body' => $body = [
                'text' => 'Plain text, yeah!',
                'html' => 'HTML is even <b>better</b>'
            ]
        ]);

        $this->assertInstanceOf(Body::class, $email->body());
        $this->assertSame($body['text'], $email->body()->text());
        $this->assertSame($body['html'], $email->body()->html());

        $this->assertTrue($email->isHtml());
    }

    public function testBodyHtmlOnly()
    {
        $email = $this->_email([
            'body' => $body = [
                'html' => 'HTML is even <b>better</b>'
            ]
        ]);

        $this->assertInstanceOf(Body::class, $email->body());
        $this->assertSame('', $email->body()->text());
        $this->assertSame($body['html'], $email->body()->html());

        $this->assertTrue($email->isHtml());
    }

    public function testAttachments()
    {
        $email = $this->_email([
            'attachments' => $attachments = [
                'file.txt',
                'image.png'
            ]
        ]);

        $this->assertSame($attachments, $email->attachments());
    }

    public function testBeforeSend()
    {
        $phpunit = $this;
        $smtpOptions = [
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

        // valid with return $mailer instance
        $mail = $this->_email([
            'transport'  => $transport,
            'beforeSend' => $beforeSend = function ($mailer) use ($phpunit, $smtpOptions) {
                $phpunit->assertInstanceOf('PHPMailer\PHPMailer\PHPMailer', $mailer);

                $mailer->SMTPOptions = $smtpOptions;

                return $mailer;
            }
        ], PHPMailer::class);

        $mailer = new Mailer();

        $this->assertSame($transport, $mail->transport());
        $this->assertSame($mail->beforeSend(), $beforeSend);
        $this->assertInstanceOf('Closure', $mail->beforeSend());

        $newMailer = $mail->beforeSend()->call($this, $mailer);
        $this->assertInstanceOf('PHPMailer\PHPMailer\PHPMailer', $mailer);
        $this->assertSame($newMailer, $mailer);
        $this->assertSame($smtpOptions, $mailer->SMTPOptions);
        $this->assertTrue($mail->send(true));

        // valid without return $mailer instance
        $mail = $this->_email([
            'transport'  => $transport,
            'beforeSend' => $beforeSend = function ($mailer) use ($phpunit, $smtpOptions) {
                $phpunit->assertInstanceOf('PHPMailer\PHPMailer\PHPMailer', $mailer);

                $mailer->SMTPOptions = $smtpOptions;
            }
        ], PHPMailer::class);

        $mailer = new Mailer();

        $this->assertSame($transport, $mail->transport());
        $this->assertSame($mail->beforeSend(), $beforeSend);
        $this->assertInstanceOf('Closure', $mail->beforeSend());

        $newMailer = $mail->beforeSend()->call($this, $mailer);
        $this->assertInstanceOf('PHPMailer\PHPMailer\PHPMailer', $mailer);
        $this->assertNull($newMailer);
        $this->assertSame($smtpOptions, $mailer->SMTPOptions);
        $this->assertTrue($mail->send(true));

        // invalid
        $mail = $this->_email([
            'transport'  => $transport,
            'beforeSend' => $beforeSend = function ($mailer) {
                return 'string';
            }
        ], PHPMailer::class);

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('"beforeSend" option return should be instance of PHPMailer\PHPMailer\PHPMailer class');
        $this->assertSame($mail->beforeSend(), $beforeSend);
        $this->assertInstanceOf('Closure', $mail->beforeSend());

        $mail->send(true);
    }
}
