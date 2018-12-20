<?php

namespace Kirby\Cms;

class FilenameTest extends TestCase
{
    public function testAttributesToArray()
    {
        $filename = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}', [
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

        $this->assertEquals($expected, $filename->attributesToArray());
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
        $filename = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}', $options);

        $this->assertEquals($expected, $filename->attributesToString('-'));
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
        $filename = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}', [
            'blur' => $value
        ]);

        $this->assertEquals($expected, $filename->blur());
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
        $filename = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}', [
            'crop' => $anchor
        ]);

        $this->assertEquals($expected, $filename->crop());
    }

    public function testEmptyCrop()
    {
        $filename = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}');
        $this->assertFalse($filename->crop());
    }

    public function testDisabledCrop()
    {
        $filename = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}', [
            'crop' => false
        ]);

        $this->assertFalse($filename->crop());
    }

    public function testCustomCrop()
    {
        $filename = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}', [
            'crop' => 'something'
        ]);

        $this->assertEquals('something', $filename->crop());
    }

    public function testDimensions()
    {
        $filename = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}', $dimensions = [
            'width'  => 300,
            'height' => 200
        ]);

        $this->assertEquals($dimensions, $filename->dimensions());
    }

    public function testEmptyDimensions()
    {
        $filename = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}');

        $this->assertEquals([], $filename->dimensions());
    }

    public function testDimensionsWithoutWidth()
    {
        $filename = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}', [
            'height' => 300
        ]);

        $this->assertEquals([
            'width'  => null,
            'height' => 300
        ], $filename->dimensions());
    }

    public function testDimensionsWithoutHeight()
    {
        $filename = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}', [
            'width' => 300
        ]);

        $this->assertEquals([
            'width'  => 300,
            'height' => null
        ], $filename->dimensions());
    }

    public function testExtension()
    {
        $filename = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}');
        $this->assertEquals('jpg', $filename->extension());
    }

    public function testUppercaseExtension()
    {
        $filename = new Filename('/test/some-file.JPG', '{{ name }}.{{ extension }}');
        $this->assertEquals('jpg', $filename->extension());
    }

    public function testJpegExtension()
    {
        $filename = new Filename('/test/some-file.jpeg', '{{ name }}.{{ extension }}');
        $this->assertEquals('jpg', $filename->extension());
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
        $filename = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}', [
            $prop => $value
        ]);

        $this->assertEquals($expected, $filename->grayscale());
    }

    public function testName()
    {
        $filename = new Filename('/var/www/some-file.jpg', '{{ name }}.{{ extension }}');
        $this->assertEquals('some-file', $filename->name());
    }

    public function testNameSanitization()
    {
        $filename = new Filename('/var/www/söme file.jpg', '{{ name }}.{{ extension }}');
        $this->assertEquals('some-file', $filename->name());
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
        $filename = new Filename('/test/some-file.jpg', 'some-file.jpg', [
            'quality' => $value
        ]);

        $this->assertEquals($expected, $filename->quality());
    }

    /**
     * @dataProvider attributesToStringProvider
     */
    public function testToString($expected, $attributes)
    {
        $filename = new Filename('/test/some-file.jpg', '{{ name }}{{ attributes }}.{{ extension }}', $attributes);

        $this->assertEquals('some-file' . $expected . '.jpg', $filename->toString());
        $this->assertEquals('some-file' . $expected . '.jpg', (string)$filename);
    }

    public function testToStringWithFalsyAttributes()
    {
        $filename = new Filename('/test/some-file.jpg', '{{ name }}{{ attributes }}.{{ extension }}', [
            'width'     => false,
            'height'    => false,
            'crop'      => false,
            'blur'      => false,
            'grayscale' => false,
            'quality'   => false
        ]);

        $this->assertEquals('some-file.jpg', $filename->toString());
        $this->assertEquals('some-file.jpg', (string)$filename);
    }

    public function testToStringWithoutAttributes()
    {
        $filename = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}');
        $this->assertEquals('some-file.jpg', $filename->toString());
        $this->assertEquals('some-file.jpg', (string)$filename);
    }
}
