<?php

namespace Kirby\Panel;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Redirect
 */
class RedirectTest extends TestCase
{
    /**
     * @covers ::code
     */
    public function testCode()
    {
        // default
        $redirect = new Redirect('https://getkirby.com');
        $this->assertSame(302, $redirect->code());

        // valid code
        $redirect = new Redirect('https://getkirby.com', 301);
        $this->assertSame(301, $redirect->code());

        // invalid code
        $redirect = new Redirect('https://getkirby.com', 404);
        $this->assertSame(302, $redirect->code());
    }

    /**
     * @covers ::location
     */
    public function testLocation()
    {
        $redirect = new Redirect('https://getkirby.com');
        $this->assertSame('https://getkirby.com', $redirect->location());
    }
}
