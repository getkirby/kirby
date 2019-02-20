<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class FileModificationsTest extends TestCase
{
    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'site' => [
                'files' => [
                    ['filename' => 'test.jpg']
                ]
            ]
        ]);
    }

    public function testThumb()
    {
        $input = [
            'width'  => 300,
            'height' => 200
        ];

        $app = $this->app->clone([
            'components' => [
                'file::version' => function ($kirby, $file, $options = []) use ($input) {
                    $this->assertEquals($input, $options);
                    return $file;
                }
            ]
        ]);

        $file = $app->file('test.jpg');
        $file->thumb($input);
    }

    public function testThumbWithDefaultPreset()
    {
        $app = $this->app->clone([
            'components' => [
                'file::version' => function ($kirby, $file, $options = []) {
                    $expected = [
                        'width' => 300
                    ];

                    $this->assertEquals($expected, $options);
                    return $file;
                }
            ],
            'options' => [
                'thumbs' => [
                    'presets' => [
                        'default' => ['width' => 300]
                    ]
                ]
            ]
        ]);

        $file = $app->file('test.jpg');
        $file->thumb();
        $file->thumb('default');
    }

    public function testThumbWithCustomPreset()
    {
        $app = $this->app->clone([
            'components' => [
                'file::version' => function ($kirby, $file, $options = []) {
                    $expected = [
                        'width' => 300
                    ];

                    $this->assertEquals($expected, $options);
                    return $file;
                }
            ],
            'options' => [
                'thumbs' => [
                    'presets' => [
                        'test' => ['width' => 300]
                    ]
                ]
            ]
        ]);

        $file = $app->file('test.jpg');
        $file->thumb('test');
    }
}
