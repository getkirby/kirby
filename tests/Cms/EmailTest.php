<?php

namespace Kirby\Cms;

class EmailTest extends TestCase
{
    public function testToArray()
    {
        $props = [
            'one' => 'eins',
            'two' => 'zwei',
        ];
        $email = new Email($props);
        $this->assertEquals($props + ['transport' => []], $email->toArray());
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

    public function testEmailInvalidPreset()
    {
        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionCode('error.email.preset.notFound');

        $email = new Email('not-a-preset', []);
    }

    public function testEmailTemplate()
    {
        $app = new App([
            'templates' => [
                'emails/contact' => __DIR__ . '/fixtures/emails/contact.php'
            ]
        ]);
        $email = new Email([
            'template' => 'contact',
            'data' => [
                'name' => 'Alex'
            ]
        ]);
        $this->assertEquals('Cheers, Alex!', $email->toArray()['body']);
    }

    public function testEmailTemplateHtml()
    {
        $app = new App([
            'templates' => [
                'emails/media.html' => __DIR__ . '/fixtures/emails/media.html.php',
                'emails/media.text' => __DIR__ . '/fixtures/emails/media.text.php',
            ]
        ]);
        $email = new Email(['template' => 'media']);
        $this->assertEquals([
            'html' => '<b>Image:</b> <img src=""/>',
            'text' => 'Image: Description'
        ], $email->toArray()['body']);
    }

    public function testEmailInvalidTemplate()
    {
        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('The email template "subscription" cannot be found');

        $email = new Email([
            'template' => 'subscription'
        ]);
    }

    public function testEmailWithObjects()
    {
        $app = new App([
            'site' => new Site(),
            'roots' => [
                'content' => '/content'
            ]
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
            '/content/report.pdf',
            '/content/graph.png'
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

    /**
     * @runInSeparateProcess
     */
    public function testEmailUserData()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'templates' => [
                'emails/user-info' => __DIR__ . '/fixtures/emails/user-info.php'
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

        $this->assertEquals('ceo@company.com', $email->toArray()['to']);
        $this->assertEquals('Welcome, Mario!', trim($email->toArray()['body']));
    }
}
