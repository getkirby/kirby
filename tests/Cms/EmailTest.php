<?php

namespace Kirby\Cms;

class EmailTest extends TestCase
{

    public function testToArray()
    {
        $props = [
            'one' => 'eins',
            'two' => 'zwei',
            'three' => 'drei'
        ];
        $email = new Email($props);
        $this->assertEquals($props, $email->toArray());
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

        $email = new Email('contact', [
            'to' => $to = 'nobody@web.de'
        ]);

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
        $email = new Email([
            'template' => 'contact',
            'data' => [
                'name' => 'Alex'
            ]
        ]);
        $this->assertEquals('Welcome to Kirby, Alex!', $email->toArray()['body']);
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage The email "subscription" cannot be found
     */
    public function testEmailInvalidTemplate()
    {
        $email = new Email([
            'template' => 'subscription'
        ]);
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

        $email = new Email([
            'from' => $from,
            'to' => [
                $to,
                'someone@gmail.com'
            ],
            'attachments' => [
                $file,
                $image
            ]
        ]);

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

        $email = new Email(['to' => $to]);

        $this->assertEquals([
            'ceo@company.com',
            'marketing@company.com'
        ], $email->toArray()['to']);
    }

    public function testEmailUserData()
    {

        $app = new App([
            'roots' => [
                'emails' => __DIR__ . '/fixtures/emails'
            ]
        ]);

        $user =  new User([
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

        $this->assertEquals('ceo@company.com', $email->toArray()['to']);
        $this->assertEquals('Welcome, Mario!', $email->toArray()['body']);

    }

}
