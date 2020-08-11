<?php

namespace Kirby\Toolkit;

use PHPUnit\Framework\TestCase;

class CanBeCounted implements \Countable
{
    public function count()
    {
        return 7;
    }
}

class HasCount
{
    public function count()
    {
        return 7;
    }
}

class VTest extends TestCase
{
    public function testValidators()
    {
        $this->assertFalse(empty(V::$validators));
        $this->assertFalse(empty(V::validators()));
    }

    public function testCustomValidator()
    {
        V::$validators['me'] = function ($name): bool {
            return V::in($name, ['I', 'me', 'myself']);
        };

        $this->assertTrue(V::me('I'));
        $this->assertTrue(V::me('me'));
        $this->assertTrue(V::me('myself'));
        $this->assertFalse(V::me('you'));
    }

    public function testCustomValue()
    {
        V::$validators['me'] = function ($name): bool {
            return V::in($name, ['I', 'me', 'myself']);
        };

        $result = V::value('myself', [
            'me' => ['I', 'me', 'myself']
        ]);

        $this->assertTrue($result);

        $this->expectException('Exception');
        $this->expectExceptionMessage('The "me" validation failed');

        V::value('you', [
            'me' => ['I', 'me', 'myself']
        ]);
    }

    public function testInvalidMethod()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('The validator does not exist: fool');

