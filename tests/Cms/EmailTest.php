<?php

namespace Kirby\Cms;

class EmailTest extends TestCase
{

    protected function _email($props = []) {
        return array_merge([
            'from' => 'no-reply@supercompany.com',
            'to' => 'someone@gmail.com',
            'subject' => 'Thank you for your contact request',
            'body' => 'We will never reply',
            'send' => false
        ], $props);
    }

    public function testToArray()
    {
        $email = new Email($this->_email());
        $this->assertEquals($this->_email(), $email->toArray());
    }

    public function testEmailPresets()
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

        $email = new Email('contact', $this->_email([
            'to' => $to = 'nobody@web.de'
        ]));

        $this->assertEquals($to, $email->toArray()['to']);
        $this->assertEquals($cc, $email->toArray()['cc']);
    }

    public function testEmailTemplate()
    {
        $app = new App([
            'roots' => [
                'emails' => __DIR__ . '/fixtures/emails'
            ]
        ]);
        $email = new Email($this->_email([
            'template' => 'contact',
            'data' => [
                'name' => 'Alex'
            ]
        ]));
        $this->assertEquals('Welcome to Kirby, Alex!', $email->toArray()['body']);
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage The email "subscription" cannot be found
     */
    public function testEmailInvalidTemplate()
    {
        $email = new Email($this->_email([
            'template' => 'subscription'
        ]));
    }

    public function testEmailWithObjects()
    {
        $app = new App([
            'site' => new Site()
        ]);

        $from = new User(['email' => 'sales@company.com']);
        $to = new User(['email' => 'ceo@company.com']);

        $file = new File([
            'filename' => 'report.pdf',
            'parent' =>  $app->site()
        ]);
        $image = new File([
            'filename' => 'graph.png',
            'parent' =>  $app->site()
        ]);

        $email = new Email($this->_email([
            'from' => $from,
            'to' => [
                $to,
                'someone@gmail.com'
            ],
            'attachments' => [
                $file,
                $image
            ]
        ]));

        $this->assertEquals('sales@company.com', $email->toArray()['from']);
        $this->assertEquals([
            'ceo@company.com',
            'someone@gmail.com'
        ], $email->toArray()['to']);
        $this->assertEquals([
            '/media/site/report.pdf',
            '/media/site/graph.png'
        ], $email->toArray()['attachments']);
    }

    public function testEmailWithCollectionObject()
    {
        $to = new Users([
            new User(['email' => 'ceo@company.com']),
            new User(['email' => 'marketing@company.com'])
        ]);

        $email = new Email($this->_email([
            'to' => $to
        ]));

        $this->assertEquals([
            'ceo@company.com',
            'marketing@company.com'
        ], $email->toArray()['to']);
    }

}
