<?php

namespace Kirby\Toolkit;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Kirby\Toolkit\Html
 */
class HtmlTest extends TestCase
{
    /**
     * @covers ::__callStatic()
     */
    public function testCallStatic()
    {
        $this->assertSame('<div>test</div>', Html::div('test'));
        $this->assertSame('<div class="test">test</div>', Html::div('test', ['class' => 'test']));
        $this->assertSame('<hr class="test">', Html::hr(['class' => 'test']));
    }

    /**
     * @covers ::a
     * @covers ::link
     */
    public function testA()
    {
        $html = Html::a('https://getkirby.com');
        $expected = '<a href="https://getkirby.com">getkirby.com</a>';
        $this->assertSame($expected, $html);

        $html = Html::a('mailto:mail@company.com');
        $expected = '!^<a href="mailto:(.*?)">(.*?)</a>$!';
        $this->assertMatchesRegularExpression($expected, $html);
        preg_match($expected, $html, $matches);
        $this->assertSame('mail@company.com', Html::decode($matches[1]));
        $this->assertSame('mail@company.com', Html::decode($matches[2]));

        $html = Html::a('tel:1234');
        $expected = '<a href="tel:1234">1234</a>';
        $this->assertSame($expected, $html);
    }

    /**
     * @covers ::a
     * @covers ::link
     */
    public function testAWithText()
    {
        $html = Html::a('https://getkirby.com', 'Kirby');
        $expected = '<a href="https://getkirby.com">Kirby</a>';
        $this->assertSame($expected, $html);

        $html = Html::a('mailto:mail@company.com', 'Kirby');
        $expected = '!^<a href="mailto:(.*?)">Kirby</a>$!';
        $this->assertMatchesRegularExpression($expected, $html);
        preg_match($expected, $html, $matches);
        $this->assertSame('mail@company.com', Html::decode($matches[1]));

        $html = Html::a('tel:1234', 'Kirby');
        $expected = '<a href="tel:1234">Kirby</a>';
        $this->assertSame($expected, $html);
    }

    /**
     * @covers ::a
     * @covers ::link
     */
    public function testAWithAttributes()
    {
        $html = Html::a('https://getkirby.com', 'Kirby', ['class' => 'test']);
        $expected = '<a class="test" href="https://getkirby.com">Kirby</a>';
        $this->assertSame($expected, $html);

        $html = Html::a('mailto:mail@company.com', 'Kirby', ['class' => 'test']);
        $expected = '!^<a class="test" href="mailto:(.*?)">Kirby</a>$!';
        $this->assertMatchesRegularExpression($expected, $html);
        preg_match($expected, $html, $matches);
        $this->assertSame('mail@company.com', Html::decode($matches[1]));

        $html = Html::a('tel:1234', 'Kirby', ['class' => 'test']);
        $expected = '<a class="test" href="tel:1234">Kirby</a>';
        $this->assertSame($expected, $html);
    }

    /**
     * @covers ::a
     * @covers ::link
     */
    public function testAWithTarget()
    {
        $html = Html::a('https://getkirby.com', 'Kirby', ['target' => '_blank']);
        $expected = '<a href="https://getkirby.com" rel="noopener noreferrer" target="_blank">Kirby</a>';
        $this->assertSame($expected, $html);
    }

    /**
     * @covers ::a
     * @covers ::link
     */
    public function testAWithTargetAndRel()
    {
        $html = Html::a('https://getkirby.com', 'Kirby', ['target' => '_blank', 'rel' => 'noopener']);
        $expected = '<a href="https://getkirby.com" rel="noopener" target="_blank">Kirby</a>';
        $this->assertSame($expected, $html);
    }

    /**
     * @covers       ::attr
     * @dataProvider attrProvider
     */
    public function testAttr($input, $value, $expected)
    {
        $this->assertSame($expected, Html::attr($input, $value));
    }

