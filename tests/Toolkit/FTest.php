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
    }

}
