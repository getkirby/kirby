<?php

namespace Kirby\Toolkit;

use Kirby\Cms\App;

/**
 * @coversDefaultClass \Kirby\Toolkit\Dom
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

            // allowed path
            ['some', true],

            // allowed path
            ['some/path', true],

            // allowed path
            ['some/path:test', true],

            // allowed path
            ['some/path:some/test', true],

            // allowed path
            ['./some/path', true],

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

            // forbidden relative URL
            ['../some/path', 'The ../ sequence is not allowed in relative URLs'],

            // forbidden relative URL
            ['..\some\path', 'The ../ sequence is not allowed in relative URLs'],

            // forbidden relative URL
            ['some/../../path', 'The ../ sequence is not allowed in relative URLs'],

            // forbidden relative URL
            ['some\..\..\path', 'The ../ sequence is not allowed in relative URLs'],

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
            ['javascript:alert()', 'Unknown URL type'],

            // forbidden URL type
            ['ftp:test', 'Unknown URL type'],

            // forbidden URL type
            ['ftp://test', 'Unknown URL type'],

            // forbidden URL type
            ['my-amazing-protocol:test', 'Unknown URL type'],

            // forbidden URL type
            ['my-amazing-protocol://test', 'Unknown URL type'],

            // forbidden URL type
            ['//test', 'Protocol-relative URLs are not allowed'],
        ];
    }

    /**
     * @dataProvider urlProvider
     * @covers ::isAllowedUrl
     */
    public function testIsAllowedUrl(string $url, $expected, array $options = [])
    {
        $this->assertSame($expected, Dom::isAllowedUrl($url, $options));
    }

    public function urlProviderCms()
    {
        return [
            // allowed URL with site at the domain root
            ['https://getkirby.com', '/some/path', true],

            // allowed URL with site at the domain root
            ['/', '/some/path', true],

            // allowed URL with site in a subfolder
            ['https://getkirby.com/some', '/some/path', true],

            // allowed URL with site in a subfolder
            ['/some', '/some/path', true],

            // disallowed URL with site in a subfolder
            ['https://getkirby.com/site', '/some/path', 'The URL points outside of the site index URL'],

            // disallowed URL with site in a subfolder
            ['/site', '/some/path', 'The URL points outside of the site index URL'],

            // disallowed URL with directory traversal
            ['https://getkirby.com/site', '/site/../some/path', 'The ../ sequence is not allowed in relative URLs'],

            // disallowed URL with directory traversal
            ['/site', '/site/../some/path', 'The ../ sequence is not allowed in relative URLs'],
        ];
    }

    /**
     * @dataProvider urlProviderCms
     * @covers ::isAllowedUrl
     */
    public function testIsAllowedUrlCms(string $indexUrl, string $url, $expected)
    {
        new App([
            'urls' => [
                'index' => $indexUrl
            ]
        ]);

        $this->assertSame($expected, Dom::isAllowedUrl($url, []));
    }
}
