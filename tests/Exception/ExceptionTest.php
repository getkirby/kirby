<?php

namespace Kirby\Exception;

use PHPUnit\Framework\TestCase;
use Kirby\Toolkit\F;
use Kirby\Toolkit\I18n;

class WillFail
{
    public function fail()
    {
        throw new Exception([
            'key' => 'key.unique',
        ]);
    }
}

/**
 * @coversDefaultClass Kirby\Exception\Exception
 */
class ExceptionTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getKey
     * @covers ::getHttpCode
     * @covers ::getData
     * @covers ::getDetails
     * @covers ::isTranslated
     */
    public function testException()
    {
        $exception = new Exception([
            'key' => 'page.slug.invalid',
            'fallback' => 'The page slug "{ slug }" is invalid',
            'data' => $data = ['slug' => 'project/(c'],
            'details' => $details = ['some' => 'details'],
            'httpCode' => $http = 500,
            'translate' => false
        ]);

        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertEquals('error.page.slug.invalid', $exception->getKey());
        $this->assertEquals('error.page.slug.invalid', $exception->getCode());
        $this->assertEquals('The page slug "project/(c" is invalid', $exception->getMessage());
        $this->assertEquals($http, $exception->getHttpCode());
        $this->assertEquals($data, $exception->getData());
        $this->assertEquals($details, $exception->getDetails());
        $this->assertFalse($exception->isTranslated());
    }

    /**
     * @covers ::__construct
     */
    public function testDefaults()
    {
        $exception = new Exception();

        $this->assertEquals('error.general', $exception->getKey());
        $this->assertEquals('An error occurred', $exception->getMessage());
        $this->assertEquals(500, $exception->getHttpCode());
        $this->assertFalse($exception->isTranslated());
        $this->assertEquals([], $exception->getData());
        $this->assertEquals([], $exception->getDetails());
    }

    /**
     * @covers ::__construct
     */
    public function testJustMessage()
    {
        $exception = new Exception('Another error occurred');

        $this->assertEquals('error.general', $exception->getKey());
        $this->assertEquals('Another error occurred', $exception->getMessage());
        $this->assertEquals(500, $exception->getHttpCode());
        $this->assertFalse($exception->isTranslated());
        $this->assertEquals([], $exception->getData());
    }

    /**
     * @covers ::__construct
     */
    public function testPrevious()
    {
        $previous  = new Exception('Previous');
        $exception = new Exception(['previous' => $previous]);

        $this->assertNull($previous->getPrevious());
        $this->assertEquals($previous, $exception->getPrevious());
    }

    /**
     * @covers ::__construct
     */
    public function testPHPUnitTesting()
    {
        $this->expectException('Kirby\Exception\Exception');
        $this->expectExceptionCode('error.key.unique');

        $class = new WillFail();
        $class->fail();
    }

    /**
     * @covers ::__construct
     */
    public function testTranslation()
    {
        I18n::$locale = 'test';
        I18n::$translations = [
            'test' => [
                'error.general'      => 'Some general error',
                'error.translatable' => 'Some other translatable error'
            ]
        ];

        // scenario 1: translation for provided key in current language
        $exception = new Exception([
            'key'      => 'translatable',
            'fallback' => 'Some fallback'
        ]);
        $this->assertEquals('Some other translatable error', $exception->getMessage());
        $this->assertTrue($exception->isTranslated());

        // scenario 3: provided fallback message
        $exception = new Exception([
            'key'      => 'not-translated',
            'fallback' => 'Some fallback'
        ]);
        $this->assertEquals('Some fallback', $exception->getMessage());
        $this->assertFalse($exception->isTranslated());

        // scenario 4: translation for default key in current language
        $exception = new Exception([
            'key' => 'not-translated'
        ]);
        $this->assertEquals('Some general error', $exception->getMessage());
        $this->assertTrue($exception->isTranslated());

        I18n::$translations = [
            'en' => [
                'error.general'      => 'Some general fallback',
                'error.translatable' => 'Some other translatable fallback'
            ]
        ];

        // scenario 2: translation for provided key in default language
        $exception = new Exception([
            'key'      => 'translatable',
            'fallback' => 'Some fallback'
        ]);
        $this->assertEquals('Some other translatable fallback', $exception->getMessage());
        $this->assertTrue($exception->isTranslated());

        // scenario 5: translation for default key in default language
        $exception = new Exception([
            'key' => 'not-translated'
        ]);
        $this->assertEquals('Some general fallback', $exception->getMessage());
        $this->assertTrue($exception->isTranslated());

        I18n::$locale = 'en';
        I18n::$translations = [];

        // scenario 6: default fallback message
        $exception = new Exception([
            'key' => 'translatable'
        ]);
        $this->assertEquals('An error occurred', $exception->getMessage());
        $this->assertFalse($exception->isTranslated());
    }

    /**
     * @covers ::toArray
     */
    public function testToArray()
    {
        $exception = new Exception();
        $expected = [
            'exception' => 'Kirby\Exception\Exception',
            'message'   => 'An error occurred',
            'key'       => 'error.general',
            'file'      => __FILE__,
            'line'      => $exception->getLine(),
            'details'   => [],
            'code'      => 500
        ];
        $this->assertEquals($expected, $exception->toArray());

        $_SERVER['DOCUMENT_ROOT'] = __DIR__;
        $exception = new Exception();
        $expected['file'] = F::filename(__FILE__);
        $expected['line'] = $exception->getLine();
        $this->assertEquals($expected, $exception->toArray());

        $_SERVER['DOCUMENT_ROOT'] = __DIR__ . '/';
        $exception = new Exception();
        $expected['line'] = $exception->getLine();
        $this->assertEquals($expected, $exception->toArray());
    }
}
