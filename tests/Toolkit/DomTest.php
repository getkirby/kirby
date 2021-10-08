<?php

namespace Kirby\Toolkit;

/**
 * @coversDefaultClass Kirby\Toolkit\Dom
 */
class DomTest extends TestCase
{
    public function urlProvider()
    {
        return [
            // allowed empty url
            ['', true],

            // allowed path
            ['/', true],

            // allowed path
            ['/some/path', true],

            // allowed fragment
            ['#', true],

            // allowed fragment
            ['#test-fragment', true],

            // allowed data uri
            ['data:image/jpeg;base64,test', true, [
                'allowedDataUris' => [
                    'data:image/jpeg;base64'
                ]
            ]],

            // allowed URL when all domains are accepted
            ['http://getkirby.com', true, [
                'allowedDomains' => true
            ]],

            // allowed URL when the domain is accepted
            ['http://getkirby.com', true, [
                'allowedDomains' => [
                    'getkirby.com'
                ]
            ]],

            // allowed empty email address
            ['mailto:', true],

            // allowed valid email address
            ['mailto:test@getkirby.com', true],

            // allowed empty phone number
            ['tel:', true],

            // allowed phone number
            ['tel:+491122334455', true],

            // forbidden data uri
            ['data:image/jpeg;base64,test', 'Invalid data URI', [
                'allowedDataUris' => []
            ]],
            
            // forbidden data uri
            ['data:image/png;base64,test', 'Invalid data URI', [
                'allowedDataUris' => ['data:image/jpeg;base64']
            ]],

            // forbidden URL when no domains are accepted
            ['https://getkirby.com', 'The hostname "getkirby.com" is not allowed', [
                'allowedDomains' => []
            ]],

            // forbidden URL when the particular domain is not accepted
            ['https://google.com', 'The hostname "google.com" is not allowed', [
                'allowedDomains' => [
                    'getkirby.com'
                ]
            ]],

            // forbidden invalid email address
            ['mailto:test', 'Invalid email address'],

            // forbidden phone numbers
            ['tel:test', 'Invalid telephone number'],

            // forbidden phone numbers - too much formatting
            ['tel:+49 (0) 1234 5678', 'Invalid telephone number'],

            // forbidden phone numbers - invalid plus sign position
            ['tel:491234+5678', 'Invalid telephone number'],

            // forbidden URL type
            ['ftp:test', 'Unknown URL type'],
            
            // forbidden URL type
            ['ftp://test', 'Unknown URL type'],
        ];
    }

    /**
     * @dataProvider urlProvider
     * @covers ::isAllowedUrl
     */
    public function testIsAllowedUrl(string $url, $expected, array $options = [])
    {
        $dom = new Dom('Test');
        $this->assertSame($expected, $dom->isAllowedUrl($url, $options));
    }
}
