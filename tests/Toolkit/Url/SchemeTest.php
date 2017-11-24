<?php

namespace Kirby\Toolkit\Url;

class SchemeTest extends TestCase
{

    public function testGet()
    {
        // http with providing URL
        $this->assertEquals('http', Scheme::get($this->_yt));

        // http without providing URL
        $this->assertEquals('http', Scheme::get());

        // https with providing URL
        $this->assertEquals('https', Scheme::get($this->_yts));
    }

    public function testIsSecured()
    {
        // Standard http
        $this->assertFalse(Scheme::isSecure());

        $_SERVER['HTTPS'] = 'https';
        $this->assertTrue(Scheme::isSecure());

        // reset
        unset($_SERVER['HTTPS']);
        $this->assertFalse(Scheme::isSecure());

        $_SERVER['SERVER_PORT'] = '443';
        $this->assertTrue(Scheme::isSecure());

         // reset
         unset($_SERVER['SERVER_PORT']);
         $this->assertFalse(Scheme::isSecure());

         $_SERVER['HTTP_X_FORWARDED_PORT'] = '443';
         $this->assertTrue(Scheme::isSecure());

          // reset
          unset($_SERVER['HTTP_X_FORWARDED_PORT']);
          $this->assertFalse(Scheme::isSecure());

          $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
          $this->assertTrue(Scheme::isSecure());

           // reset
           unset($_SERVER['HTTP_X_FORWARDED_PROTO']);
           $this->assertFalse(Scheme::isSecure());


           $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https, http';
           $this->assertTrue(Scheme::isSecure());

            // reset
            unset($_SERVER['HTTP_X_FORWARDED_PROTO']);
            $this->assertFalse(Scheme::isSecure());

    }
}
