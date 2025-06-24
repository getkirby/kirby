<?php

namespace Kirby\Image;

use ReflectionClass;

class ExifTest extends TestCase
{
	protected function _exif($filename = 'image/cat.jpg')
	{
		$image = new Image(static::FIXTURES . '/' . $filename);
		return new Exif($image);
	}

	public function testData()
	{
		$exif  = $this->_exif();
		$this->assertSame([
			'FileName'      => 'cat.jpg',
			'FileDateTime'  => exif_read_data(static::FIXTURES . '/image/cat.jpg')['FileDateTime'],
			'FileSize'      => 23574,
			'FileType'      => 2,
			'MimeType'      => 'image/jpeg',
			'SectionsFound' => '',
			'COMPUTED'      => [
				'html'      => 'width="500" height="500"',
				'Height'    => 500,
				'Width'     => 500,
				'IsColor'   => 1
			]
		], $exif->data());
	}

	public function testCamera()
	{
		$exif = $this->_exif();
		$this->assertInstanceOf(Camera::class, $exif->camera());

		// from cache
		$this->assertInstanceOf(Camera::class, $exif->camera());
	}

	public function testLocation()
	{
		$exif = $this->_exif();
		$this->assertInstanceOf(Location::class, $exif->location());

		// from cache
		$this->assertInstanceOf(Location::class, $exif->location());
	}

	public function testTimestamp()
	{
		$exif  = $this->_exif();
		$this->assertSame((string)exif_read_data(static::FIXTURES . '/image/cat.jpg')['FileDateTime'], $exif->timestamp());
	}

	public function testExposure()
	{
		$exif  = $this->_exif();
		$this->assertNull($exif->exposure());
	}

	public function testAperture()
	{
		$exif  = $this->_exif();
		$this->assertNull($exif->aperture());
	}

	public function testIso()
	{
		$exif  = $this->_exif();
		$this->assertNull($exif->iso());
	}

	public function testIsColor()
	{
		$exif  = $this->_exif();
		$this->assertTrue($exif->isColor());
	}

	public function testIsBw()
	{
		$exif  = $this->_exif();
		$this->assertFalse($exif->isBw());
	}

	public function testFocalLength()
	{
		$exif  = $this->_exif();
		$this->assertNull($exif->focalLength());
	}

	public function testOrientation()
	{
		$exif  = $this->_exif('orientation/Landscape_0.jpg');
		$this->assertSame(0, $exif->orientation());

		$exif  = $this->_exif('orientation/Landscape_6.jpg');
		$this->assertSame(6, $exif->orientation());
	}

	public function testParseTimestampDateTimeOriginal()
	{
		$exif = $this->_exif();

		// changing protected property $data via Reflection class
		$ref = new ReflectionClass($exif);
		$data = $ref->getProperty('data');
		$data->setAccessible(true);
		$options = $data->getValue($exif);
		$options['DateTimeOriginal'] = '11.12.2016 11:13:14';
		$data->setValue($exif, $options);

		// setting protected method public
		$parse = $ref->getMethod('parseTimestamp');
		$parse->setAccessible(true);

		$this->assertSame((string)strtotime('11.12.2016 11:13:14'), $parse->invoke($exif));
	}

	public function testToArray()
	{
		$exif  = $this->_exif();
		$this->assertIsArray($exif->toArray());
		$this->assertIsArray($exif->__debugInfo());
	}
}
