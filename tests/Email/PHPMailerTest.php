<?php

namespace Kirby\Email;

class PHPMailerTest extends TestCase
{
    protected function _email($props = [], $mailer = PHPMailer::class)
    {
        return parent::_email($props, $mailer);
    }

    public function testSend()
    {
        $email = $this->_email([
            'to' => 'test@test.com'
        ]);
        $this->assertFalse($email->isSent());
        $email->send(true);
        $this->assertTrue($email->isSent());
    }

    public function testProperties()
    {
        $phpunit = $this;
        $beforeSend = false;

        $email = $this->_email([
            'from' => 'no-reply@supercompany.com',
            'fromName' => 'Super Company NoReply',
            'to' => 'someone@gmail.com',
            'replyTo' => 'reply@supercompany.com',
            'replyToName' => 'Super Company Reply',
            'subject' => 'Thank you for your contact request',
            'cc' => [
                'marketing@supercompany.com',
                'sales@supercompany.com' => 'Super Company Sales'
            ],
            'bcc' => [
                'support@supercompany.com' => 'Super Company Support'
            ],
            'body' => [
                'text' => 'We will never reply',
                'html' => '<strong>We will never reply</strong>',
            ],
            'attachments' => [
                __DIR__ . '/fixtures/files/test.jpg'
            ],
            'beforeSend' => function (\PHPMailer\PHPMailer\PHPMailer $mailer) use ($phpunit, &$beforeSend) {
                $phpunit->assertInstanceOf('PHPMailer\PHPMailer\PHPMailer', $mailer);
                $phpunit->assertSame('mail', $mailer->Mailer);
                $phpunit->assertSame('no-reply@supercompany.com', $mailer->From);
                $phpunit->assertSame('Super Company NoReply', $mailer->FromName);
                $phpunit->assertSame([['someone@gmail.com', '']], $mailer->getToAddresses());
                $phpunit->assertSame([
                    'reply@supercompany.com' => ['reply@supercompany.com', 'Super Company Reply']
                ], $mailer->getReplyToAddresses());
                $phpunit->assertSame('text/html', $mailer->ContentType);
                $phpunit->assertSame('<strong>We will never reply</strong>', $mailer->Body);
                $phpunit->assertSame('We will never reply', $mailer->AltBody);
                $phpunit->assertSame([
                    ['marketing@supercompany.com', ''],
                    ['sales@supercompany.com', 'Super Company Sales']
                ], $mailer->getCcAddresses());
                $phpunit->assertSame([
                    ['support@supercompany.com', 'Super Company Support']
                ], $mailer->getBccAddresses());
                $phpunit->assertSame([
                    [
                        __DIR__ . '/fixtures/files/test.jpg',
                        'test.jpg',
                        'test.jpg',
                        'base64',
                        'image/jpeg',
                        false,
                        'attachment',
                        'test.jpg',
                    ]
                ], $mailer->getAttachments());

                $beforeSend = true;
            }
        ], PHPMailer::class);

        $this->assertFalse($beforeSend);
        $this->assertFalse($email->isSent());
        $email->send(true);
        $this->assertTrue($email->isSent());
        $this->assertTrue($beforeSend);
    }

    public function testBeforeSendInvalid()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('"beforeSend" option return should be instance of PHPMailer\PHPMailer\PHPMailer class');

        $email = $this->_email([
            'to' => 'someone@gmail.com',
            'subject' => 'Thank you for your contact request',
            'beforeSend' => function (\PHPMailer\PHPMailer\PHPMailer $mailer) {
                return 'yay';
            }
        ], PHPMailer::class);

