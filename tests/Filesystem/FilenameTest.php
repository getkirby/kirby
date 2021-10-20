<?php

namespace Kirby\Filesystem;

use PHPUnit\Framework\TestCase as TestCase;

/**
 * @coversDefaultClass \Kirby\Filesystem\Filename
 */
class FilenameTest extends TestCase
{
    /**
     * @covers ::attributesToArray
     */
    public function testAttributesToArray()
    {
        $name = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}', [
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
                '-crop',
                [
                    'crop' => 'center',
                ]
            ],
        ];
    }

    /**
     * @covers ::attributesToString
     * @dataProvider attributesToStringProvider
     */
    public function testAttributesToString($expected, $options)
    {
        $name = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}', $options);

        $this->assertSame($expected, $name->attributesToString('-'));
    }

    /**
     * @covers ::attributesToString
     */
    public function testAttributesToStringWithoutAttrs()
    {
        $name = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}', []);
        $this->assertSame('', $name->attributesToString());
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
     * @covers ::blur
     * @dataProvider blurOptionProvider
     */
    public function testBlur($value, $expected)
    {
        $name = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}', [
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
     * @covers ::crop
     * @dataProvider cropAnchorProvider
     */
    public function testCrop($anchor, $expected)
    {
        $name = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}', [
            'crop' => $anchor
        ]);

        $this->assertSame($expected, $name->crop());
    }

    /**
     * @covers ::crop
     */
    public function testCropEmpty()
    {
        $name = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}');
        $this->assertFalse($name->crop());
    }

    /**
     * @covers ::crop
     */
    public function testCropDisabled()
    {
        $name = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}', [
            'crop' => false
        ]);

        $this->assertFalse($name->crop());
    }

    /**
     * @covers ::crop
     */
    public function testCropCustom()
    {
        $name = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}', [
            'crop' => 'something'
        ]);

        $this->assertSame('something', $name->crop());
    }

    /**
     * @covers ::dimensions
     */
    public function testDimensions()
    {
        $name = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}', $dimensions = [
            'width'  => 300,
            'height' => 200
        ]);

        $this->assertSame($dimensions, $name->dimensions());
    }

    /**
     * @covers ::dimensions
     */
    public function testDimensionsEmpty()
    {
        $name = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}');

        $this->assertSame([], $name->dimensions());
    }

    /**
     * @covers ::dimensions
     */
    public function testDimensionsWithoutWidth()
    {
        $name = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}', [
            'height' => 300
        ]);

        $this->assertSame([
            'width'  => null,
            'height' => 300
        ], $name->dimensions());
    }

    /**
     * @covers ::dimensions
     */
    public function testDimensionsWithoutHeight()
    {
        $name = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}', [
            'width' => 300
        ]);

        $this->assertSame([
            'width'  => 300,
            'height' => null
        ], $name->dimensions());
    }

    /**
     * @covers ::extension
     */
    public function testExtension()
    {
        $name = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}');
        $this->assertSame('jpg', $name->extension());
    }

    /**
     * @covers ::extension
     */
    public function testExtensionUppercase()
    {
        $name = new Filename('/test/some-file.JPG', '{{ name }}.{{ extension }}');
        $this->assertSame('jpg', $name->extension());
    }

    /**
     * @covers ::extension
     */
    public function testExtensionJpeg()
    {
        $name = new Filename('/test/some-file.jpeg', '{{ name }}.{{ extension }}');
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
     * @covers ::grayscale
     * @dataProvider grayscaleOptionProvider
     */
    public function testGrayscale($prop, $value, $expected)
    {
        $name = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}', [
            $prop => $value
        ]);

        $this->assertSame($expected, $name->grayscale());
    }

    /**
     * @covers ::name
     */
    public function testName()
    {
        $name = new Filename('/var/www/some-file.jpg', '{{ name }}.{{ extension }}');
        $this->assertSame('some-file', $name->name());
    }

    /**
     * @covers ::name
     */
    public function testNameSanitization()
    {
        $name = new Filename('/var/www/söme file.jpg', '{{ name }}.{{ extension }}');
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
     * @covers ::quality
     * @dataProvider qualityOptionProvider
     */
    public function testQuality($value, $expected)
    {
        $name = new Filename('/test/some-file.jpg', 'some-file.jpg', [
            'quality' => $value
        ]);

        $this->assertSame($expected, $name->quality());
    }

    /**
     * @covers ::toString
     * @covers ::__toString
     * @dataProvider attributesToStringProvider
     */
    public function testToString($expected, $attributes)
    {
        $name = new Filename('/test/some-file.jpg', '{{ name }}{{ attributes }}.{{ extension }}', $attributes);

        $this->assertSame('some-file' . $expected . '.jpg', $name->toString());
        $this->assertSame('some-file' . $expected . '.jpg', (string)$name);
    }

    /**
     * @covers ::toString
     * @ocvers ::__toString
     */
    public function testToStringWithFalsyAttributes()
    {
        $name = new Filename('/test/some-file.jpg', '{{ name }}{{ attributes }}.{{ extension }}', [
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

    /**
     * @covers ::toString
     * @ocvers ::__toString
     */
    public function testToStringWithoutAttributes()
    {
        $name = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}');
        $this->assertSame('some-file.jpg', $name->toString());
        $this->assertSame('some-file.jpg', (string)$name);
    }
}
