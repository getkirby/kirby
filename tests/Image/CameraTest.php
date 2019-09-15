<?php

namespace Kirby\Image;

use PHPUnit\Framework\TestCase;

class CameraTest extends TestCase
{
    protected function _exif()
    {
        return [
            'Make'  => 'Kirby Kamera Inc.',
            'Model' => 'Deluxe Snap 3000'
        ];
    }

    public function testSetup()
    {
        $exif   = $this->_exif();
        $camera = new Camera($exif);
        $this->assertEquals($exif['Make'], $camera->make());
        $this->assertEquals($exif['Model'], $camera->model());
    }

    public function testToArray()
    {
        $exif   = $this->_exif();
        $camera = new Camera($exif);
        $this->assertEquals(array_change_key_case($exif), $camera->toArray());
        $this->assertEquals(array_change_key_case($exif), $camera->__debugInfo());
    }

    public function testToString()
    {
        $exif   = $this->_exif();
        $camera = new Camera($exif);
        $this->assertEquals('Kirby Kamera Inc. Deluxe Snap 3000', (string)$camera);
    }
}
