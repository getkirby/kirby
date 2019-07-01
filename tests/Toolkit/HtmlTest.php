<?php

namespace Kirby\Toolkit;

use PHPUnit\Framework\TestCase;

class HtmlTest extends TestCase
{
    public function test__callStatic()
    {
        $html = Html::div('test');
        $expected = '<div>test</div>';

        $this->assertEquals($expected, $html);
    }

    public function test__callStaticWithAttributes()
    {
        $html = Html::div('test', ['class' => 'test']);
        $expected = '<div class="test">test</div>';

        $this->assertEquals($expected, $html);
    }

    public function test__callStaticWithVoidElement()
    {
        $html = Html::hr(['class' => 'test']);
        $expected = '<hr class="test">';

        $this->assertEquals($expected, $html);
    }

    public function testA()
    {
        $html = Html::a('https://getkirby.com');
        $expected = '<a href="https://getkirby.com">getkirby.com</a>';

        $this->assertEquals($expected, $html);

        $html = Html::a('mailto:mail@company.com');
        $expected = '!\<a href="mailto\:.*?">.*?\</a>!';

        $this->assertRegExp($expected, $html);

        $html = Html::a('tel:1234');
        $expected = '<a href="tel:1234">tel:1234</a>';

        $this->assertEquals($expected, $html);
    }

    public function testAWithText()
    {
        $html = Html::a('https://getkirby.com', 'Kirby');
        $expected = '<a href="https://getkirby.com">Kirby</a>';

        $this->assertEquals($expected, $html);
    }

    public function testAWithAttributes()
    {
        $html = Html::a('https://getkirby.com', 'Kirby', ['class' => 'test']);
        $expected = '<a class="test" href="https://getkirby.com">Kirby</a>';

        $this->assertEquals($expected, $html);
    }

    public function testAWithTarget()
    {
        $html = Html::a('https://getkirby.com', 'Kirby', ['target' => '_blank']);
        $expected = '<a href="https://getkirby.com" rel="noopener noreferrer" target="_blank">Kirby</a>';

        $this->assertEquals($expected, $html);
    }

    public function testAWithTargetAndRel()
    {
        $html = Html::a('https://getkirby.com', 'Kirby', ['target' => '_blank', 'rel' => 'noopener']);
        $expected = '<a href="https://getkirby.com" rel="noopener" target="_blank">Kirby</a>';

        $this->assertEquals($expected, $html);
    }

    public function testAttr()
    {
        $tests = [
            [
                'input'    => [],
                'expected' => ''
            ],
            [
                'input'    => ['a' => 'a', 'b' => 'b'],
                'expected' => 'a="a" b="b"'
            ],
            [
                'input'    => ['a' => 'a', 'b' => true],
                'expected' => 'a="a" b'
            ],
            [
                'input'    => ['a' => 'a', 'b' => ''],
                'expected' => 'a="a"'
            ],
            [
                'input'    => ['a' => 'a', 'b' => false],
                'expected' => 'a="a"'
            ],
        ];

        foreach ($tests as $test) {
            $result = Html::attr($test['input']);
            $this->assertEquals($test['expected'], $result);
        }
    }

    public function testAttrSingleMode()
    {
        $result = Html::attr('a', 'a');
        $this->assertEquals('a="a"', $result);

        $result = Html::attr('a', null);
        $this->assertEquals(null, $result);

        $result = Html::attr('a', ['a', 'b']);
        $this->assertEquals('a="a b"', $result);

        $result = Html::attr('a', ['a', null]);
        $this->assertEquals('a="a"', $result);
    }

    public function testBreaks()
    {
        $html = Html::breaks("line 1\nline 2");
        $expected = "line 1<br />\nline 2";

        $this->assertEquals($expected, $html);
    }

    public function testDecode()
    {
        $html = Html::decode('some uber <em>crazy</em> stuff');
        $expected = 'some uber crazy stuff';

        $this->assertEquals($expected, $html);
    }

    public function testEmail()
    {
        $html = Html::email('mail@company.com');
        $expected = '!\<a href=".*?">.*?\</a>!';

        $this->assertRegExp($expected, $html);
    }

    public function testEmailWithArrayText()
    {
        $html = Html::email('mail@company.com', ['<b>Email</b>']);
        $expected = '!\<a href=".*?"><b>Email</b></a>!';

        $this->assertRegExp($expected, $html);
    }

    public function testEmailWithoutAddress()
    {
        $html = Html::email('');
        $this->assertEquals('', $html);
    }

    public function testEmailWithText()
    {
        $html = Html::email('mail@company.com', 'Email');
        $expected = '!\<a href=".*?">Email</a>!';

        $this->assertRegExp($expected, $html);
    }

    public function testEmailWithAttributes()
    {
        $html = Html::email('mail@company.com', 'Email', ['class' => 'email']);
        $expected = '!\<a class="email" href=".*?">Email</a>!';

        $this->assertRegExp($expected, $html);
    }

    public function testEmailWithTarget()
    {
        $html = Html::email('mail@company.com', 'Email', ['target' => '_blank']);
        $expected = '!\<a href=".*?" rel="noopener noreferrer" target="_blank">Email</a>!';

        $this->assertRegExp($expected, $html);
    }

    public function testEncode()
    {
        $html = Html::encode('äöü');
        $expected = '&auml;&ouml;&uuml;';

        $this->assertEquals($expected, $html);
    }

    public function testEncodeWithHtml()
    {
        $html = Html::encode('ä<p>ö</p>', true);
        $expected = '&auml;<p>&ouml;</p>';

        $this->assertEquals($expected, $html);
    }

    public function testFigure()
    {
        $html = Html::figure('test');
        $expected = '<figure>test</figure>';

        $this->assertEquals($expected, $html);
    }

