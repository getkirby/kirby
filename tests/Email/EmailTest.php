<?php

namespace Kirby\Email;

class EmailTest extends TestCase
{
    protected function _email($props = [], $mailer = Email::class)
    {
        return parent::_email($props, $mailer);
    }

    public function testProperties()
    {
        $email = $this->_email([
            'from' => $form = 'no-reply@supercompany.com',
            'to' => $to = 'someone@gmail.com',
            'replyTo' => $replyTo = 'no-reply@supercompany.com',
            'subject' => $subject = 'Thank you for your contact request',
            'body' => $body = 'We will never reply',
            'cc' => $cc = [
                'marketing@supercompany.com',
                'sales@supercompany.com'
            ],
            'bcc' => $cc
        ]);

        $this->assertEquals($form, $email->from());
        $this->assertEquals([$to], $email->to());
        $this->assertEquals($replyTo, $email->replyTo());
        $this->assertEquals($subject, $email->subject());
        $this->assertEquals($cc, $email->cc());
        $this->assertEquals($cc, $email->bcc());

        $this->assertInstanceOf(Body::class, $email->body());
        $this->assertEquals($body, $email->body()->text());
        $this->assertEquals(null, $email->body()->html());

        $this->assertEquals(['type' => 'mail'], $email->transport());
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

        $this->assertEquals('', $email->replyTo());
        $this->assertEquals([], $email->cc());
        $this->assertEquals([], $email->bcc());
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
        $this->assertEquals($body['text'], $email->body()->text());
        $this->assertEquals($body['html'], $email->body()->html());

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

        $this->assertEquals($attachments, $email->attachments());
    }
}