        V::fool('me');
    }

    public function testAccepted()
    {
        $this->assertTrue(V::accepted(true));
        $this->assertTrue(V::accepted('true'));
        $this->assertTrue(V::accepted(1));
        $this->assertTrue(V::accepted('1'));
        $this->assertTrue(V::accepted('yes'));
        $this->assertTrue(V::accepted('on'));

        $this->assertFalse(V::accepted('word'));
        $this->assertFalse(V::accepted(false));
        $this->assertFalse(V::accepted('false'));
        $this->assertFalse(V::accepted(5));
        $this->assertFalse(V::accepted(0));
        $this->assertFalse(V::accepted('0'));
        $this->assertFalse(V::accepted(null));
        $this->assertFalse(V::accepted('off'));
    }

    public function testContains()
    {
        $this->assertTrue(V::contains('word', 'or'));
        $this->assertFalse(V::contains('word', 'test'));
    }

    public function testDenied()
    {
        $this->assertTrue(V::denied(false));
        $this->assertTrue(V::denied('false'));
        $this->assertTrue(V::denied(0));
        $this->assertTrue(V::denied('0'));
        $this->assertTrue(V::denied('no'));
        $this->assertTrue(V::denied('off'));

        $this->assertFalse(V::denied('word'));
        $this->assertFalse(V::denied(true));
        $this->assertFalse(V::denied('true'));
        $this->assertFalse(V::denied(5));
        $this->assertFalse(V::denied(1));
        $this->assertFalse(V::denied('1'));
        $this->assertFalse(V::denied('on'));
        $this->assertFalse(V::denied(null));
    }

    public function testAlpha()
    {
        $this->assertTrue(V::alpha('foo'));
        $this->assertTrue(V::alpha('abcdefghijklmnopqrstuvwxyz'));
        $this->assertTrue(V::alpha('ABCDEFGHIJKLMNOPQRSTUVWXYZ'));
        $this->assertTrue(V::alpha('abcdefABCDEF'));

        $this->assertFalse(V::alpha('äöüß'));
        $this->assertFalse(V::alpha('abc1234'));
        $this->assertFalse(V::alpha('abc"§$%&/()=?'));

        $this->assertTrue(V::alpha('uñicode', true));
        $this->assertTrue(V::alpha('nonunicode', true));
        $this->assertFalse(V::alpha('uñicode', false));
        $this->assertFalse(V::alpha('uñi-code', true));
    }

    public function testAlphanum()
    {
        $this->assertTrue(V::alphanum('foo'));
        $this->assertTrue(V::alphanum('abcdefghijklmnopqrstuvwxyz'));
        $this->assertTrue(V::alphanum('ABCDEFGHIJKLMNOPQRSTUVWXYZ'));
        $this->assertTrue(V::alphanum('abcdefABCDEF'));
        $this->assertTrue(V::alphanum('abc1234'));
        $this->assertTrue(V::alphanum('0123456789'));


        $this->assertFalse(V::alphanum('äöüß'));
        $this->assertFalse(V::alphanum('abc"§$%&/()=?'));

        $this->assertTrue(V::alphanum('uñicode1234', true));
        $this->assertTrue(V::alphanum('nonunicode1234', true));
        $this->assertFalse(V::alphanum('uñicode1234', false));
        $this->assertFalse(V::alphanum('uñicode-1234', true));
    }

    public function testBetween()
    {
        $this->assertTrue(V::between(4, 3, 5));
        $this->assertTrue(V::between('kirby', 4, 6));

        $this->assertFalse(V::between(3, 4, 5));
        $this->assertFalse(V::between('kirby', 2, 4));
    }

    public function testDate()
    {
        $this->assertTrue(V::date('2017-12-24'));
        $this->assertTrue(V::date('29.01.1989'));
        $this->assertTrue(V::date('January 29, 1989'));

        $this->assertFalse(V::date('äöüß'));
        $this->assertFalse(V::date('2017-02-31'));
        $this->assertFalse(V::date('January 32, 1989'));
    }

    public function testDifferent()
    {
        $this->assertTrue(V::different('foo', 'bar'));
        $this->assertTrue(V::different('bar', 'foo'));
        $this->assertTrue(V::different(1, 2));
        $this->assertTrue(V::different(null, 'bar'));

        $this->assertFalse(V::different('foo', 'foo'));
        $this->assertFalse(V::different('bar', 'bar'));
        $this->assertFalse(V::different(1, 1));
        $this->assertFalse(V::different('true', 'true'));
        $this->assertFalse(V::different(null, null));

        // non-strict
        $this->assertFalse(V::different('true', true));

        // strict
        $this->assertTrue(V::different('true', true, true));
    }

    public function testEndsWith()
    {
        $this->assertTrue(V::endsWith('test', 'st'));
        $this->assertFalse(V::endsWith('test', 'te'));
    }

    public function testSame()
    {
        $this->assertTrue(V::same('foo', 'foo'));
        $this->assertTrue(V::same('bar', 'bar'));
        $this->assertTrue(V::same(1, 1));
        $this->assertTrue(V::same('true', 'true'));
        $this->assertTrue(V::same(null, null));

        $this->assertFalse(V::same('foo', 'bar'));
        $this->assertFalse(V::same('bar', 'foo'));
        $this->assertFalse(V::same(1, 2));
        $this->assertFalse(V::same(null, 'bar'));

        // non-strict
        $this->assertTrue(V::same('true', true));

        // strict
        $this->assertFalse(V::same('true', true, true));
    }

    public function testEmail()
    {
        $this->assertTrue(V::email('bastian@getkirby.com'));
        $this->assertTrue(V::email('bastian-v3@getkirby.com'));
        $this->assertTrue(V::email('bastian.allgeier@getkirby.com'));
        $this->assertTrue(V::email('bastian@getkürby.com'));

        $this->assertFalse(V::email('bastian@getkirby'));
        $this->assertFalse(V::email('bastiangetkirby.com'));
        $this->assertFalse(V::email('bastian[at]getkirby.com'));
        $this->assertFalse(V::email('bastian@getkürby'));
        $this->assertFalse(V::email('@getkirby.com'));
    }

    public function testDateComparison()
    {
        $this->assertTrue(V::date('2345-01-01', '==', '01.01.2345'));
        $this->assertFalse(V::date('2345-01-02', '==', '01.01.2345'));

        $this->assertTrue(V::date('2345-01-02', '!=', '01.01.2345'));
        $this->assertFalse(V::date('2345-01-01', '!=', '01.01.2345'));

        $this->assertTrue(V::date('2345-01-02', '>', '01.01.2345'));
        $this->assertFalse(V::date('2345-01-01', '>', '01.01.2345'));
        $this->assertFalse(V::date('2344-12-31', '>', '01.01.2345'));

        $this->assertTrue(V::date('2345-01-01', '>=', '01.01.2345'));
        $this->assertTrue(V::date('2345-01-02', '>=', '01.01.2345'));
        $this->assertFalse(V::date('2344-12-31', '>=', '01.01.2345'));

        $this->assertTrue(V::date('2344-12-31', '<', '01.01.2345'));
        $this->assertFalse(V::date('2345-01-01', '<', '01.01.2345'));
        $this->assertFalse(V::date('2345-01-02', '<', '01.01.2345'));

        $this->assertTrue(V::date('2345-01-01', '<=', '01.01.2345'));
        $this->assertTrue(V::date('2344-12-31', '<=', '01.01.2345'));
        $this->assertFalse(V::date('2345-01-02', '<=', '01.01.2345'));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid date comparison operator: "<>". Allowed operators: "==", "!=", "<", "<=", ">", ">="');

        V::date('2345-01-01', '<>', '2345-01-01');
    }

    public function testFilename()
    {
        $this->assertTrue(V::filename('size.txt'));
        $this->assertTrue(V::filename('si.ze.md'));
        $this->assertTrue(V::filename('my-awesome-image@2x.jpg'));

        $this->assertFalse(V::filename('../'));
    }

    public function testIn()
    {
        $this->assertTrue(V::in('bastian', ['bastian', 'nico', 'sonja']));

        $this->assertFalse(V::in('bastian', ['lukas', 'nico', 'sonja']));
        $this->assertFalse(V::in('bastian', []));
    }

    public function testNotIn()
    {
        $this->assertFalse(V::notIn('bastian', ['bastian', 'nico', 'sonja']));

        $this->assertTrue(V::notIn('bastian', ['lukas', 'nico', 'sonja']));
        $this->assertTrue(V::notIn('bastian', []));
    }

    public function testInteger()
    {
        $this->assertTrue(V::integer(5));
        $this->assertTrue(V::integer(0));
        $this->assertTrue(V::integer('5'));
        $this->assertTrue(V::integer(5, true));
        $this->assertTrue(V::integer(0, true));

        $this->assertFalse(V::integer(5.1));
        $this->assertFalse(V::integer([5]));
        $this->assertFalse(V::integer(5.1, true));
        $this->assertFalse(V::integer('5', true));
        $this->assertFalse(V::integer(null, true));
    }

    public function testIp()
    {
        $this->assertTrue(V::ip('192.168.255.1'));
        $this->assertTrue(V::ip('8.8.8.8'));

        $this->assertFalse(V::ip(5));
        $this->assertFalse(V::ip('192.168'));
        $this->assertFalse(V::ip('192:168:255:12'));
        $this->assertFalse(V::ip('192.168.255.24.23'));
    }

    public function testLess()
    {
        $this->assertTrue(V::less(1, 2));
        $this->assertFalse(V::less(2, 1));
    }

    public function testMaxLength()
    {
        $this->assertTrue(V::maxLength('Kirby', 10));
        $this->assertTrue(V::maxLength('Kirby', 5));
        $this->assertTrue(V::maxLength(' Kirby ', 5));

        $this->assertFalse(V::maxLength('Kirby', 3));
    }

    public function testMinLength()
    {
        $this->assertTrue(V::minLength('Kirby', 2));
        $this->assertTrue(V::minLength('Kirby', 5));

        $this->assertFalse(V::minLength('Kirby', 6));
        $this->assertFalse(V::minLength(' Kirby ', 6));
    }

    public function testMaxWords()
    {
        $this->assertTrue(V::maxWords('This is Kirby', 10));
        $this->assertTrue(V::maxWords('This is Kirby', 3));
        $this->assertTrue(V::maxWords('This is Kirby ', 3));

        $this->assertFalse(V::maxWords('This is Kirby', 2));
    }

    public function testMinWords()
    {
        $this->assertTrue(V::minWords('This is Kirby', 2));
        $this->assertTrue(V::minWords('This is Kirby', 3));

        $this->assertFalse(V::minWords('This is Kirby', 4));
        $this->assertFalse(V::minWords('This is Kirby ', 4));
    }

    public function testMore()
    {
        $this->assertTrue(V::more(1, 0));
        $this->assertFalse(V::more(0, 1));
    }

    public function testNotContains()
    {
        $this->assertFalse(V::notContains('word', 'or'));
        $this->assertTrue(V::notContains('word', 'test'));
    }

    public function testNum()
    {
        $this->assertTrue(V::num(2));
        $this->assertTrue(V::num(3.5));

        $this->assertFalse(V::num('foo'));
        // $this->assertFalse(V::num('5'));
        $this->assertFalse(V::num(null));
        $this->assertFalse(V::num(false));
    }

    public function testRequired()
    {
        $this->assertTrue(V::required('a', ['a' => 2]));
        $this->assertTrue(V::required('a', ['a' => 'foo']));
        $this->assertTrue(V::required('a', ['a' => ['foo']]));

        $this->assertFalse(V::required('a', ['a' => '']));
        $this->assertFalse(V::required('a', ['a' => null]));
        $this->assertFalse(V::required('a', ['a' => []]));
    }

    public function testSize()
    {
        $this->assertTrue(V::size('foo', 3));
        $this->assertTrue(V::size(' foo ', 3));
        $this->assertTrue(V::size(7.9, 7.9));
        $this->assertTrue(V::size([
            'bastian',
            'lukas',
            'nico'
        ], 3));
        $this->assertTrue(V::size(new CanBeCounted(), 7));
        $this->assertTrue(V::size(new HasCount(), 7));
        $this->assertTrue(V::size(5, 7, '<'));
        $this->assertTrue(V::size(7, 5, '>'));

        $this->assertFalse(V::size('foo', 4));
        $this->assertFalse(V::size(' foo ', 5));
        $this->assertFalse(V::size(7.9, 8));
        $this->assertFalse(V::size([], 3));
        $this->assertFalse(V::size(new CanBeCounted(), 8));
        $this->assertFalse(V::size(new HasCount(), 8));
        $this->assertFalse(V::size(5, 7, '>'));
        $this->assertFalse(V::size(7, 5, '<'));
    }

    public function testSizeInvalid()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('$value is of type without size');

        V::size(false, 5);
    }

    public function testSizeInvalidObject()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('$value is an uncountable object');

        V::size(new \stdClass(), 5);
    }

    public function testTime()
    {
        $this->assertTrue(V::time('12:12:12'));
        $this->assertFalse(V::time('24:24:24'));
    }

    public function testUrl()
    {
        // based on https://mathiasbynens.be/demo/url-regex
        $this->assertTrue(V::url('http://www.getkirby.com'));
        $this->assertTrue(V::url('http://www.getkirby.com/docs/param:value/?foo=bar/#anchor'));
        $this->assertTrue(V::url('https://www.getkirby.de.vu'));
        $this->assertTrue(V::url('https://getkirby.com:1234'));
        $this->assertTrue(V::url('https://getkirby.com:1234/test'));
        $this->assertTrue(V::url('http://foo.com/blah_blah'));
        $this->assertTrue(V::url('http://foo.com/blah_blah/'));
        $this->assertTrue(V::url('http://foo.com/blah_blah_(wikipedia)'));
        $this->assertTrue(V::url('http://foo.com/blah_blah_(wikipedia)_(again)'));
        $this->assertTrue(V::url('http://www.example.com/wpstyle/?p=364'));
        $this->assertTrue(V::url('https://www.example.com/foo/?bar=baz&inga=42&quux'));
        $this->assertTrue(V::url('http://✪df.ws/123'));
        $this->assertTrue(V::url('http://userid:password@example.com:8080'));
        $this->assertTrue(V::url('http://userid:password@example.com:8080/'));
        $this->assertTrue(V::url('http://userid@example.com'));
        $this->assertTrue(V::url('http://userid@example.com/'));
        $this->assertTrue(V::url('http://userid@example.com:8080'));
        $this->assertTrue(V::url('http://userid@example.com:8080/'));
        $this->assertTrue(V::url('http://userid:password@example.com'));
        $this->assertTrue(V::url('http://userid:password@example.com/'));
        $this->assertTrue(V::url('http://142.42.1.1/'));
        $this->assertTrue(V::url('http://142.42.1.1:8080/'));
        $this->assertTrue(V::url('http://➡.ws/䨹'));
        $this->assertTrue(V::url('http://⌘.ws'));
        $this->assertTrue(V::url('http://⌘.ws/'));
        $this->assertTrue(V::url('http://foo.com/blah_(wikipedia)#cite-1'));
        $this->assertTrue(V::url('http://foo.com/blah_(wikipedia)_blah#cite-1'));
        $this->assertTrue(V::url('http://foo.com/unicode_(✪)_in_parens'));
        $this->assertTrue(V::url('http://foo.com/(something)?after=parens'));
        $this->assertTrue(V::url('http://☺.damowmow.com/'));
        $this->assertTrue(V::url('http://code.google.com/events/#&product=browser'));
        $this->assertTrue(V::url('http://j.mp'));
        $this->assertTrue(V::url('ftp://foo.bar/baz'));
        $this->assertTrue(V::url('http://foo.bar/?q=Test%20URL-encoded%20stuff'));
        $this->assertTrue(V::url('http://مثال.إختبار'));
        $this->assertTrue(V::url('http://例子.测试'));
        $this->assertTrue(V::url('http://उदाहरण.परीक्षा'));
        $this->assertTrue(V::url("http://-.~_!$&'()*+,;=:%40:80%2f::::::@example.com"));
        $this->assertTrue(V::url('http://1337.net'));
        $this->assertTrue(V::url('http://a.b-c.de'));
        $this->assertTrue(V::url('http://223.255.255.254'));
        $this->assertTrue(V::url('http://localhost/test/'));
        $this->assertTrue(V::url('http://localhost:8080/test'));
        $this->assertTrue(V::url('http://127.0.0.1/kirby/'));
        $this->assertTrue(V::url('http://127.0.0.1:8080/kirby'));
        $this->assertTrue(V::url('https://127.0.0.1/kirby/panel/pages/blog+vvvv'));
        $this->assertTrue(V::url('https://localhost/kirby/panel/pages/blog+vvvv'));

        // TODO: Find better regex to also cover the following
        // $this->assertTrue(V::url('http://special---offer.com/'));
        // $this->assertTrue(V::url('http://10.1.1.1'));
        // $this->assertTrue(V::url('http://10.1.1.254'));

        $this->assertFalse(V::url('foo'));
        $this->assertFalse(V::url('http://'));
        $this->assertFalse(V::url('http://.'));
        $this->assertFalse(V::url('http://..'));
        $this->assertFalse(V::url('http://../'));
        $this->assertFalse(V::url('http://?'));
        $this->assertFalse(V::url('http://??'));
        $this->assertFalse(V::url('http://??/'));
        $this->assertFalse(V::url('http://#'));
        $this->assertFalse(V::url('http://##'));
        $this->assertFalse(V::url('http://##/'));
        $this->assertFalse(V::url('http://foo.bar?q=Spaces should be encoded'));
        $this->assertFalse(V::url('//'));
        $this->assertFalse(V::url('//a'));
        $this->assertFalse(V::url('///a'));
        $this->assertFalse(V::url('///'));
        $this->assertFalse(V::url('http:///a'));
        $this->assertFalse(V::url('foo.com'));
        $this->assertFalse(V::url('rdar://1234'));
        $this->assertFalse(V::url('h://test'));
        $this->assertFalse(V::url('http:// shouldfail.com'));
        $this->assertFalse(V::url(':// should fail'));
        $this->assertFalse(V::url('http://foo.bar/foo(bar)baz quux'));
        $this->assertFalse(V::url('ftps://foo.bar/'));
        $this->assertFalse(V::url('http://-error-.invalid/'));
        $this->assertFalse(V::url('http://a.b--c.de/'));
        $this->assertFalse(V::url('http://-a.b.co'));
        $this->assertFalse(V::url('http://a.b-.co'));
        $this->assertFalse(V::url('http://0.0.0.0'));
        $this->assertFalse(V::url('http://10.1.1.0'));
        $this->assertFalse(V::url('http://10.1.1.255'));
        $this->assertFalse(V::url('http://224.1.1.1'));
        $this->assertFalse(V::url('http://1.1.1.1.1'));
        $this->assertFalse(V::url('http://123.123.123'));
        $this->assertFalse(V::url('http://3628126748'));
        $this->assertFalse(V::url('http://.www.foo.bar/'));
        $this->assertFalse(V::url('http://www.foo.bar./'));
        $this->assertFalse(V::url('http://.www.foo.bar./'));
    }

    public function inputProvider()
    {
        return [
            // everything alright
            [
                [
                    'a' => 'a',
                    'b' => 'b'
                ],
                [],
                true
            ],
            // invalid email
            [
                [
                    'email' => 'test'
                ],
                [
                    'email' => [
                        'email'
                    ]
                ],
                false,
                'Please enter a valid email address for field "email"',
            ],
            // missing required field
            [
                [
                ],
                [
                    'email' => [
                        'required' => true
                    ]
                ],
                false,
                'The "email" field is missing',
            ],
            // skipping missing non-required field
            [
                [
                ],
                [
                    'email' => [
                        'email'
                    ],
                    'name' => [
                        'required' => true
                    ]
                ],
                false,
                'The "name" field is missing',
            ],
        ];
    }

    /**
     * @dataProvider inputProvider
     */
    public function testInput($input, $rules, $result, $message = null)
    {
        if ($result === false) {
            $this->expectException('Exception');
            $this->expectExceptionMessage($message);
        }

        $this->assertTrue(V::input($input, $rules));
    }

    public function testValue()
    {
        $result = V::value('test@getkirby.com', [
            'email',
            'maxLength' => 17,
            'minLength' => 17
        ]);

        $this->assertTrue($result);
    }

    public function testValueFails()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Please enter "b"');

        V::value('a', [
            'same' => 'b'
        ]);
    }
}
