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
}
