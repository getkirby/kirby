<?php

namespace Kirby\Image;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Location::class)]
class LocationTest extends TestCase
{
	protected function _exif(): array
	{
		return [
			'GPSLatitudeRef'  => 'N',
			'GPSLatitude'     => ['50/1', '49/1', '8592/1000'],
			'GPSLongitudeRef' => 'W',
			'GPSLongitude'    => ['0/1', '1', '/12450']
		];
	}

	public function testLatLng(): void
	{
		$camera = new Location($this->_exif());
		$this->assertSame(50.819053333333336, $camera->lat());
		$this->assertSame(-0.016666666666666666, $camera->lng());
	}

	public function testToArray(): void
	{
		$camera = new Location($this->_exif());
		$array  = [
			'lat' => 50.819053333333336,
			'lng' => -0.016666666666666666
		];
		$this->assertSame($array, $camera->toArray());
		$this->assertSame($array, $camera->__debugInfo());
	}

	public function testToString(): void
	{
		$camera = new Location($this->_exif());
		$this->assertStringContainsString('50.8190533333', (string)$camera);
		$this->assertStringContainsString('-0.016666666666', (string)$camera);
	}
}
