<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class FileModificationsTest extends TestCase
{
    protected $app;

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

    public function testThumbWithInvalidReturnValue()
    {
        $app = $this->app->clone([
            'components' => [
                'file::version' => function ($kirby, $file, $options = []) {
                    return 'image';
                }
            ]
        ]);

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The file::version component must return a File or FileVersion object');

        $file = $app->file('test.jpg');
        $file->thumb(['width' => 100]);
    }

    public function testThumbWithNoOptions()
    {
        $file = $this->app->file('test.jpg');
        $this->assertSame($file, $file->thumb([]));
    }

    public function testBlur()
    {
        $app = $this->app->clone([
            'components' => [
                'file::version' => function ($kirby, $file, $options = []) {
                    $this->assertEquals(['blur' => 5], $options);
                    return $file;
                }
            ]
        ]);

        $file = $app->file('test.jpg');
        $file->blur(5);
    }

    public function testBw()
    {
        $app = $this->app->clone([
            'components' => [
                'file::version' => function ($kirby, $file, $options = []) {
                    $this->assertEquals(['grayscale' => true], $options);
                    return $file;
                }
            ]
        ]);

        $file = $app->file('test.jpg');
        $file->bw();
    }

    public function cropOptions()
    {
        $field = new Field(null, 'crop', 'top left');

        return [
            [
                [300],
                [
                    'width' => 300,
                    'height' => null,
                    'quality' => null,
                    'crop' => 'center'
                ]
            ],
            [
                [300, 200],
                [
                    'width' => 300,
                    'height' => 200,
                    'quality' => null,
                    'crop' => 'center'
                ]
            ],
            [
                [300, 200, 10],
                [
                    'width' => 300,
                    'height' => 200,
                    'quality' => 10,
                    'crop' => 'center'
                ]
            ],
            [
                [300, 200, $field],
                [
                    'width' => 300,
                    'height' => 200,
                    'quality' => null,
                    'crop' => 'top left'
                ]
            ],
            [
                [300, 200, 'top left'],
                [
                    'width' => 300,
                    'height' => 200,
                    'quality' => null,
                    'crop' => 'top left'
                ]
            ],
            [
                [300, 200, ['crop' => 'top left', 'quality' => 20]],
                [
                    'width' => 300,
                    'height' => 200,
                    'quality' => 20,
                    'crop' => 'top left'
                ]
            ],
        ];
    }

    /**
     * @dataProvider cropOptions
     */
    public function testCrop($args, $expected)
    {
        $app = $this->app->clone([
            'components' => [
                'file::version' => function ($kirby, $file, $options = []) use ($expected) {
                    $this->assertEquals($expected, $options);
                    return $file;
                }
            ]
        ]);

        $file = $app->file('test.jpg');
        $file->crop(...$args);
    }

    public function testQuality()
    {
        $app = $this->app->clone([
            'components' => [
                'file::version' => function ($kirby, $file, $options = []) {
                    $this->assertEquals(['quality' => 10], $options);
                    return $file;
                }
            ]
        ]);

        $file = $app->file('test.jpg');
        $file->quality(10);
    }

    public function testResize()
    {
        $app = $this->app->clone([
            'components' => [
                'file::version' => function ($kirby, $file, $options = []) {
                    $this->assertEquals(['width' => 100, 'height' => 200, 'quality' => 10], $options);
                    return $file;
                }
            ]
        ]);

        $file = $app->file('test.jpg');
        $file->resize(100, 200, 10);
    }
}
