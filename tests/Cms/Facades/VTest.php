<?php

namespace Kirby\Cms;

use Kirby\Toolkit\V;

class VTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);
    }

    public function messageInput()
    {
        return [
            ['accepted', ['test'], 'Please confirm'],
            ['alpha', ['test'], 'Please only enter characters between a-z'],
            ['alphanum', ['test'], 'Please only enter characters between a-z or numerals 0-9'],
            ['between', ['test', 1, 2], 'Please enter a value between "1" and "2"'],
            ['contains', ['test', 'foo'], 'Please enter a value that contains "foo"'],
            ['date', ['test'], 'Please enter a valid date'],
            ['denied', ['test'], 'Please deny'],
            ['different', ['test', 'test'], 'The value must not be "test"'],
            ['email', ['test', 'test'], 'Please enter a valid email address'],
            ['endsWith', ['test', 'test'], 'The value must end with "test"'],
            ['filename', ['test'], 'Please enter a valid filename'],
            ['in', ['test', ['a', 'b']], 'Please enter one of the following: (a, b)'],
            ['integer', ['test'], 'Please enter a valid integer'],
            ['ip', ['test'], 'Please enter a valid IP address'],
            ['less', ['test', 5], 'Please enter a value lower than 5'],
            ['match', ['test', '!foo!'], 'The value does not match the expected pattern'],
            ['max', ['test', 5], 'Please enter a value equal to or lower than 5'],
            ['min', ['test', 5], 'Please enter a value equal to or greater than 5'],
            ['maxlength', ['test', 5], 'Please enter a shorter value. (max. 5 characters)'],
            ['minlength', ['test', 5], 'Please enter a longer value. (min. 5 characters)'],
            ['maxwords', ['test', 5], 'Please enter no more than 5 word(s)'],
            ['minwords', ['test', 5], 'Please enter at least 5 word(s)'],
            ['more', ['test', 5], 'Please enter a greater value than 5'],
            ['notContains', ['test', 'test'], 'Please enter a value that does not contain "test"'],
            ['notIn', ['test', ['a', 'b']], 'Please don\'t enter any of the following: (a, b)'],
            ['num', ['test'], 'Please enter a valid number'],
            ['required', ['test'], 'Please enter something'],
            ['same', ['test', 'test'], 'Please enter "test"'],
            ['size', ['test', 5], 'The size of the value must be "5"'],
            ['startsWith', ['test', 'test'], 'The value must start with "test"'],
            ['time', ['test'], 'Please enter a valid time'],
            ['url', ['test'], 'Please enter a valid URL'],
        ];
    }

    /**
     * @dataProvider messageInput
     */
    public function testMessage($validator, $args, $expected)
    {
        $this->assertSame($expected, V::message($validator, ...$args));
    }

    public function testMessageInvalidValidator()
    {
        $this->assertNull(V::message('foo'));
    }
}
