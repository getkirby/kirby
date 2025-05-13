<?php

namespace Kirby\Image;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Camera::class)]
class CameraTest extends TestCase
{
	protected function _exif(): array
	{
		return [
			'Make'  => 'Kirby Kamera Inc.',
			'Model' => 'Deluxe Snap 3000'
		];
	}

	public function testSetup(): void
	{
		$exif   = $this->_exif();
		$camera = new Camera($exif);
		$this->assertSame($exif['Make'], $camera->make());
		$this->assertSame($exif['Model'], $camera->model());
	}

	public function testToArray(): void
	{
		$exif   = $this->_exif();
		$camera = new Camera($exif);
		$this->assertSame(array_change_key_case($exif), $camera->toArray());
		$this->assertSame(array_change_key_case($exif), $camera->__debugInfo());
	}

	public function testToString(): void
	{
		$exif   = $this->_exif();
		$camera = new Camera($exif);
		$this->assertSame('Kirby Kamera Inc. Deluxe Snap 3000', (string)$camera);
	}
}