    public function attrProvider()
    {
        return [
            [[],                         null,  ''],
            [['B' => 'b', 'A' => 'a'],   null,  'a="a" b="b"'],
            [['B' => 'b', 'A' => 'a'],   true,  'a="a" b="b"'],
            [['B' => 'b', 'A' => 'a'],   false, 'b="b" a="a"'],
            [['a' => 'a', 'b' => true],  null,  'a="a" b'],
            [['a' => 'a', 'b' => ' '],   null,  'a="a" b=""'],
            [['a' => 'a', 'b' => ''],    null,  'a="a"'],
            [['a' => 'a', 'b' => false], null,  'a="a"'],
            [['a' => 'a', 'b' => null],  null,  'a="a"'],
            [['a' => 'a', 'b' => []],    null,  'a="a"']
        ];
    }

    /**
     * @covers ::attr
     */
    public function testAttrArrayValue()
    {
        $result = Html::attr('a', ['a', 'b']);
        $this->assertSame('a="a b"', $result);

        $result = Html::attr('a', ['a', 1]);
        $this->assertSame('a="a 1"', $result);

        $result = Html::attr('a', ['a', null]);
        $this->assertSame('a="a"', $result);

        $result = Html::attr('a', ['value' => '&', 'escape' => true]);
        $this->assertSame('a="&amp;"', $result);

        $result = Html::attr('a', ['value' => '&', 'escape' => false]);
        $this->assertSame('a="&"', $result);
    }

    /**
     * @covers ::breaks
     */
    public function testBreaks()
    {
        $this->assertSame("line 1<br />\nline 2", Html::breaks("line 1\nline 2"));
    }

    /**
     * @covers ::email
     */
    public function testEmail()
    {
        $html = Html::email('mail@company.com?subject=Test');
        $expected = '!^<a href="mailto:(.*?)">(.*?)</a>$!';
        $this->assertMatchesRegularExpression($expected, $html);
        preg_match($expected, $html, $matches);
        $this->assertSame('mail@company.com?subject=Test', Html::decode($matches[1]));
        $this->assertSame('mail@company.com', Html::decode($matches[2]));
    }

    /**
     * @covers ::email
     */
    public function testEmailWithText()
    {
        $html = Html::email('mail@company.com', '<b>Email</b>');
        $expected = '!^<a href="mailto:(.*?)">&lt;b&gt;Email&lt;/b&gt;</a>$!';
        $this->assertMatchesRegularExpression($expected, $html);
        preg_match($expected, $html, $matches);
        $this->assertSame('mail@company.com', Html::decode($matches[1]));
    }

    /**
     * @covers ::email
     */
    public function testEmailWithArrayText()
    {
        $html = Html::email('mail@company.com', ['<b>Email</b>']);
        $expected = '!^<a href="mailto:(.*?)"><b>Email</b></a>$!';
        $this->assertMatchesRegularExpression($expected, $html);
        preg_match($expected, $html, $matches);
        $this->assertSame('mail@company.com', Html::decode($matches[1]));
    }

    /**
     * @covers ::email
     */
    public function testEmailWithoutAddress()
    {
        $html = Html::email('');
        $this->assertSame('', $html);
    }

    /**
     * @covers ::email
     */
    public function testEmailWithAttributes()
    {
        $html = Html::email('mail@company.com', 'Email', ['class' => 'email']);
        $expected = '!^<a class="email" href="mailto:(.*?)">Email</a>$!';
        $this->assertMatchesRegularExpression($expected, $html);
        preg_match($expected, $html, $matches);
        $this->assertSame('mail@company.com', Html::decode($matches[1]));
    }

    /**
     * @covers ::email
     */
    public function testEmailWithTarget()
    {
        $html = Html::email('mail@company.com', 'Email', ['target' => '_blank']);
        $expected = '!^<a href="mailto:(.*?)" rel="noopener noreferrer" target="_blank">Email</a>$!';
        $this->assertMatchesRegularExpression($expected, $html);
        preg_match($expected, $html, $matches);
        $this->assertSame('mail@company.com', Html::decode($matches[1]));
    }

