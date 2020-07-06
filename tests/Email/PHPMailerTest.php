<?php

namespace Kirby\Email;

use PHPMailer\PHPMailer\PHPMailer as MailProvider;

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

    public function testPhpMailerCallback()
    {
        $phpunit = $this;

        $mail = $this->_email([
            'transport' => $transport = [
                'type' => 'smtp',
                'host' => 'mail.getkirby.com',
                'port' => 465,
                'security' => true,
                'auth' => true,
                'username' => 'test@test.com',
                'password' => 'randomstring',
                'phpmailer' => $phpmailer = function ($mailer) use ($phpunit) {
                    $phpunit->assertInstanceOf('PHPMailer\PHPMailer\PHPMailer', $mailer);

                    $mailer->SMTPOptions = [
                        'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        ]
                    ];

                    return $mailer;
                }
            ]
        ]);

        $this->assertSame($transport, $mail->transport());
        $this->assertInstanceOf('Closure', $phpmailer);
        $this->assertInstanceOf('PHPMailer\PHPMailer\PHPMailer', $phpmailer(new MailProvider));
    }
}