        $this->assertFalse($email->isSent());
        $email->send(true);
    }

    public function testSMTPTransportDefaults()
    {
        $phpunit = $this;
        $beforeSend = false;

        $email = $this->_email([
            'transport' => [
                'type' => 'smtp'
            ],
            'to' => 'someone@gmail.com',
            'subject' => 'Thank you for your contact request',
            'beforeSend' => function (\PHPMailer\PHPMailer\PHPMailer $mailer) use ($phpunit, &$beforeSend) {
                $phpunit->assertInstanceOf('PHPMailer\PHPMailer\PHPMailer', $mailer);
                $phpunit->assertSame('smtp', $mailer->Mailer);
                $phpunit->assertSame(null, $mailer->Host);
                $phpunit->assertSame(false, $mailer->SMTPAuth);
                $phpunit->assertSame(null, $mailer->Username);
                $phpunit->assertSame(null, $mailer->Password);
                $phpunit->assertSame('ssl', $mailer->SMTPSecure);
                $phpunit->assertSame(null, $mailer->Port);

                $beforeSend = true;
            }
        ], PHPMailer::class);

        $this->assertFalse($beforeSend);
        $this->assertFalse($email->isSent());
        $email->send(true);
        $this->assertTrue($email->isSent());
        $this->assertTrue($beforeSend);
    }

    public function testSMTPTransportSecurity1()
    {
        $phpunit = $this;
        $beforeSend = false;

        $email = $this->_email([
            'transport' => [
                'type'     => 'smtp',
                'security' => true
            ],
            'to' => 'someone@gmail.com',
            'subject' => 'Thank you for your contact request',
            'beforeSend' => function (\PHPMailer\PHPMailer\PHPMailer $mailer) use ($phpunit, &$beforeSend) {
                $phpunit->assertInstanceOf('PHPMailer\PHPMailer\PHPMailer', $mailer);
                $phpunit->assertSame('smtp', $mailer->Mailer);
                $phpunit->assertSame('tls', $mailer->SMTPSecure);
                $phpunit->assertSame(587, $mailer->Port);

                $beforeSend = true;
            }
        ], PHPMailer::class);

        $this->assertFalse($beforeSend);
        $this->assertFalse($email->isSent());
        $email->send(true);
        $this->assertTrue($email->isSent());
        $this->assertTrue($beforeSend);
    }

    public function testSMTPTransportSecurity2()
    {
        $phpunit = $this;
        $beforeSend = false;

        $email = $this->_email([
            'transport' => [
                'type'     => 'smtp',
                'security' => true,
                'port'     => 587
            ],
            'to' => 'someone@gmail.com',
            'subject' => 'Thank you for your contact request',
            'beforeSend' => function (\PHPMailer\PHPMailer\PHPMailer $mailer) use ($phpunit, &$beforeSend) {
                $phpunit->assertInstanceOf('PHPMailer\PHPMailer\PHPMailer', $mailer);
                $phpunit->assertSame('smtp', $mailer->Mailer);
                $phpunit->assertSame('tls', $mailer->SMTPSecure);
                $phpunit->assertSame(587, $mailer->Port);

                $beforeSend = true;
            }
        ], PHPMailer::class);

        $this->assertFalse($beforeSend);
        $this->assertFalse($email->isSent());
        $email->send(true);
        $this->assertTrue($email->isSent());
        $this->assertTrue($beforeSend);
    }

    public function testSMTPTransportSecurity3()
    {
        $phpunit = $this;
        $beforeSend = false;

        $email = $this->_email([
            'transport' => [
                'type'     => 'smtp',
                'security' => true,
                'port'     => 465
            ],
            'to' => 'someone@gmail.com',
            'subject' => 'Thank you for your contact request',
            'beforeSend' => function (\PHPMailer\PHPMailer\PHPMailer $mailer) use ($phpunit, &$beforeSend) {
                $phpunit->assertInstanceOf('PHPMailer\PHPMailer\PHPMailer', $mailer);
                $phpunit->assertSame('smtp', $mailer->Mailer);
                $phpunit->assertSame('ssl', $mailer->SMTPSecure);
                $phpunit->assertSame(465, $mailer->Port);

                $beforeSend = true;
            }
        ], PHPMailer::class);

        $this->assertFalse($beforeSend);
        $this->assertFalse($email->isSent());
        $email->send(true);
        $this->assertTrue($email->isSent());
        $this->assertTrue($beforeSend);
    }

    public function testSMTPTransportSecurityInvalid()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Could not automatically detect the "security" protocol from the "port" option, please set it explicitly to "tls" or "ssl".');

        $email = $this->_email([
            'transport' => [
                'type'     => 'smtp',
                'security' => true,
                'port'     => 1234
            ],
            'to' => 'someone@gmail.com',
            'subject' => 'Thank you for your contact request'
        ], PHPMailer::class);

        $this->assertFalse($email->isSent());
        $email->send(true);
    }
}