    /**
     * @covers ::encode
     */
    public function testEncode()
    {
        $html = Html::encode('äöü');
        $expected = '&auml;&ouml;&uuml;';
        $this->assertSame($expected, $html);

        $html = Html::encode('ä<p>ö</p>');
        $expected = '&auml;&lt;p&gt;&ouml;&lt;/p&gt;';
        $this->assertSame($expected, $html);

        $html = Html::encode('ä<span title="Amazing &amp; great">ö</span>', true);
        $expected = '&auml;<span title="Amazing &amp; great">&ouml;</span>';
        $this->assertSame($expected, $html);

        $this->assertSame('', Html::encode(''));
        $this->assertSame('', Html::encode(null));
    }

    /**
     * @covers ::entities
     */
    public function testEntities()
    {
        Html::$entities = null;
        $this->assertTrue(count(Html::entities()) > 0);

        Html::$entities = [];
        $this->assertSame([], Html::entities());

        Html::$entities = ['t' => 'test'];
        $this->assertSame(['t' => 'test'], Html::entities());

        Html::$entities = null;
    }

    /**
     * @covers ::figure
     */
    public function testFigure()
    {
        $html = Html::figure('test');
        $expected = '<figure>test</figure>';
        $this->assertSame($expected, $html);

        $html = Html::figure('test', '', ['class' => 'figure']);
        $expected = '<figure class="figure">test</figure>';
        $this->assertSame($expected, $html);

        $html = Html::figure('test', 'yay');
        $expected = '<figure>test<figcaption>yay</figcaption></figure>';
        $this->assertSame($expected, $html);
    }

    /**
     * @covers ::gist
     */
    public function testGist()
    {
        $html = Html::gist($url = 'https://gist.github.com/bastianallgeier/dfb2a889ae73c7c318ea300efd2df6ff');
        $expected = '<script src="' . $url . '.js"></script>';
        $this->assertSame($expected, $html);

        $html = Html::gist($url = 'https://gist.github.com/bastianallgeier/dfb2a889ae73c7c318ea300efd2df6ff', 'kirbycontent.txt');
        $expected = '<script src="' . $url . '.js?file=kirbycontent.txt"></script>';
        $this->assertSame($expected, $html);
    }

    /**
     * @covers ::iframe
     */
    public function testIframe()
    {
        $html = Html::iframe($url = 'https://getkirby.com');
        $expected = '<iframe src="' . $url . '"></iframe>';
        $this->assertSame($expected, $html);
    }

    /**
     * @covers ::img
     */
    public function testImg()
    {
        $html = Html::img($src = 'https://getkirby.com/image.jpg');
        $expected = '<img alt="" src="' . $src . '">';
        $this->assertSame($expected, $html);
    }

    /**
     * @covers ::isVoid
     */
    public function testIsVoid()
    {
        $original = Html::$voidList;

        $this->assertTrue(Html::isVoid('hr'));
        $this->assertFalse(Html::isVoid('div'));
        $this->assertFalse(Html::isVoid(''));

        Html::$voidList[] = 'div';
        $this->assertTrue(Html::isVoid('div'));

        Html::$voidList = $original;
    }

    /**
     * @covers ::rel
     */
    public function testRel()
    {
        $html = Html::rel('me');
        $expected = 'me';
        $this->assertSame($expected, $html);

        $html = Html::rel(null, '_blank');
        $expected = 'noopener noreferrer';
        $this->assertSame($expected, $html);

        $html = Html::rel('noopener', '_blank');
        $expected = 'noopener';
        $this->assertSame($expected, $html);
    }

    /**
     * @covers ::tel
     * @covers ::link
     */
    public function testTel()
    {
        $html = Html::tel('1234');
        $expected = '<a href="tel:1234">1234</a>';
        $this->assertSame($expected, $html);
    }

    /**
     * @covers ::tel
     * @covers ::link
     */
    public function testTelWithText()
    {
        $html = Html::tel('1234', 'Tel');
        $expected = '<a href="tel:1234">Tel</a>';
        $this->assertSame($expected, $html);
    }

    /**
     * @covers ::tel
     * @covers ::link
     */
    public function testTelWithArrayText()
    {
        $html = Html::tel('1234', ['<b>Tel</b>']);
        $expected = '<a href="tel:1234"><b>Tel</b></a>';
        $this->assertSame($expected, $html);
    }

