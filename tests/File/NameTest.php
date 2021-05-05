<?php

namespace Kirby\File;

use PHPUnit\Framework\TestCase as TestCase;

class NameTest extends TestCase
{
    public function testAttributesToArray()
    {
        $name = new Name('/test/some-file.jpg', '{{ name }}.{{ extension }}', [
            'width'     => 300,
            'height'    => 200,
            'crop'      => 'top left',
            'grayscale' => true,
            'blur'      => 10,
            'quality'   => 90
        ]);

        $expected = [
            'dimensions' => '300x200',
            'crop'       => 'top-left',
            'blur'       => 10,
            'bw'         => true,
            'q'          => 90
        ];

        $this->assertSame($expected, $name->attributesToArray());
    }

    public function attributesToStringProvider()
    {
        return [
            [
                '-300x200-crop-top-left-blur10-bw-q90',
                [
                    'width'     => 300,
                    'height'    => 200,
                    'crop'      => 'top left',
                    'grayscale' => true,
                    'blur'      => 10,
                    'quality'   => 90
                ]
            ],
            [
                '-300x200',
                [
                    'width'  => 300,
                    'height' => 200,
                ]
            ],
            [
                '-x200',
                [
                    'height' => 200,
                ]
            ],
            [
                '',
                [
                    'crop' => 'center',
                ]
            ],
        ];
    }

    /**
     * @dataProvider attributesToStringProvider
     */
    public function testAttributesToString($expected, $options)
    {
        $name = new Name('/test/some-file.jpg', '{{ name }}.{{ extension }}', $options);

        $this->assertSame($expected, $name->attributesToString('-'));
    }

    public function blurOptionProvider()
    {
        return [
            [false, false],
            [true, 1],
            [90, 90],
            [90.0, 90],
            ['90', 90],
        ];
    }

    /**
     * @dataProvider blurOptionProvider
     */
    public function testBlur($value, $expected)
    {
        $name = new Name('/test/some-file.jpg', '{{ name }}.{{ extension }}', [
            'blur' => $value
        ]);

        $this->assertSame($expected, $name->blur());
    }

    public function cropAnchorProvider(): array
    {
        return [
            ['center', 'center'],
            ['top', 'top'],
            ['bottom', 'bottom'],
            ['left', 'left'],
            ['right', 'right'],
            ['top left', 'top-left'],
            ['top right', 'top-right'],
            ['bottom left', 'bottom-left'],
            ['bottom right', 'bottom-right'],
        ];
    }

    /**
     * @dataProvider cropAnchorProvider
     */
    public function testCrop($anchor, $expected)
    {
        $name = new Name('/test/some-file.jpg', '{{ name }}.{{ extension }}', [
            'crop' => $anchor
        ]);

        $this->assertSame($expected, $name->crop());
    }

    public function testEmptyCrop()
    {
        $name = new Name('/test/some-file.jpg', '{{ name }}.{{ extension }}');
        $this->assertFalse($name->crop());
    }

    public function testDisabledCrop()
    {
        $name = new Name('/test/some-file.jpg', '{{ name }}.{{ extension }}', [
            'crop' => false
        ]);

        $this->assertFalse($name->crop());
    }

    public function testCustomCrop()
    {
        $name = new Name('/test/some-file.jpg', '{{ name }}.{{ extension }}', [
            'crop' => 'something'
        ]);

        $this->assertSame('something', $name->crop());
    }

    public function testDimensions()
    {
        $name = new Name('/test/some-file.jpg', '{{ name }}.{{ extension }}', $dimensions = [
            'width'  => 300,
            'height' => 200
        ]);

        $this->assertSame($dimensions, $name->dimensions());
    }

    public function testEmptyDimensions()
    {
        $name = new Name('/test/some-file.jpg', '{{ name }}.{{ extension }}');

        $this->assertSame([], $name->dimensions());
    }

    public function testDimensionsWithoutWidth()
    {
        $name = new Name('/test/some-file.jpg', '{{ name }}.{{ extension }}', [
            'height' => 300
        ]);

        $this->assertSame([
            'width'  => null,
            'height' => 300
        ], $name->dimensions());
    }

    public function testDimensionsWithoutHeight()
    {
        $name = new Name('/test/some-file.jpg', '{{ name }}.{{ extension }}', [
            'width' => 300
        ]);

        $this->assertSame([
            'width'  => 300,
            'height' => null
        ], $name->dimensions());
    }

    public function testExtension()
    {
        $name = new Name('/test/some-file.jpg', '{{ name }}.{{ extension }}');
        $this->assertSame('jpg', $name->extension());
    }

    public function testUppercaseExtension()
    {
        $name = new Name('/test/some-file.JPG', '{{ name }}.{{ extension }}');
        $this->assertSame('jpg', $name->extension());
    }

    public function testJpegExtension()
    {
        $name = new Name('/test/some-file.jpeg', '{{ name }}.{{ extension }}');
        $this->assertSame('jpg', $name->extension());
    }

    public function grayscaleOptionProvider()
    {
        return [
            ['grayscale', true, true],
            ['grayscale', false, false],
            ['greyscale', true, true],
            ['greyscale', false, false],
            ['bw', true, true],
            ['bw', false, false],
        ];
    }

    /**
     * @dataProvider grayscaleOptionProvider
     */
    public function testGrayscale($prop, $value, $expected)
    {
        $name = new Name('/test/some-file.jpg', '{{ name }}.{{ extension }}', [
            $prop => $value
        ]);

        $this->assertSame($expected, $name->grayscale());
    }

    public function testName()
    {
        $name = new Name('/var/www/some-file.jpg', '{{ name }}.{{ extension }}');
        $this->assertSame('some-file', $name->name());
    }

    public function testNameSanitization()
    {
        $name = new Name('/var/www/söme file.jpg', '{{ name }}.{{ extension }}');
        $this->assertSame('some-file', $name->name());
    }

    public function qualityOptionProvider()
    {
        return [
            [false, false],
            [true, false],
            [90, 90],
            [90.0, 90],
            ['90', 90],
        ];
    }

    /**
     * @dataProvider qualityOptionProvider
     */
    public function testQuality($value, $expected)
    {
        $name = new Name('/test/some-file.jpg', 'some-file.jpg', [
            'quality' => $value
        ]);

        $this->assertSame($expected, $name->quality());
    }

    /**
     * @dataProvider attributesToStringProvider
     */
    public function testToString($expected, $attributes)
    {
        $name = new Name('/test/some-file.jpg', '{{ name }}{{ attributes }}.{{ extension }}', $attributes);

        $this->assertSame('some-file' . $expected . '.jpg', $name->toString());
        $this->assertSame('some-file' . $expected . '.jpg', (string)$name);
    }

    public function testToStringWithFalsyAttributes()
    {
        $name = new Name('/test/some-file.jpg', '{{ name }}{{ attributes }}.{{ extension }}', [
            'width'     => false,
            'height'    => false,
            'crop'      => false,
            'blur'      => false,
            'grayscale' => false,
            'quality'   => false
        ]);

        $this->assertSame('some-file.jpg', $name->toString());
        $this->assertSame('some-file.jpg', (string)$name);
    }

    public function testToStringWithoutAttributes()
    {
        $name = new Name('/test/some-file.jpg', '{{ name }}.{{ extension }}');
        $this->assertSame('some-file.jpg', $name->toString());
        $this->assertSame('some-file.jpg', (string)$name);
    }
}
