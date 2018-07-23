<?php

namespace Kirby\Toolkit;

class FTest extends TestCase
{

    public function testSafeName()
    {
        // with extension
        $this->assertEquals('uber-genious.txt', F::safeName('über genious.txt'));

        // with unsafe extension
        $this->assertEquals('uber-genious.taxt', F::safeName('über genious.täxt'));

        // without extension
        $this->assertEquals('uber-genious', F::safeName('über genious'));

        // with leading dash
        $this->assertEquals('super.jpg', F::safeName('-super.jpg'));

        // with leading underscore
        $this->assertEquals('super.jpg', F::safeName('_super.jpg'));

        // with leading dot
        $this->assertEquals('super.jpg', F::safeName('.super.jpg'));
    }

}