    /**
     * @covers ::tag
     */
    public function testTag()
    {
        $html = Html::tag('p', 'test');
        $expected = '<p>test</p>';
        $this->assertSame($expected, $html);

        $html = Html::tag('p', '');
        $expected = '<p></p>';
        $this->assertSame($expected, $html);

        $html = Html::tag('p', null);
        $expected = '<p></p>';
        $this->assertSame($expected, $html);

        $html = Html::tag('hr', '');
        $expected = '<hr>';
        $this->assertSame($expected, $html);

        $html = Html::tag('hr', null);
        $expected = '<hr>';
        $this->assertSame($expected, $html);

        Html::$void = ' />';
        $html = Html::tag('hr', null);
        $expected = '<hr />';
        $this->assertSame($expected, $html);
        Html::$void = '>';

        $html = Html::tag('p', 'test', ['class' => 'test']);
        $expected = '<p class="test">test</p>';
        $this->assertSame($expected, $html);

        $html = Html::tag('p', 'täst', ['class' => 'test']);
        $expected = '<p class="test">t&auml;st</p>';
        $this->assertSame($expected, $html);

        $html = Html::tag('p', ['<i>test</i>']);
        $expected = '<p><i>test</i></p>';
        $this->assertSame($expected, $html);
    }

    /**
     * @covers       ::value
     * @dataProvider valueProvider
     */
    public function testValue($input, $expected)
    {
        $this->assertSame($expected, Html::value($input));
    }

    public function valueProvider()
    {
        return [
            [true, 'true'],
            [false, 'false'],
            [1, '1'],
            [null, null],
            ['', null],
            ['test', 'test'],
            ['täst', 't&auml;st'],
        ];
    }

    /**
     * @covers       ::video
     * @covers       ::youtube
     * @covers       ::vimeo
     * @dataProvider videoProvider
     */
    public function testVideo($url, $src)
    {
        // plain
        $html = Html::video($url);
        $expected = '<iframe allowfullscreen src="' . $src . '"></iframe>';
        $this->assertSame($expected, $html);

        // with attributes
        $html = Html::video($url, [], ['class' => 'video']);
        $expected = '<iframe allowfullscreen class="video" src="' . $src . '"></iframe>';
        $this->assertSame($expected, $html);

        // with options
        $options = [
            'vimeo'   => ['foo' => 'bar'],
            'youtube' => ['foo' => 'bar']
        ];
        $html = Html::video($url, $options);
        $char = Str::contains($src, '?') === true ? '&amp;' : '?';
        $expected = '<iframe allowfullscreen src="' . $src . $char . 'foo=bar"></iframe>';
        $this->assertSame($expected, $html);

        // with attributes and options
        $options = [
            'vimeo'   => ['foo' => 'bar'],
            'youtube' => ['foo' => 'bar']
        ];
        $html = Html::video($url, $options, ['class' => 'video']);
        $expected = '<iframe allowfullscreen class="video" src="' . $src . $char . 'foo=bar"></iframe>';
        $this->assertSame($expected, $html);
    }

