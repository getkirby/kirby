<?php

namespace Kirby\Image;

use PHPUnit\Framework\TestCase;

class LocationTest extends TestCase
{
    protected function _exif()
    {
        return [
            'GPSLatitudeRef'  => 'N',
            'GPSLatitude'     => ['50/1', '49/1', '8592/1000'],
            'GPSLongitudeRef' => 'W',
            'GPSLongitude'    => ['0/1', '1', '/12450']
        ];
    }

    public function testLatLng()
    {
        $camera = new Location($this->_exif());
        $this->assertEquals(50.819053333333336, $camera->lat());
        $this->assertEquals(-0.016666666666666666, $camera->lng());
    }

    public function testToArray()
    {
        $camera = new Location($this->_exif());
        $array  = [
            'lat' => 50.819053333333336,
            'lng' => -0.016666666666666666
        ];
        $this->assertEquals($array, $camera->toArray());
        $this->assertEquals($array, $camera->__debugInfo());
    }

    public function testToString()
    {
        $camera = new Location($this->_exif());
        $this->assertStringContainsString('50.8190533333', (string)$camera);
        $this->assertStringContainsString('-0.016666666666', (string)$camera);
    }
}