    public function testFigureWithAttributes()
    {
        $html = Html::figure('test', null, ['class' => 'figure']);
        $expected = '<figure class="figure">test</figure>';

        $this->assertEquals($expected, $html);
    }

    public function testFigureWithCaption()
    {
        $html = Html::figure('test', 'yay');
        $expected = '<figure>test<figcaption>yay</figcaption></figure>';

        $this->assertEquals($expected, $html);
    }

    public function testGist()
    {
        $html = Html::gist($url = 'https://gist.github.com/bastianallgeier/dfb2a889ae73c7c318ea300efd2df6ff');
        $expected = '<script src="' . $url . '.js"></script>';

        $this->assertEquals($expected, $html);
    }

    public function testGistWithFile()
    {
        $html = Html::gist($url = 'https://gist.github.com/bastianallgeier/dfb2a889ae73c7c318ea300efd2df6ff', 'kirbycontent.txt');
        $expected = '<script src="' . $url . '.js?file=kirbycontent.txt"></script>';

        $this->assertEquals($expected, $html);
    }

    public function testIframe()
    {
        $html = Html::iframe($url = 'https://getkirby.com');
        $expected = '<iframe src="' . $url . '"></iframe>';

        $this->assertEquals($expected, $html);
    }

    public function testImg()
    {
        $html = Html::img($src = 'https://getkirby.com/image.jpg');
        $expected = '<img alt="" src="' . $src . '">';

        $this->assertEquals($expected, $html);
    }

    public function testRel()
    {
        $html = Html::rel('me');
        $expected = 'me';

        $this->assertEquals($expected, $html);
    }

    public function testRelWithTarget()
    {
        $html = Html::rel(null, '_blank');
        $expected = 'noopener noreferrer';

        $this->assertEquals($expected, $html);

        $html = Html::rel('noopener', '_blank');
        $expected = 'noopener';

        $this->assertEquals($expected, $html);
    }

    public function testTel()
    {
        $html = Html::tel('1234');
        $expected = '<a href="tel:1234">1234</a>';

        $this->assertEquals($expected, $html);
    }

    public function testTag()
    {
        $html = Html::tag('p', 'test');
        $expected = '<p>test</p>';

        $this->assertEquals($expected, $html);
    }

    public function testTagWithAttributes()
    {
        $html = Html::tag('p', 'test', ['class' => 'test']);
        $expected = '<p class="test">test</p>';

        $this->assertEquals($expected, $html);
    }

    public function testTagWithArrayContent()
    {
        $html = Html::tag('p', ['<i>test</i>']);
        $expected = '<p><i>test</i></p>';

        $this->assertEquals($expected, $html);
    }

    public function videoProvider()
    {
        return [

            // youtube
            ['http://www.youtube.com/watch?v=d9NF2edxy-M', 'https://youtube.com/embed/d9NF2edxy-M'],
            ['http://www.youtube.com/embed/d9NF2edxy-M', 'https://youtube.com/embed/d9NF2edxy-M'],
            ['https://youtu.be/d9NF2edxy-M', 'https://youtube.com/embed/d9NF2edxy-M'],
            ['https://www.youtube-nocookie.com/watch?v=d9NF2edxy-M', 'https://www.youtube-nocookie.com/embed/d9NF2edxy-M'],
            ['https://www.youtube-nocookie.com/embed/d9NF2edxy-M', 'https://www.youtube-nocookie.com/embed/d9NF2edxy-M'],

            // vimeo
            ['https://vimeo.com/239882943', 'https://player.vimeo.com/video/239882943'],
            ['https://player.vimeo.com/video/239882943', 'https://player.vimeo.com/video/239882943'],
        ];
    }

    /**
     * @dataProvider videoProvider
     */
    public function testVideo($url, $src)
    {
        // plain
        $html = Html::video($url);
        $expected = '<iframe allowfullscreen src="' . $src . '"></iframe>';

        $this->assertEquals($expected, $html);

        // with attributes
        $html = Html::video($url, null, ['class' => 'video']);
        $expected = '<iframe allowfullscreen class="video" src="' . $src . '"></iframe>';

        $this->assertEquals($expected, $html);

        // with options
        $options = [
            'vimeo'   => ['foo' => 'bar'],
            'youtube' => ['foo' => 'bar']
        ];

        $html = Html::video($url, $options);
        $expected = '<iframe allowfullscreen src="' . $src . '?foo=bar"></iframe>';

        $this->assertEquals($expected, $html);
    }

    /**
     * @dataProvider videoProvider
     */
    public function testVideoWithAttributes($url, $src)
    {
        // with attributes
        $html = Html::video($url, null, ['class' => 'video']);
        $expected = '<iframe allowfullscreen class="video" src="' . $src . '"></iframe>';

        $this->assertEquals($expected, $html);
    }

    /**
     * @dataProvider videoProvider
     */
    public function testVideoWithOptions($url, $src)
    {
        $options = [
            'vimeo'   => ['foo' => 'bar'],
            'youtube' => ['foo' => 'bar']
        ];

        $html = Html::video($url, $options);
        $expected = '<iframe allowfullscreen src="' . $src . '?foo=bar"></iframe>';

        $this->assertEquals($expected, $html);
    }

    public function testVideoWithInvalidUrl()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Unexpected video type');

        Html::video('https://somevideo.com');
    }

    public function testVideoWithInvalidYoutubeUrl()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Invalid Youtube source');

        Html::video('https://youtube.com/asldjhaskjdhakjs');
    }

    public function testVideoWithInvalidVimeoUrl()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Invalid Vimeo source');

        Html::video('https://vimeo.com/asldjhaskjdhakjs');
    }
}
