<?php

namespace Kirby\Sane;

require_once __DIR__ . '/mocks.php';

/**
 * @covers \Kirby\Sane\DomHandler
 */
class DomHandlerTest extends TestCase
{
    protected $type = 'sane';

    public function testSanitize()
    {
        $fixture = '<xml><test attr="value">Hello world</test></xml>';
        $this->assertSame($fixture, DomHandler::sanitize($fixture));

        $fixture   = '<?xml version="1.0"?><xml><test>Hello world</test></xml>';
        $sanitized = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<xml><test>Hello world</test></xml>";
        $this->assertSame($sanitized, DomHandler::sanitize($fixture));

        $fixture   = '<?xml version="1.0" standalone="no"?><xml><test>Hello world</test></xml>';
        $sanitized = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"no\"?>\n<xml><test>Hello world</test></xml>";
        $this->assertSame($sanitized, DomHandler::sanitize($fixture));
    }

    public function testValidate()
    {
        $this->assertNull(DomHandler::validate('<!DOCTYPE xml><xml><test attr="value">Hello world</test></xml>'));
    }

    public function testValidateException1()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The URL is not allowed in attribute "href" (line 2): Unknown URL type');

        DomHandler::validate("<xml>\n<a href='javascript:alert(1)'></a>\n</xml>");
    }

    public function testValidateException2()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The doctype must not reference external files');

        DomHandler::validate("<!DOCTYPE xml SYSTEM \"https://malicious.com/something.dtd\">\n<xml>\n<a href='javascript:alert(1)'></a>\n</xml>");
    }
}
