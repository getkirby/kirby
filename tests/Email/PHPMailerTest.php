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
}
