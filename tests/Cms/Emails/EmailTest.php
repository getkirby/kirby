<?php

namespace Kirby\Cms;

use PHPMailer\PHPMailer\PHPMailer as Mailer;

class EmailTest extends TestCase
{
    public function testToArray()
    {
        $props = [
            'one' => 'eins',
            'two' => 'zwei',
        ];

        $expected = [
            'one'         => 'eins',
            'two'         => 'zwei',
            'transport'   => [],
            'from'        => null,
            'fromName'    => null,
            'replyTo'     => null,
            'replyToName' => null,
            'to'          => [],
            'cc'          => [],
            'bcc'         => [],
            'attachments' => [],
            'beforeSend'  => null
        ];

        $email = new Email($props);
        $this->assertEquals($expected, $email->toArray());
    }

    public function testPresets()
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

        $this->assertEquals([$to], $email->toArray()['to']);
        $this->assertEquals([$cc], $email->toArray()['cc']);
    }

    public function testInvalidPreset()
    {
        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionCode('error.email.preset.notFound');

        $email = new Email('not-a-preset', []);
    }

    public function testTemplate()
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

    public function testTemplateHtml()
    {
        $app = new App([
            'templates' => [
                'emails/media.html' => __DIR__ . '/fixtures/emails/media.html.php'
            ]
        ]);
        $email = new Email(['template' => 'media']);
        $this->assertEquals([
            'html' => '<b>Image:</b> <img src=""/>'
        ], $email->toArray()['body']);
    }

    public function testTemplateHtmlText()
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

    public function testInvalidTemplate()
    {
        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('The email template "subscription" cannot be found');

        $email = new Email([
            'template' => 'subscription'
        ]);
    }

    public function testTransformSimple()
    {
        $email = new Email([
            'from'     => 'sales@company.com',
            'fromName' => 'Company Sales',
            'to'       => 'ceo@company.com',
            'cc'       => [
                'someone@gmail.com',
                'another@gmail.com' => 'Another Gmail',
            ],
            'attachments' => [
                '/amazing/absolute/path.txt'
            ]
        ]);

        $this->assertEquals('sales@company.com', $email->toArray()['from']);
        $this->assertEquals('Company Sales', $email->toArray()['fromName']);
        $this->assertEquals(['ceo@company.com'], $email->toArray()['to']);
        $this->assertEquals([
            'someone@gmail.com',
            'another@gmail.com' => 'Another Gmail'
        ], $email->toArray()['cc']);
        $this->assertEquals([
            '/amazing/absolute/path.txt'
        ], $email->toArray()['attachments']);
    }

    public function testTransformComplex()
    {
        $app = new App([
            'site' => new Site(),
            'roots' => [
                'content' => '/content'
            ]
        ]);

        $from = new User(['email' => 'sales@company.com', 'name' => 'Company Sales']);
        $to = new User(['email' => 'ceo@company.com', 'name' => 'Company CEO']);

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
            'fromName' => 'Amazing Sales!',
            'replyTo' => $from,
            'to' => [
                $to,
                'someone@gmail.com',
                'another@gmail.com' => 'Another Gmail'
            ],
            'attachments' => [
                $file,
                $image,
                '/amazing/absolute/path.txt'
            ]
        ]);

        $this->assertEquals('sales@company.com', $email->toArray()['from']);
        $this->assertEquals('Amazing Sales!', $email->toArray()['fromName']);
        $this->assertEquals('sales@company.com', $email->toArray()['replyTo']);
        $this->assertEquals('Company Sales', $email->toArray()['replyToName']);
        $this->assertEquals([
            'ceo@company.com' => 'Company CEO',
            'someone@gmail.com',
            'another@gmail.com' => 'Another Gmail'
        ], $email->toArray()['to']);
        $this->assertEquals([
            '/content/report.pdf',
            '/content/graph.png',
            '/amazing/absolute/path.txt'
        ], $email->toArray()['attachments']);
    }

    public function testTransformCollection()
    {
        $to = new Users([
            new User(['email' => 'ceo@company.com', 'name' => 'Company CEO']),
            new User(['email' => 'marketing@company.com', 'name' => 'Company Marketing'])
        ]);

        $email = new Email(['to' => $to]);

        $this->assertEquals([
            'ceo@company.com' => 'Company CEO',
            'marketing@company.com' => 'Company Marketing'
        ], $email->toArray()['to']);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testUserData()
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

        $this->assertEquals(['ceo@company.com' => 'Mario'], $email->toArray()['to']);
        $this->assertEquals('Welcome, Mario!', trim($email->toArray()['body']));
    }

    public function testBeforeSend()
    {
        new App([
            'options' => [
                'email' => [
                    'beforeSend' => function ($mailer) {
                        $mailer->SMTPOptions = [
                            'ssl' => [
                                'verify_peer'       => false,
                                'verify_peer_name'  => false,
                                'allow_self_signed' => true
                            ]
                        ];

                        return $mailer;
                    }
                ]
            ]
        ]);

        $email = new Email([
            'to' => 'ceo@company.com'
        ]);
        $beforeSend = $email->toArray()['beforeSend'];

        $this->assertInstanceOf('Closure', $beforeSend);
        $this->assertInstanceOf('PHPMailer\PHPMailer\PHPMailer', $beforeSend(new Mailer()));
    }
}