    public function videoProvider()
    {
        return [
            // YouTube
            [
                'https://www.youtube.com/embed/videoseries?list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys',
                'https://www.youtube.com/embed/videoseries?list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys'
            ],
            [
                'https://www.youtube.com/embed/videoseries?test=value&list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys',
                'https://www.youtube.com/embed/videoseries?test=value&amp;list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys'
            ],
            [
                'http://www.youtube-nocookie.com/embed/videoseries?list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys',
                'https://www.youtube-nocookie.com/embed/videoseries?list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys'
            ],
            [
                'http://www.youtube-nocookie.com/embed/videoseries?test=value&list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys',
                'https://www.youtube-nocookie.com/embed/videoseries?test=value&amp;list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys'
            ],
            [
                'http://www.youtube.com/embed/d9NF2edxy-M',
                'https://www.youtube.com/embed/d9NF2edxy-M'
            ],
            [
                'http://www.youtube.com/embed/d9NF2edxy-M?start=10',
                'https://www.youtube.com/embed/d9NF2edxy-M?start=10'
            ],
            [
                'http://www.youtube.com/embed/d9NF2edxy-M?start=10&list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys',
                'https://www.youtube.com/embed/d9NF2edxy-M?start=10&amp;list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys'
            ],
            [
                'https://www.youtube-nocookie.com/embed/d9NF2edxy-M',
                'https://www.youtube-nocookie.com/embed/d9NF2edxy-M'
            ],
            [
                'https://www.youtube-nocookie.com/embed/d9NF2edxy-M?start=10',
                'https://www.youtube-nocookie.com/embed/d9NF2edxy-M?start=10'
            ],
            [
                'https://www.youtube-nocookie.com/watch?v=d9NF2edxy-M',
                'https://www.youtube-nocookie.com/embed/d9NF2edxy-M'
            ],
            [
                'https://www.youtube-nocookie.com/watch?v=d9NF2edxy-M&t=10',
                'https://www.youtube-nocookie.com/embed/d9NF2edxy-M?start=10'
            ],
            [
                'https://www.youtube-nocookie.com/watch?test=value&v=d9NF2edxy-M&t=10',
                'https://www.youtube-nocookie.com/embed/d9NF2edxy-M?test=value&amp;start=10'
            ],
            [
                'https://www.youtube-nocookie.com/playlist?list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys',
                'https://www.youtube-nocookie.com/embed/videoseries?list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys'
            ],
            [
                'https://www.youtube-nocookie.com/playlist?test=value&list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys',
                'https://www.youtube-nocookie.com/embed/videoseries?test=value&amp;list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys'
            ],
            [
                'http://www.youtube.com/watch?v=d9NF2edxy-M',
                'https://www.youtube.com/embed/d9NF2edxy-M'
            ],
            [
                'http://www.youtube.com/watch?test=value&v=d9NF2edxy-M',
                'https://www.youtube.com/embed/d9NF2edxy-M?test=value'
            ],
            [
                'http://www.youtube.com/watch?v=d9NF2edxy-M&t=10',
                'https://www.youtube.com/embed/d9NF2edxy-M?start=10'
            ],
            [
                'https://www.youtube.com/playlist?list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys',
                'https://www.youtube.com/embed/videoseries?list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys'
            ],
            [
                'https://www.youtube.com/playlist?test=value&list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys',
                'https://www.youtube.com/embed/videoseries?test=value&amp;list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys'
            ],
            [
                'https://www.youtu.be/d9NF2edxy-M',
                'https://www.youtube.com/embed/d9NF2edxy-M'
            ],
            [
                'https://www.youtu.be/d9NF2edxy-M?t=10',
                'https://www.youtube.com/embed/d9NF2edxy-M?start=10'
            ],
            [
                'https://youtu.be/d9NF2edxy-M?t=10',
                'https://www.youtube.com/embed/d9NF2edxy-M?start=10'
            ],
            [
                'https://www.youtu.be/d9NF2edxy-M?test=value&t=10',
                'https://www.youtube.com/embed/d9NF2edxy-M?test=value&amp;start=10'
            ],

            // Vimeo
            [
                'https://vimeo.com/239882943',
                'https://player.vimeo.com/video/239882943'
            ],
            [
                'https://vimeo.com/239882943?test=value',
                'https://player.vimeo.com/video/239882943?test=value'
            ],
            [
                'https://player.vimeo.com/video/239882943',
                'https://player.vimeo.com/video/239882943'
            ],
            [
                'https://player.vimeo.com/video/239882943?test=value',
                'https://player.vimeo.com/video/239882943?test=value'
            ],
        ];
    }

    /**
     * @covers ::video
     */
    public function testVideoWithInvalidUrl()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Unexpected video type');

        Html::video('https://somevideo.com');
    }

    /**
     * @covers ::youtube
     */
    public function testVideoWithInvalidYoutubeUrl()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Invalid YouTube source');

        Html::video('https://youtube.com/asldjhaskjdhakjs');
    }

    /**
     * @covers ::vimeo
     */
    public function testVideoWithInvalidVimeoUrl()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Invalid Vimeo source');

        Html::video('https://vimeo.com/asldjhaskjdhakjs');
    }
}
