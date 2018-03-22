<?php

namespace Kirby\Email;

class PHPMailerTest extends TestCase
{

    protected function _email($props = [], $send = true) {
        return parent::_email(PHPMailer::class, $props, $send);
    }

    public function testSend()
    {
        $email = $this->_email([
            'to' => 'test@test.com'
        ], false);
        $this->assertFalse($email->isSent());
        $email->send(true);
        $this->assertTrue($email->isSent());
    }

}
