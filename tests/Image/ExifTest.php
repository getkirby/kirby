<?php

namespace Kirby\Image;

use Kirby\Filesystem\File;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Exif::class)]
class ExifTest extends TestCase
{
	protected function sections(): array
	{
		return [
			'IFD0' => [
				'Make'  => 'Kirby Kamera Inc.',
				'Model' => 'Deluxe Snap 3000'
			],
			'EXIF' => [
				'DateTimeOriginal' => '2016:12:11 11:13:14',
				'ExposureTime'     => '1/250',
				'FNumber'          => '28/10',
				'FocalLength'      => '50/1',
				'ISOSpeedRatings'  => 100
			],
			'GPS' => [
				'GPSLatitudeRef'  => 'N',
				'GPSLatitude'     => ['40/1', '15/1', '0/1'],
				'GPSLongitudeRef' => 'W',
				'GPSLongitude'    => ['75/1', '7/1', '0/1']
			],
			'THUMBNAIL' => [
				'Make' => 'Thumbnail'
			]
		];
	}

	public function testAperture(): void
	{
		$exif = new Exif($this->sections());
		$this->assertSame('f/2.8', $exif->aperture());

		$exif = new Exif([]);
		$this->assertNull($exif->aperture());

		$exif = new Exif(['EXIF' => ['FNumber' => '28/0']]);
		$this->assertNull($exif->aperture());
	}

	public function testCamera(): void
	{
		$exif = new Exif($this->sections());

		$this->assertSame('Kirby Kamera Inc.', $exif->camera()->make());
		$this->assertSame('Deluxe Snap 3000', $exif->camera()->model());

		// from cache
		$this->assertSame($exif->camera(), $exif->camera());
	}

	public function testData(): void
	{
		$exif = new Exif($this->sections());

		$this->assertSame('Kirby Kamera Inc.', $exif->data()['Make']);
		$this->assertSame('1/250', $exif->data()['ExposureTime']);
		$this->assertSame('N', $exif->data()['GPSLatitudeRef']);
	}

	public function testExposure(): void
	{
		$exif = new Exif($this->sections());
		$this->assertSame('1/250', $exif->exposure());

		$exif = new Exif([]);
		$this->assertNull($exif->exposure());
	}

	public function testFocalLength(): void
	{
		$exif = new Exif($this->sections());
		$this->assertSame('50/1', $exif->focalLength());

		$exif = new Exif(['EXIF' => ['FocalLengthIn35mmFilm' => '75']]);
		$this->assertSame('75', $exif->focalLength());
	}

	public function testFromImage(): void
	{
		$image = new File(root: static::FIXTURES . '/image/onigiri-adobe-rgb-gps.webp');
		$exif  = $image->exif();

		$this->assertSame(40.25, round($exif->location()->lat(), 2));
		$this->assertSame(-75.13, round($exif->location()->lng(), 2));
	}

	public function testIso(): void
	{
		$exif = new Exif($this->sections());
		$this->assertSame('100', $exif->iso());

		$exif = new Exif(['EXIF' => ['ISOSpeedRatings' => [100, 200]]]);
		$this->assertSame([100, 200], $exif->iso());

		$exif = new Exif([]);
		$this->assertNull($exif->iso());
	}

	public function testLocation(): void
	{
		$exif = new Exif($this->sections());

		$this->assertSame(40.25, $exif->location()->lat());
		$this->assertSame(-75.11666666666666, $exif->location()->lng());

		// from cache
		$this->assertSame($exif->location(), $exif->location());
	}

	public function testTimestamp(): void
	{
		$exif = new Exif($this->sections(), 1000);
		$this->assertSame((string)strtotime('2016-12-11 11:13:14'), $exif->timestamp());

		$exif = new Exif([], 1000);
		$this->assertSame('1000', $exif->timestamp());

		$exif = new Exif([]);
		$this->assertNull($exif->timestamp());
	}

	public function testToArray(): void
	{
		$exif = new Exif($this->sections());

		$this->assertSame([
			'camera'      => [
				'make'  => 'Kirby Kamera Inc.',
				'model' => 'Deluxe Snap 3000'
			],
			'location'    => [
				'lat' => 40.25,
				'lng' => -75.11666666666666
			],
			'timestamp'   => (string)strtotime('2016-12-11 11:13:14'),
			'exposure'    => '1/250',
			'aperture'    => 'f/2.8',
			'iso'         => '100',
			'focalLength' => '50/1'
		], $exif->toArray());
	}
}
